<?php

declare( strict_types=1 );

namespace Assistenzde\DatabaseTimezoneBundle\UnitTest\Doctrine\DBAL\Type;

use Assistenzde\DatabaseTimezoneBundle\Doctrine\DBAL\Type\DateType;
use Assistenzde\DatabaseTimezoneBundle\Doctrine\DBAL\Type\TimezonedTypeInterface;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use ReflectionMethod;

/**
 * Class DateTypeTest
 */
final class DateTypeTest extends AbstractTimezonedTypeTest
{
    /**
     * {@inheritDoc}
     */
    protected function createTimzonedType(): TimezonedTypeInterface
    {
        return new DateType();
    }


    public function testConvertToDatabaseValue()
    {
        $dateType = new DateType();

        $reflectionSetterMethod = new ReflectionMethod(DateType::class, 'setDatabaseTimezone');
        $reflectionSetterMethod->setAccessible(true);

        // 1. set a timezone for all date/datetime values to save in the database
        $reflectionSetterMethod->invoke($dateType, new DateTimeZone('America/Nassau'));

        // 2. create a new time zone object (with UTC)
        $dateTimeZone = new DateTimeZone('UTC');
        $datetime     = date_create('2020-11-24 00:00:00', $dateTimeZone);

        // 3. convert to database value (which converts the datetime object to America/Nassau time zone
        //    but keeps the original datetime object in the original timezone (UTC)
        /** @var string $databaseValue */
        $databaseValue = $dateType->convertToDatabaseValue($datetime, self::$DatabasePlatform);

        // 4. check if the origin date time has not changed
        self::assertEquals('2020-11-24 00:00:00', $datetime->format('Y-m-d H:i:s'));

        // 5. check if the date time values (as string) has changed
        // the converted database date (only available as string) equals the origin datetime
        // because date and time is the same (timezone does not matter)
        self::assertNotEquals($databaseValue, $datetime->format('Y-m-d H:i:s'));
        self::assertEquals('2020-11-24', $databaseValue);
        self::assertEquals(0, $datetime->diff(date_create($databaseValue, $dateTimeZone))->h);
    }

    /**
     * @throws Exception
     */
    public function testConvertValidToPHPValue()
    {
        $dateType = new DateType();

        $reflectionSetterMethod = new ReflectionMethod(DateType::class, 'setDatabaseTimezone');
        $reflectionSetterMethod->setAccessible(true);

        // 1. backup the default server timezone
        $backupDefaultTimezone = date_default_timezone_get();

        // 2. set a new default timezone
        date_default_timezone_set('Europe/Berlin');

        // 3. set a new database timezone
        $reflectionSetterMethod->invoke($dateType, new DateTimeZone('Indian/Mauritius'));

        // 4. convert database date time (Indian/Mauritius time zone) to server date time (time zone Europe/Berlin)
        /** @var DateTimeInterface $datetime */
        $datetime = $dateType->convertToPHPValue('2020-11-24', self::$DatabasePlatform);

        // 5. there are 3 hours difference between Mauritius and Berlin
        self::assertEquals('2020-11-24 00:00:00', $datetime->format('Y-m-d H:i:s'));
        self::assertEquals('Europe/Berlin', $datetime->getTimezone()->getName());

        // 6. restore original timezone
        date_default_timezone_set($backupDefaultTimezone);
    }
}
