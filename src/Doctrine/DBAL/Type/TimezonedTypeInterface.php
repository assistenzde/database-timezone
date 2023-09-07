<?php

declare( strict_types=1 );

namespace Assistenzde\DatabaseTimezoneBundle\Doctrine\DBAL\Type;

use DateTimeZone;

/**
 * Class DateTimeType is used to save all datetime values in database as a custom timezone.
 *
 * @link http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/working-with-datetime.html
 */
interface TimezonedTypeInterface
{
    /**
     * @param DateTimeZone $dateTimeZone
     */
    public static function setDatabaseTimezone(DateTimeZone $dateTimeZone);

    /**
     * @return DateTimeZone
     */
    public static function getDatabaseTimezone(): DateTimeZone;

    /**
     * @return DateTimeZone
     */
    public static function getDefaultTimezone(): DateTimeZone;
}
