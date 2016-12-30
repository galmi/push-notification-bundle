<?php

namespace Dmytrof\PushNotificationBundle\Provider;

use Dmytrof\PushNotificationBundle\Provider\AbstractProvider;
use OneSignal\OneSignal as OneSignalApi;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Templating\EngineInterface;
use Dmytrof\PushNotificationBundle\Notification\Message;

class OneSignal extends AbstractProvider
{
    /**
     * @var OneSignal
     */
    protected $client;
    /**
     * @var string
     */
    protected $errorMessage;
    /**
     * @var int
     */
    protected $errorCode;

    /**
     * Constructor
     *
     * @param OneSignal $client
     * @param SessionInterface $session
     * @param EngineInterface $templating
     * @param string $code
     */
    public function __construct(OneSignalApi $client, SessionInterface $session, EngineInterface $templating, $code)
    {
        parent::__construct($session, $templating, $code);
        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function sendMessage(Message $message)
    {
        $this->prepareMessage($message);

        $notificationData = [
            'contents' => [
                $message->getLocale() => $message->getContent(),
            ],
        ];

        if ($message->getSubject()) {
            $notificationData['headings'] = [
                $message->getLocale() => $message->getSubject(),
            ];
        }

        if ($message->getFilters()) {
            $notificationData['filters'] = $message->getFilters();
        }

        if ($message->getIncludedSegments()) {
            $notificationData['included_segments'] = $message->getIncludedSegments();
        }

        if ($message->getExcludedSegments()) {
            $notificationData['excluded_segments'] = $message->getExcludedSegments();
        }

        if ($message->getUrl()) {
            $notificationData['url'] = $message->getUrl();
        }

        if ($message->getData()) {
            $notificationData['data'] = $message->getData();
        }

        return $this->sendPush($notificationData);
    }

    /**
     * Send the push message with client
     *
     * @param array $notificationData
     *
     * @return boolean
     */
    public function sendPush(array $notificationData)
    {
        // Call the REST Web Service
        $response = $this->client->notifications->add($notificationData);
        // Check if its ok
        if (!isset($response['error'])) {
            return true;
        } else {
            $this->errorMessage = implode('; ', $response['error']);
            $this->errorCode = null;
            return false;
        }
    }

    /**
     * Getter for errorMessage
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
    /**
     * Getter for errorCode
     *
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
}