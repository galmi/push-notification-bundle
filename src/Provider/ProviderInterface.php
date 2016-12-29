<?php

namespace Dmytrof\PushNotificationBundle\Provider;

use Dmytrof\PushNotificationBundle\Notification\Message;

interface ProviderInterface
{
    /**
     * Return new instance of Message
     *
     * @return Message
     */
    public function createMessage($content=null, $subject=null, $locale=null);

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
     * Send message
     *
     * @param Message $message
     * @return boolean
     */
    public function sendMessage(Message $message);
}