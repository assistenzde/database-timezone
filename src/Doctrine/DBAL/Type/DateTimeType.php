<?php

declare( strict_types=1 );

namespace Assistenzde\DatabaseTimezoneBundle\Doctrine\DBAL\Type;

use DateTime;
use DateTimeZone;
use Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType as BaseDateTimeType;

/**
 * Class DateTimeType is used to save all datetime values in database as a custom timezone.
 *
 * @link http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/working-with-datetime.html
 */
class DateTimeType extends BaseDateTimeType implements TimezonedTypeInterface
{
    use TimezonedTypeTrait;

    /**
     * {@inheritDoc}
     *
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        // convert to database date time zone
        if( $value instanceof DateTime )
        {
            $value = clone $value;
            $value->setTimezone(static::getDatabaseTimezone());
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?DateTime
    {
        // return null value
        if( is_null($value) )
        {
            return null;
        }

        // return already converted value
        if( $value instanceof DateTime )
        {
            return $value;
        }

        // convert value to a DateTime object
        $converted = DateTime::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            static::$databaseTimezone
        )->setTimezone(static::getDefaultTimezone());

        if( !$converted )
        {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                $platform->getDateTimeFormatString()
            );
        }

        return $converted;
    }
}
