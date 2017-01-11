<?php

namespace Dmytrof\PushNotificationBundle\Provider;

use Dmytrof\PushNotificationBundle\Model\Notification;

interface ProviderInterface
{
    /**
     * Return new instance of Notification
     *
     * @return Notification
     */
    public function createNotification($content=null, $subject=null, $locale=null);

    /**
     * Return provider's code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set tags for user
     *
     * @param array $tags
     * @return ProviderInterface
     */
    public function setTags(array $tags);

    /**
     * Get tags for user
     *
     * @param boolean $clearFlag
     * @return array
     */
    public function getTags($clearFlag = false);

    /**
     * Merge tags for user
     *
     * @param array $tags
     * @return ProviderInterface
     */
    public function addTags(array $tags);

    /**
     * Add one tag for user
     *
     * @param string $key
     * @param mixed $value
     * @return ProviderInterface
     */
    public function addTag($key, $value);

    /**
     * Remove tags
     *
     * @param array $tags
     * @return ProviderInterface
     */
    public function removeTags(array $tags);

    /**
     * Get tags to remove for user
     *
     * @param boolean $clearFlag
     * @return array
     */
    public function getRemovableTags($clearFlag = false);

    /**
     * Send notification
     *
     * @param Notification $notification
     * @return boolean
     */
    public function sendNotification(Notification $notification);
}