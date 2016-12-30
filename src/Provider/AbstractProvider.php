<?php

namespace Dmytrof\PushNotificationBundle\Provider;

use Dmytrof\PushNotificationBundle\Provider\ProviderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Templating\EngineInterface;
use Dmytrof\PushNotificationBundle\Notification\Message;

abstract class AbstractProvider implements ProviderInterface
{
    const TAGS_SESSION_KEY = 'push.notification.tags';
    const REMOVABLE_TAGS_SESSION_KEY = 'push.notification.tags.removable';

    private $session;
    private $templating;
    private $code;

    public function __construct(SessionInterface $session, EngineInterface $templating, $code)
    {
        $this->session = $session;
        $this->templating = $templating;
        $this->code = $code;
    }

    /**
     * Return session
     *
     * @return SessionInterface
     */
    protected function getSession()
    {
        return $this->session;
    }

    /**
     * Return templating
     *
     * @return EngineInterface
     */
    protected function getTemplating()
    {
        return $this->templating;
    }

    /**
     * {@inheritDoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     *
     * {@inheritDoc}
     */
    public function createMessage($content=null, $subject=null, $locale=null)
    {
        return new Message();
    }

    /**
     * {@inheritDoc}
     */
    public function setTags(array $tags)
    {
        $this->getSession()->set(static::TAGS_SESSION_KEY, $tags);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTags($clearFlag = false)
    {
        $tags = $this->getSession()->get(static::TAGS_SESSION_KEY, []);
        if ($clearFlag) {
            $this->getSession()->remove(static::TAGS_SESSION_KEY);
        }
        return $tags;
    }

    /**
     * {@inheritDoc}
     */
    public function addTags(array $tags)
    {
        $tags = array_merge($this->getTags(), $tags);
        return $this->setTags($tags);
    }

    /**
     * {@inheritDoc}
     */
    public function addTag($key, $value)
    {
        return $this->addTags([$key => $value]);
    }

    /**
     * {@inheritDoc}
     */
    public function removeTags(array $tags)
    {
        $tags = array_merge($this->getRemovableTags(), $tags);
        $this->getSession()->set(static::REMOVABLE_TAGS_SESSION_KEY, $tags);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRemovableTags($clearFlag = false)
    {
        $tags = $this->getSession()->get(static::REMOVABLE_TAGS_SESSION_KEY, []);
        if ($clearFlag) {
            $this->getSession()->remove(static::REMOVABLE_TAGS_SESSION_KEY);
        }
        return $tags;
    }

    /**
     * @param Message $message
     *
     * @return ProviderInterface
     */
    protected function prepareMessage(Message $message)
    {
        if (!is_null($message->getTemplate())) {
            $message->prepareFromTemplate($this->getTemplating());
        }
        return $this;
    }
}