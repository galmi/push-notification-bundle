<?php

namespace Dmytrof\PushNotificationBundle\Tests\Model;

use Dmytrof\PushNotificationBundle\Model\Notification;

class NotificationTest extends \PHPUnit_Framework_TestCase
{
    public function getNotificationData()
    {
        return [
            'subject' => 'Test subject',
            'message' => 'Test message',
            'locale'  => 'ru',
        ];
    }

    /**
     * Test creating of notification
     */
    public function testCreateNotification()
    {
        $notificationData = $this->getNotificationData();

        $notification = new Notification($notificationData['message'], $notificationData['subject']);

        $this->assertEquals($notificationData['subject'], $notification->getSubject());
        $this->assertEquals($notificationData['message'], $notification->getMessage());
        $this->assertEquals(Notification::DEFAULT_LOCALE, $notification->getLocale());

        $notification = new Notification($notificationData['subject'], $notificationData['message'], $notificationData['locale']);

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
}