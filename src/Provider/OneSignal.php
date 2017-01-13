<?php

namespace Dmytrof\PushNotificationBundle\Provider;

use Dmytrof\PushNotificationBundle\Provider\AbstractProvider;
use OneSignal\OneSignal as OneSignalApi;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Templating\EngineInterface;
use Dmytrof\PushNotificationBundle\Model\Notification;

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
    public function sendNotification(Notification $notification)
    {
        $this->prepareNotification($notification);

        $notificationData = [
            'contents' => [
                $notification->getLocale() => $notification->getMessage(),
            ],
        ];

        if ($notification->getSubject()) {
            $notificationData['headings'] = [
                $notification->getLocale() => $notification->getSubject(),
            ];
        }

        if ($notification->getFilters()) {
            $notificationData['filters'] = $notification->getFilters();
        }

        if ($notification->getIncludedSegments()) {
            $notificationData['included_segments'] = $notification->getIncludedSegments();
        }

        if ($notification->getExcludedSegments()) {
            $notificationData['excluded_segments'] = $notification->getExcludedSegments();
        }

        if ($notification->getUrl()) {
            $notificationData['url'] = $notification->getUrl();
        }

        if ($notification->getData()) {
            $notificationData['data'] = $notification->getData();
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
            $this->errorMessage = join('; ', $response['error']);
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