<?php

namespace Dmytrof\PushNotificationBundle;

/**
 * Common package exception interface to allow
 * users of caching only this package specific
 * exceptions thrown
 *
 */
interface Exception
{
    /**
     * Following best practices for PHP5.3 package exceptions.
     * All exceptions thrown in this package will have to implement this interface
     */
}
