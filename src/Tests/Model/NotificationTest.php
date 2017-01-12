<?php

namespace Dmytrof\PushNotificationBundle\Tests\Model;

use Dmytrof\PushNotificationBundle\Model\Notification;
use Dmytrof\PushNotificationBundle\Exception\RuntimeException;
use Symfony\Component\Templating\EngineInterface;

class NotificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test creating of notification
     */
    public function testCreateNotification()
    {
        $notificationData = [
            'subject' => 'Test subject',
            'message' => 'Test message',
            'locale'  => 'ru',
        ];

        $notification = new Notification($notificationData['message'], $notificationData['subject']);

        $this->assertEquals($notificationData['subject'], $notification->getSubject());
        $this->assertEquals($notificationData['message'], $notification->getMessage());
        $this->assertEquals(Notification::DEFAULT_LOCALE, $notification->getLocale());

        $notification = new Notification($notificationData['message'], $notificationData['subject'], $notificationData['locale']);

        $this->assertEquals($notificationData['subject'], $notification->getSubject());
        $this->assertEquals($notificationData['message'], $notification->getMessage());
        $this->assertEquals($notificationData['locale'], $notification->getLocale());
    }

    /**
     * Test setting of template
     */
    public function testAddTemplate()
    {
        $templateData = [
            'test_template.html.twig',
            [
                'foo' => 'bar',
            ]
        ];

        $notification = new Notification();
        $notification->setTemplate($templateData[0], $templateData[1]);

        $this->assertEquals($templateData, $notification->getTemplate());

        $notification->removeTemplate();

        $this->assertEquals(null, $notification->getTemplate());
    }

    /**
     * Test setting of url
     */
    public function testAddUrl()
    {
        $url = 'http://www.example.com';

        $notification = new Notification();
        $notification->setUrl($url);

        $this->assertEquals($url, $notification->getUrl());
    }

    /**
     * Test setting of data
     */
    public function testAddData()
    {
        $notification = new Notification();

        $data = [
            'hello' => 'world',
            'foo' => 'bar',
        ];

        $notification->setData($data);

        $this->assertEquals($data, $notification->getData());

        $data2 = [
            'bar' => 'baz',
            'hello' => 'melloy',
        ];

        $notification->addData($data2);

        $this->assertEquals(array_merge($data, $data2), $notification->getData());
        $this->assertCount(3, $notification->getData());
    }

    /**
     * Test filtering by tags
     */
    public function testFiteringByTags()
    {
        $tag1 = [
            'field' => 'tag',
            'key' => 'tagName1',
            'relation' => '=',
            'value' => 'value1',
        ];

        $notification = new Notification();

        $notification->filterByTag($tag1['key'], $tag1['relation'], $tag1['value']);

        $this->assertEquals([$tag1], $notification->getFilters());

        $tag2 = [
            'field' => 'tag',
            'key' => 'tagName2',
            'relation' => '>',
            'value' => '5',
        ];

        $notification->filterByTag($tag2['key'], $tag2['relation'], $tag2['value'], true);
        $this->assertCount(3, $notification->getFilters());

        $this->assertEquals([$tag1, ['operator' => 'OR'], $tag2], $notification->getFilters());


        $notification->clearFilters();
        $this->assertCount(0, $notification->getFilters());
    }

    /**
     * Test including of segments
     */
    public function testIncludingSegments()
    {
        $segments = ['All', 'Test'];

        $notification = new Notification();
        $notification->includeSegments($segments);

        $this->assertEquals($segments, $notification->getIncludedSegments());
        $this->assertCount(2, $notification->getIncludedSegments());

        $notification->includeSegments(['foo', 'bar']);

        $this->assertCount(4, $notification->getIncludedSegments());
        $this->assertEquals(array_merge($segments, ['foo', 'bar']), $notification->getIncludedSegments());

        $notification->clearIncludedSegments();

        $this->assertCount(0, $notification->getIncludedSegments());
    }

    /**
     * Test excluding of segments
     */
    public function testExcludingSegments()
    {
        $segments = ['Test'];

        $notification = new Notification();
        $notification->excludeSegments($segments);

        $this->assertEquals($segments, $notification->getExcludedSegments());
        $this->assertCount(1, $notification->getExcludedSegments());

        $notification->excludeSegments(['foo', 'bar']);

        $this->assertCount(3, $notification->getExcludedSegments());
        $this->assertEquals(array_merge($segments, ['foo', 'bar']), $notification->getExcludedSegments());

        $notification->clearExcludedSegments();

        $this->assertCount(0, $notification->getExcludedSegments());
    }

    /**
     * Test preparing from template
     */
    public function testPreparingFromTemplate()
    {
        $templateXml = "
            <notification>
                <subject>Notification Subject</subject>
                <message>Message of notification</message>
                <url>http://www.google.com</url>
            </notification>
        ";

        $templating = $this->getMockBuilder(EngineInterface::class)
                           ->setMethods(['render', 'exists', 'supports'])
                           ->getMock();
        $templating
            ->method('render')
            ->willReturn($templateXml)
        ;
        $templating
            ->method('exists')
            ->willReturn(true)
        ;
        $templating
            ->method('supports')
            ->willReturn(true)
        ;

        $notification = new Notification();
        $notification
            ->setTemplate('DmytrofPushNotificationBundle:PushNotification:test_notification.html.twig')
            ->prepareFromTemplate($templating);

        $this->assertEquals('Notification Subject', $notification->getSubject());
        $this->assertEquals('Message of notification', $notification->getMessage());
        $this->assertEquals('http://www.google.com', $notification->getUrl());


        $this->expectException(RuntimeException::class);

        $notification = new Notification();
        $notification
            ->prepareFromTemplate($templating);
    }
}