# one-signal-bundle
Symfony2 bundle for sending push notifications with OneSignal

## Installation

### Step 1: Composer require

	$ php composer.phar require "norkunas/onesignal-php-api":"1.0.x-dev" "dmytrof/push-notification-bundle"

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

    # app/config/config.yml
    dmytrof_push_notification:
	    provider: one_signal
	    one_signal:													# See: https://documentation.onesignal.com/docs/web-push-sdk-setup-http
	        app_id: "%one_signal.app_id%"
	        app_auth_key: "%one_signal.auth_key%"
	        safari_web_id: "%one_signal.safari_web_id%" 	# Safari Support (Optional). See: https://documentation.onesignal.com/docs/web-push-sdk-setup-http#section-3-safari-support-optional-
	        subdomain: "%one_signal.subdomain%"				# HTTP Setup ONLY. See https://documentation.onesignal.com/docs/web-push-sdk-setup-http#section-1-4-choose-subdomain


## Usage

### Web Push SDK Setup

	{# layout.html.twig #}
	<head>
	{# ... #}

	{{ dmytrof_push_notification_web_sdk() }}
	</head>


### Tag your users

	<?php
	// SomeController

	public function addTagAction()
	{
		// ...

		$this->get('dmytrof_push_notification.provider')->addTag('tagName', 'tagValue');

    	// ...
	}

	public function addTagsAction()
	{
		// ...

		$this->get('dmytrof_push_notification.provider')->addTags([
			'tagName1' => 'value1',
			'tagName2' => 'value2'
		]);

    	// ...
	}

Users will be tagged after rendering of layout with Web Push SDK

### Remove tags

	<?php
	// SomeController

	public function removeTagsAction()
	{
		// ...

		$this->get('dmytrof_push_notification.provider')->removeTags([
			'tagName1',
			'tagName2'
		]);

    	// ...
	}

Tags will be removed after rendering of layout with Web Push SDK

### Send Push Notification

	<?php
	// SomeController

	public function sendNotificationAction()
	{
		// Send to all

		$provider = $this->get('dmytrof_push_notification.provider');
		$message = $provider->createMessage()
									->setSubject('Message Subject')
									->setContent('Test Message for all')
									->includeSegments(['All']);

		$provider->sendMessage($message);


    	// Filter by tags

    	$message = $provider->createMessage()
									->setSubject('Message Subject')
									->setContent('Filtered by tags')
									->filterByTag('tagName1', '=', 'value1')
									->filterByTag('tagName2', '=', 'value2', true);
      	$provider->sendMessage($message);


		// Send templated message to user

		$message = $provider->createMessage()
									->setTemplate('AppBundle:PushNotification:test_message.html.twig', [
										'user' => $user
									])
									->filterByTag('userId', '=', $user->getId());
		$provider->sendMessage($message);
	}

Configure message template:

	{#} 'AppBundle:PushNotification:test_message.html.twig' {#}
	{% extends 'DmytrofPushNotificationBundle:PushNotification:layout.html.twig' %}

	{% block Subject %}
		Test templated subject
	{% endblock %}

	{% block Message %}
		Youruser ID: {{ user.id }}
	{% endblock %}

	{% block Url %}
		{{ absolute_url(path('YOUR_ROUTE')) }}
	{% endblock %}


