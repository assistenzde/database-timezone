<?php

declare( strict_types=1 );

namespace Assistenzde\DatabaseTimezoneBundle\Doctrine\DBAL\Type;

use DateTimeZone;

/**
 * Class DateTimeType is used to save all datetime values in database as a custom timezone.
 *
 * @link http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/working-with-datetime.html
 */
trait TimezonedTypeTrait
{
    /**
     * @var DateTimeZone|null
     */
    protected static ?DateTimeZone $databaseTimezone = null;

    /**
     * @var DateTimeZone|null
     */
    protected static ?DateTimeZone $defaultTimezone = null;

    /**
     * @param DateTimeZone $dateTimeZone
     */
    public static function setDatabaseTimezone(DateTimeZone $dateTimeZone)
    {
        self::$databaseTimezone = $dateTimeZone;
    }

    /**
     * @return DateTimeZone
     */
    public static function getDatabaseTimezone(): DateTimeZone
    {
        if( is_null(self::$databaseTimezone) )
        {
            self::$databaseTimezone = new DateTimeZone(date_default_timezone_get());
        }
        return self::$databaseTimezone;
    }

    /**
     * @return DateTimeZone
     */
    public static function getDefaultTimezone(): DateTimeZone
    {
        if( is_null(self::$defaultTimezone) )
        {
            self::$defaultTimezone = new DateTimeZone(date_default_timezone_get());
        }
        return self::$defaultTimezone;
    }
}
