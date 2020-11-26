<?php

declare( strict_types=1 );

namespace Assistenzde\DatabaseTimezoneBundle\UnitTest\Doctrine\DBAL\Type;

use Assistenzde\DatabaseTimezoneBundle\Doctrine\DBAL\Type\DateTimeType;
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
final class DateTimeTypeTest extends KernelTestCase
{
    /**
     * @var AbstractPlatform
     */
    protected static AbstractPlatform $DatabasePlatform;

    /**
     * @var DateTimeType
     */
    protected DateTimeType $dateTimeType;

    /**
     * {@inheritDoc}
     */
    public static function setUpBeforeClass(): void
    {
        self::$DatabasePlatform = new MySqlPlatform();
    }

    /**
     * @return DateTimeType
     */
    protected function createDateTimeType(): DateTimeType
    {
        $dateTimeType = new DateTimeType();

        $reflectionPropertyDatabaseTimezone = new ReflectionProperty(DateTimeType::class, 'databaseTimezone');
        $reflectionPropertyDatabaseTimezone->setAccessible(true);
        $reflectionPropertyDatabaseTimezone->setValue($dateTimeType, null);

        $reflectionPropertyDefaultTimezone = new ReflectionProperty(DateTimeType::class, 'defaultTimezone');
        $reflectionPropertyDefaultTimezone->setAccessible(true);
        $reflectionPropertyDefaultTimezone->setValue($dateTimeType, null);

        return $dateTimeType;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        if( !static::$booted )
        {
            self::bootKernel();
        }

        $this->dateTimeType = $this->createDateTimeType();
    }

    public function testGetDefaultTimezone()
    {
        $reflectionMethod = new ReflectionMethod(DateTimeType::class, 'getDefaultTimezone');
        $reflectionMethod->setAccessible(true);

        // 1. backup the default server timezone
        $backupDefaultTimezone = date_default_timezone_get();

        // 2. set a custom server timezone
        date_default_timezone_set('Europe/Berlin');

        // 3. check if default server timezone is used as timezone
        /** @var DateTimeZone $defaultDateTimeZone */
        $defaultDateTimeZone = $reflectionMethod->invoke($this->dateTimeType);
        self::assertEquals(date_default_timezone_get(), $defaultDateTimeZone->getName());

        // 4. restore original timezone
        date_default_timezone_set($backupDefaultTimezone);
    }

    public function testGetDatabaseTimezone()
    {
        $reflectionMethod = new ReflectionMethod(DateTimeType::class, 'getDatabaseTimezone');
        $reflectionMethod->setAccessible(true);

        // 1. backup the default server timezone
        $backupDefaultTimezone = date_default_timezone_get();

        // 2. set a new default timezone
        date_default_timezone_set('Indian/Mauritius');

        // 3. check if default server timezone is used as database timezone
        /** @var DateTimeZone $databaseDateTimeZone */
        $databaseDateTimeZone = $reflectionMethod->invoke($this->dateTimeType);
        self::assertEquals(date_default_timezone_get(), $databaseDateTimeZone->getName());

        // 4. restore original timezone
        date_default_timezone_set($backupDefaultTimezone);
    }

    public function testInvalidSetDatabaseTimezone()
    {
        $reflectionSetterMethod = new ReflectionMethod(DateTimeType::class, 'setDatabaseTimezone');
        $reflectionSetterMethod->setAccessible(true);

        self::expectException(TypeError::class);
        $reflectionSetterMethod->invoke($this->dateTimeType, 117.8);
    }

    public function testSetDatabaseTimezone()
    {
        $reflectionGetterMethod = new ReflectionMethod(DateTimeType::class, 'getDatabaseTimezone');
        $reflectionGetterMethod->setAccessible(true);
        $reflectionSetterMethod = new ReflectionMethod(DateTimeType::class, 'setDatabaseTimezone');
        $reflectionSetterMethod->setAccessible(true);

        // 2. set a custom server timezone
        $reflectionSetterMethod->invoke($this->dateTimeType, new DateTimeZone('Asia/Shanghai'));

        // 3. check if custom server timezone is used
        $databaseDateTimeZone = $reflectionGetterMethod->invoke($this->dateTimeType);
        self::assertEquals('Asia/Shanghai', $databaseDateTimeZone->getName());
    }

    public function testInvalidConvertToDatabaseValue()
    {
        self::expectException(ConversionException::class);
        $this->dateTimeType->convertToDatabaseValue(654685, self::$DatabasePlatform);
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToDatabaseValue()
    {
        $reflectionSetterMethod = new ReflectionMethod(DateTimeType::class, 'setDatabaseTimezone');
        $reflectionSetterMethod->setAccessible(true);

        // 1. set a timezone for all date/datetime values to save in the database
        $reflectionSetterMethod->invoke($this->dateTimeType, new DateTimeZone('America/Nassau'));

        // 2. create a new time zone object (with UTC)
        $dateTimeZone = new DateTimeZone('UTC');
        $datetime     = date_create('2020-11-24 21:00:00', $dateTimeZone);

        // 3. convert to database value (which converts the datetime object to America/Nassau time zone
        //    but keeps the original datetime object in the original timezone (UTC)
        /** @var DateTimeInterface $databaseValue */
        $databaseValue = $this->dateTimeType->convertToDatabaseValue($datetime, self::$DatabasePlatform);

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
    public function testConvertNullToPHPValue()
    {
        self::assertNull($this->dateTimeType->convertToPHPValue(null, self::$DatabasePlatform));
    }

    /**
     * @throws Exception
     */
    public function testConvertInvalidToPHPValue()
    {
        $reflectionSetterMethod = new ReflectionMethod(DateTimeType::class, 'setDatabaseTimezone');
        $reflectionSetterMethod->setAccessible(true);

        $this->expectException(Error::class);
        $this->dateTimeType->convertToPHPValue('no valid datetime', self::$DatabasePlatform);
    }

    /**
     * @throws Exception
     */
    public function testConvertDateTimeToPHPValue()
    {
        $reflectionSetterMethod = new ReflectionMethod(DateTimeType::class, 'setDatabaseTimezone');
        $reflectionSetterMethod->setAccessible(true);

        $dateTime = date_create();

        /** @var DateTimeInterface $datetime */
        $convertedDateTime = $this->dateTimeType->convertToPHPValue($dateTime, self::$DatabasePlatform);
        self::assertSame($dateTime, $convertedDateTime);
    }
    /**
     * @throws Exception
     */
    public function testConvertValidToPHPValue()
    {
        $reflectionSetterMethod = new ReflectionMethod(DateTimeType::class, 'setDatabaseTimezone');
        $reflectionSetterMethod->setAccessible(true);

        // 1. backup the default server timezone
        $backupDefaultTimezone = date_default_timezone_get();

        // 2. set a new default timezone
        date_default_timezone_set('Europe/Berlin');

        // 3. set a new database timezone
        $reflectionSetterMethod->invoke($this->dateTimeType, new DateTimeZone('Indian/Mauritius'));

        // 4. convert database date time (Indian/Mauritius time zone) to server date time (time zone Europe/Berlin)
        /** @var DateTimeInterface $datetime */
        $datetime = $this->dateTimeType->convertToPHPValue('2020-11-24 21:00:00', self::$DatabasePlatform);

        // 5. there are 3 hours difference between Mauritius and Berlin
        self::assertEquals('2020-11-24 18:00:00', $datetime->format('Y-m-d H:i:s'));
        self::assertEquals('Europe/Berlin', $datetime->getTimezone()->getName());

        // 6. restore original timezone
        date_default_timezone_set($backupDefaultTimezone);
    }
}
