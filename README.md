# one-signal-bundle
Symfony2 bundle for sending push notifications with OneSignal

## Installation

### Step 1: Composer require

    $ php composer.phar require "norkunas/onesignal-php-api":"1.0.x-dev" "dmytrof/push-notification-bundle":"0.x-dev"

### Step2: Enable the bundle in the kernel

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Dmytrof\PushNotificationBundle\DmytrofPushNotificationBundle(),
            // ...
        );
    }

## Configuration

Enable the bundle in your config.yml:

    # config.yml
    dmytrof_push_notification:
    provider: one_signal
    one_signal:
        app_id: "%one_signal.app_id%"
        app_auth_key: "%one_signal.auth_key%"
        subdomain: "%one_signal.subdomain%"
