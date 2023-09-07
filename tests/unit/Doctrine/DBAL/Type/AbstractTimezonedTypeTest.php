<?php

declare( strict_types=1 );

namespace Assistenzde\DatabaseTimezoneBundle\UnitTest\Doctrine\DBAL\Type;

use Assistenzde\DatabaseTimezoneBundle\Doctrine\DBAL\Type\DateTimeType;
use Assistenzde\DatabaseTimezoneBundle\Doctrine\DBAL\Type\DateType;
use Assistenzde\DatabaseTimezoneBundle\Doctrine\DBAL\Type\TimezonedTypeInterface;
use Assistenzde\DatabaseTimezoneBundle\Doctrine\DBAL\Type\TimezonedTypeTrait;
use Assistenzde\DatabaseTimezoneBundle\UnitTest\KernelTestCase;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Exception;
use ReflectionMethod;
use ReflectionProperty;
use TypeError;

/**
 * Class DateTypeTest
 */
abstract class AbstractTimezonedTypeTest extends KernelTestCase
{
    /**
     * @var AbstractPlatform
     */
    protected static AbstractPlatform $DatabasePlatform;

    /**
     * @var TimezonedTypeInterface|null
     */
    protected ?TimezonedTypeInterface $timezonedType = null;

    /**
     * {@inheritDoc}
     */
    public static function setUpBeforeClass(): void
    {
        self::$DatabasePlatform = new MySqlPlatform();
    }

    /**
     * Retruns an object of the date*type class to test.
     *
     * @return TimezonedTypeInterface
     */
    abstract protected function createTimzonedType(): TimezonedTypeInterface;

    /**
     * @return TimezonedTypeInterface
     */
    protected function createTimzonedObject(): TimezonedTypeInterface
    {
        $timezonedType = $this->createTimzonedType();

        $reflectionPropertyDatabaseTimezone = new ReflectionProperty(TimezonedTypeTrait::class, 'databaseTimezone');
        $reflectionPropertyDatabaseTimezone->setAccessible(true);
        $reflectionPropertyDatabaseTimezone->setValue($timezonedType, null);

        $reflectionPropertyDefaultTimezone = new ReflectionProperty(TimezonedTypeTrait::class, 'defaultTimezone');
        $reflectionPropertyDefaultTimezone->setAccessible(true);
        $reflectionPropertyDefaultTimezone->setValue($timezonedType, null);

        return $timezonedType;
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

        $this->timezonedType = $this->createTimzonedObject();
    }

    public function testGetDefaultTimezone()
    {
        $reflectionMethod = new ReflectionMethod(TimezonedTypeTrait::class, 'getDefaultTimezone');
        $reflectionMethod->setAccessible(true);

        // 1. backup the default server timezone
        $backupDefaultTimezone = date_default_timezone_get();

        // 2. set a custom server timezone
        date_default_timezone_set('Europe/Berlin');

        // 3. check if default server timezone is used as timezone
        /** @var DateTimeZone $defaultDateTimeZone */
        $defaultDateTimeZone = $reflectionMethod->invoke($this->timezonedType);
        self::assertEquals(date_default_timezone_get(), $defaultDateTimeZone->getName());

        // 4. restore original timezone
        date_default_timezone_set($backupDefaultTimezone);
    }

    public function testGetDatabaseTimezone()
    {
        $reflectionMethodGetter = new ReflectionMethod(TimezonedTypeTrait::class, 'getDatabaseTimezone');
        $reflectionMethodGetter->setAccessible(true);

        // 1. backup the default server timezone
        $backupDefaultTimezone = date_default_timezone_get();

        // 2. set a new default timezone
        date_default_timezone_set('Indian/Mauritius');

        // 3. check if default server timezone is used as database timezone
        /** @var DateTimeZone $databaseDateTimeZone */
        $databaseDateTimeZone = $reflectionMethodGetter->invoke($this->timezonedType);
        self::assertEquals('Indian/Mauritius', $databaseDateTimeZone->getName());

        // 4. restore original timezone
        date_default_timezone_set($backupDefaultTimezone);
    }

    public function testSetDatabaseTimezone()
    {
        $reflectionGetterMethod = new ReflectionMethod(TimezonedTypeTrait::class, 'getDatabaseTimezone');
        $reflectionGetterMethod->setAccessible(true);
        $reflectionSetterMethod = new ReflectionMethod(TimezonedTypeTrait::class, 'setDatabaseTimezone');
        $reflectionSetterMethod->setAccessible(true);

        // 1. set a custom database timezone
        $reflectionSetterMethod->invoke($this->timezonedType, new DateTimeZone('Asia/Jerusalem'));

        // 2. check if custom server timezone is used
        $databaseDateTimeZone = $reflectionGetterMethod->invoke($this->timezonedType);
        self::assertEquals('Asia/Jerusalem', $databaseDateTimeZone->getName());
    }

    public function testInvalidSetDatabaseTimezone()
    {
        $reflectionSetterMethod = new ReflectionMethod(TimezonedTypeTrait::class, 'setDatabaseTimezone');
        $reflectionSetterMethod->setAccessible(true);

        self::expectException(TypeError::class);
        $reflectionSetterMethod->invoke($this->timezonedType, 117.8);
    }


    /**
     * For all {@see TimezonedTypeInterface} objects: `NULL` database values convert to `null` in PHP.
     *
     * @throws Exception
     */
    public function testConvertNullToPHPValue()
    {
        self::assertNull($this->timezonedType->convertToPHPValue(null, self::$DatabasePlatform));
    }

    /**
     * @throws ConversionException
     */
    public function testInvalidConvertToDatabaseValue()
    {
        $dateType = new DateType();

        self::expectException(ConversionException::class);
        $dateType->convertToDatabaseValue(654685, self::$DatabasePlatform);
    }

    /**
     * For all {@see TimezonedTypeInterface} objects: Converting an invalid database value throws an exception in PHP.
     *
     * @throws Exception
     */
    public function testConvertInvalidToPHPValue()
    {
        $dateType = new DateType();

        $this->expectException(ConversionException::class);
        $dateType->convertToPHPValue('no valid datetime', self::$DatabasePlatform);
    }

    /**
     * For all {@see TimezonedTypeInterface} objects: An object which was already converted to a {@see DateTimeInterface} must not be converted twice.
     *
     * @throws Exception
     */
    public function testConvertDateTimeToPHPValue()
    {
        $dateType = new DateType();

        $reflectionSetterMethod = new ReflectionMethod(DateType::class, 'setDatabaseTimezone');
        $reflectionSetterMethod->setAccessible(true);

        $dateTime = date_create();

        /** @var DateTimeInterface $datetime */
        // the passed $dateTime is already converted, so the same value must be returned
        $convertedDateTime = $dateType->convertToPHPValue($dateTime, self::$DatabasePlatform);
        self::assertSame($dateTime, $convertedDateTime);
    }
}
