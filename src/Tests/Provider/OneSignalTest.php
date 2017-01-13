<?php

namespace Dmytrof\PushNotificationBundle\Tests\Provider;

use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Templating\EngineInterface;
use OneSignal\OneSignal as OneSignalApi;
use Dmytrof\PushNotificationBundle\Provider\OneSignal;
use Dmytrof\PushNotificationBundle\Model\Notification;
use Http\Client\Common\HttpMethodsClient;
use GuzzleHttp\Psr7\Response;

class OneSignalTest extends \PHPUnit_Framework_TestCase
{
    const RESPONSE_200OK = 1;
    const RESPONSE_400BadRequest = 2;
    const RESPONSE_200InvalidPlayerIds = 3;
    const RESPONSE_200NoSubscribedPlayers = 4;
    
    protected $providerCode = 'onesignal';
    
    /**
     * Test create OneSignal provider
     */
    public function testCreateProvider()
    {
        $rendered = 'rendered';
        
        $templating = $this->getMockBuilder(EngineInterface::class)
                            ->setMethods(['render', 'exists', 'supports'])
                            ->getMock();
        $templating
            ->method('render')
            ->willReturn($rendered)
        ;
        $templating
            ->method('exists')
            ->willReturn(true)
        ;
        $templating
            ->method('supports')
            ->willReturn(true)
        ;
        
        $session = new Session(new MockArraySessionStorage());
        
        $httpClient = $this->getMockBuilder(HttpMethodsClient::class)
                            ->disableOriginalConstructor()
                            ->setMethods(['send'])
                            ->getMock();
        $httpClient
            ->method('send')
            ->willReturnCallback([$this, 'httpClientSendCallback'])
        ;
        
        
        $client = new OneSignalApi(null, $httpClient);
        
        $provider = new OneSignal($client, $session, $templating, $this->providerCode);
        
        $this->assertEquals($this->providerCode, $provider->getCode());
        
        return $provider;
    }
    
    /**
     * Test create notification
     *
     * @depends testCreateProvider
     */
    public function testCreateNotification(OneSignal $provider)
    {
        $this->assertInstanceOf(Notification::class, $provider->createNotification());
        
        $message = 'bar';
        $subject = 'foo';
        $locale = 'buz';
        
        $notification = $provider->createNotification($message, $subject, $locale);
        
        $this->assertEquals($message, $notification->getMessage());
        $this->assertEquals($subject, $notification->getSubject());
        $this->assertEquals($locale, $notification->getLocale());
    
        return $notification;
    }
    
    /**
     * Test tags logic
     *
     * @depends testCreateProvider
     */
    public function testTagsLogic(OneSignal $provider)
    {
        $this->assertEmpty($provider->getTags());
        
        $tags = [
            'tag1' => 'value1',
            'tag2' => 'value2',
        ];
        $provider->setTags($tags);
        
        $this->assertEquals($tags, $provider->getTags());
        $this->assertCount(2, $provider->getTags(true));
        $this->assertEmpty($provider->getTags());
    
        $provider->addTag('foo', 'bar');
        
        $this->assertCount(1, $provider->getTags());
        $this->assertEquals(['foo' => 'bar'], $provider->getTags());
        
        $provider->setTags($tags);
        
        $this->assertEquals($tags, $provider->getTags());
        $this->assertCount(2, $provider->getTags());
        
        $tags2 = [
            'foo' => 'woo',
            'bee' => 'pee',
            'tag2' => 'yes',
        ];
        $provider->addTags($tags2);
        
        $this->assertCount(4, $provider->getTags());
        $this->assertEquals(array_merge($tags, $tags2), $provider->getTags(true));
        
        $this->assertEmpty($provider->getTags());
    }
    
    /**
     * Test removable tags logic
     *
     * @depends testCreateProvider
     */
    public function testRemovableTagsLogic(OneSignal $provider)
    {
        $this->assertEmpty($provider->getRemovableTags());
    
        $tags = [
            'tag1' => 'value1',
            'tag2' => 'value2',
        ];
        $provider->removeTags($tags);
    
        $this->assertEquals($tags, $provider->getRemovableTags());
        $this->assertCount(2, $provider->getRemovableTags(true));
        $this->assertEmpty($provider->getRemovableTags());
    
        $provider->removeTags($tags);
    
        $tags2 = [
            'foo' => 'woo',
            'bee' => 'pee',
            'tag2' => 'yes',
        ];
        $provider->removeTags($tags2);
    
        $this->assertCount(4, $provider->getRemovableTags());
        $this->assertEquals(array_merge($tags, $tags2), $provider->getRemovableTags(true));
    
        $this->assertEmpty($provider->getRemovableTags());
    }
    
    /**
     * Test send notification
     *
     * @depends testCreateProvider
     * @depends testCreateNotification
     */
    public function testSendNotification(OneSignal $provider, Notification $notification)
    {
        $this->assertTrue($provider->sendNotification($notification));
        $this->assertEmpty($provider->getErrorMessage());
        
        $notificationData = [
            'app_id' => static::RESPONSE_400BadRequest,
            'included_segments' => ['All'],
        ];
        
        $this->assertFalse($provider->sendPush($notificationData));
        $this->assertNotEmpty($provider->getErrorMessage());
        $this->assertEquals('["Notification content must not be null for any languages."]', $provider->getErrorMessage());
        
        $notificationData = [
            'app_id' => static::RESPONSE_200InvalidPlayerIds,
            'contents' => [
                'en' => 'foo',
            ],
            'included_segments' => ['All'],
        ];
        
        $this->assertFalse($provider->sendPush($notificationData));
        $this->assertNotEmpty($provider->getErrorMessage());
        
        $notificationData = [
            'app_id' => static::RESPONSE_200NoSubscribedPlayers,
            'contents' => [
                'en' => 'foo',
            ],
            'included_segments' => ['All'],
        ];
        
        $this->assertFalse($provider->sendPush($notificationData));
        $this->assertNotEmpty($provider->getErrorMessage());
    }
    
    
    /**
     * Callback of httpClient->send
     *
     * @param string $method
     * @param string $uri
     * @param array $headers
     * @param string $body
     *
     * @return string
     */
    public function httpClientSendCallback($method, $uri, array $headers, $body)
    {
        $this->assertEquals('POST', $method);
        
        $body = json_decode($body, true);
                
        // Result responses from https://documentation.onesignal.com/reference#section-results-create-notification
        switch ($body['app_id']) {
            case static::RESPONSE_400BadRequest:
                return new Response(400, [], '{"errors":["Notification content must not be null for any languages."]}');
            
            case static::RESPONSE_200NoSubscribedPlayers:
                return new Response(200, [], '{"id":"","recipients":0,"errors":["All included players are not subscribed"]}');
                
            case static::RESPONSE_200InvalidPlayerIds:
                return new Response(200, [], '{"errors":{"invalid_player_ids":["5fdc92b2-3b2a-11e5-ac13-8fdccfe4d986","00cb73f8-5815-11e5-ba69-f75522da5528"]}}');
            
            case static::RESPONSE_200OK:
            default:
                return new Response(200, [], '{"id":"458dcec4-cf53-11e3-add2-000c2940e62c","recipients":3}');
        }
    }
    
}