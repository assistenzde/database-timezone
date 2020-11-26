<?php

declare( strict_types=1 );

namespace Assistenzde\DatabaseTimezoneBundle\UnitTest\Event;

use Assistenzde\DatabaseTimezoneBundle\Doctrine\DBAL\Type\DateTimeType;
use Assistenzde\DatabaseTimezoneBundle\Event\KernelEvent;
use Assistenzde\DatabaseTimezoneBundle\UnitTest\KernelTestCase;
use ReflectionMethod;
use ReflectionProperty;

final class KernelEventTest extends KernelTestCase
{
    /**
     * @var KernelEvent
     */
    protected KernelEvent $kernelEvent;

    /**
     * @var DateTimeType
     */
    protected DateTimeType $dateTimeType;

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

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->kernelEvent = self::$container->get(KernelEvent::class);

        $this->dateTimeType = $this->createDateTimeType();
    }

    public function testConstructor()
    {
        $reflectionPropertyTimezoneName = new ReflectionProperty(KernelEvent::class, 'timezoneName');
        $reflectionPropertyTimezoneName->setAccessible(true);
        self::assertEquals('Pacific/Tahiti', $reflectionPropertyTimezoneName->getValue($this->kernelEvent));
    }

    /**
     * Public method called
     * - before controller action and
     * - before console command.
     */
    public function testUpdateKernelTimezone()
    {
        $reflectionGetterMethod = new ReflectionMethod(DateTimeType::class, 'getDatabaseTimezone');
        $reflectionGetterMethod->setAccessible(true);

        // 1. backup the default server timezone
        $backupDefaultTimezone = date_default_timezone_get();

        // 2. set a new default timezone
        date_default_timezone_set('Africa/Kampala');

        // 3. check if default server timezone is used as database timezone
        $databaseDateTimeZone = $reflectionGetterMethod->invoke($this->dateTimeType);
        self::assertEquals('Africa/Kampala', $databaseDateTimeZone->getName());

        // 4. update kernel date time with configured timezone from config
        // {@see \Assistenzde\DatabaseTimezoneBundle\UnitTest\UnitTestKernel::configureContainer()}
        $this->kernelEvent->updateKernelTimezone();
        $databaseDateTimeZone = $reflectionGetterMethod->invoke($this->dateTimeType);
        self::assertEquals('Pacific/Tahiti', $databaseDateTimeZone->getName());

        // 4. restore original timezone
        date_default_timezone_set($backupDefaultTimezone);
    }

}
