<?php

declare( strict_types=1 );

namespace Assistenzde\DatabaseTimezoneBundle\UnitTest\Doctrine\DBAL\Type;

use Assistenzde\DatabaseTimezoneBundle\Doctrine\DBAL\Type\DateTimeType;
use Assistenzde\DatabaseTimezoneBundle\Doctrine\DBAL\Type\DateType;
use Assistenzde\DatabaseTimezoneBundle\Doctrine\DBAL\Type\TimezonedTypeInterface;
use Assistenzde\DatabaseTimezoneBundle\UnitTest\KernelTestCase;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Error;
use Exception;
use ReflectionMethod;
use ReflectionProperty;
use TypeError;

/**
 * Class DateTimeTypeTest
 */
final class DateTimeTypeTest extends AbstractTimezonedTypeTest
{
    /**
     * {@inheritDoc}
     */
    protected function createTimzonedType(): TimezonedTypeInterface
    {
        return new DateTimeType();
    }

    public function testConvertToDatabaseValue()
    {
        $datetimeType = new DateTimeType();
        
        $reflectionSetterMethod = new ReflectionMethod(DateTimeType::class, 'setDatabaseTimezone');
        $reflectionSetterMethod->setAccessible(true);

        // 1. set a timezone for all date/datetime values to save in the database
        $reflectionSetterMethod->invoke($datetimeType, new DateTimeZone('America/Nassau'));

        // 2. create a new time zone object (with UTC)
        $dateTimeZone = new DateTimeZone('UTC');
        $datetime     = date_create('2020-11-24 21:00:00', $dateTimeZone);

        // 3. convert to database value (which converts the datetime object to America/Nassau time zone
        //    but keeps the original datetime object in the original timezone (UTC)
        /** @var string $databaseValue */
        $databaseValue = $datetimeType->convertToDatabaseValue($datetime, self::$DatabasePlatform);

        // 4. check if the origin date time was not changed
        self::assertEquals('2020-11-24 21:00:00', $datetime->format('Y-m-d H:i:s'));

        // 5. check if the date time values (as string) has changed
        // the converted database datetime (only available as string) does not equal the origin datetime - because the time zones has changed
        // in normal (winter time) there is a difference of 5 hours between UTC and America/Nassau
        self::assertNotEquals($databaseValue, $datetime->format('Y-m-d H:i:s'));
        self::assertEquals('2020-11-24 16:00:00', $databaseValue);
        self::assertEquals(5, $datetime->diff(date_create($databaseValue, $dateTimeZone))->h);
    }
    
    /**
     * @throws Exception
     */
    public function testConvertToPHPValue()
    {
        $datetimeType = new DateTimeType();

        $reflectionSetterMethod = new ReflectionMethod(DateTimeType::class, 'setDatabaseTimezone');
        $reflectionSetterMethod->setAccessible(true);

        // 1. backup the default server timezone
        $backupDefaultTimezone = date_default_timezone_get();

        // 2. set a new default timezone
        date_default_timezone_set('Europe/Berlin');

        // 3. set a new database timezone
        $reflectionSetterMethod->invoke($datetimeType, new DateTimeZone('Indian/Mauritius'));

        // 4. convert database date time (Indian/Mauritius time zone) to server date time (time zone Europe/Berlin)
        /** @var DateTimeInterface $datetime */
        $datetime = $datetimeType->convertToPHPValue('2020-11-24 21:00:00', self::$DatabasePlatform);

        // 5. there are 3 or 4 hours difference between Mauritius and Berlin
        self::assertContains($datetime->format('Y-m-d H:i:s'), ['2020-11-24 18:00:00', '2020-11-24 17:00:00']);
        self::assertEquals('Europe/Berlin', $datetime->getTimezone()->getName());

        // 6. restore original timezone
        date_default_timezone_set($backupDefaultTimezone);
    }
}
