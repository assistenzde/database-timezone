<?php

declare( strict_types=1 );

namespace Assistenzde\DatabaseTimezoneBundle\Doctrine\DBAL\Type;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateType as BaseDateType;

/**
 * Class DateType is used to save all datetime values in database as a custom timezone.
 *
 * @link http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/working-with-datetime.html
 */
class DateType extends BaseDateType implements TimezonedTypeInterface
{
    use TimezonedTypeTrait;

    /**
     * {@inheritDoc}
     *
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return $value;
        }

        // convert to database date time zone
        if( $value instanceof DateTimeInterface )
        {
            $value = DateTime::createFromFormat(
                $platform->getDateTimeFormatString(),
                $value->format($platform->getDateTimeFormatString()),
                static::$databaseTimezone
            );
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?DateTimeInterface
    {
        // return null value
        if( is_null($value) )
        {
            return null;
        }

        // return already converted value
        if( $value instanceof DateTimeInterface )
        {
            return $value;
        }

        // convert value to a DateTime object
        $converted = DateTime::createFromFormat(
            '!' . $platform->getDateFormatString(),
            $value,
            static::getDefaultTimezone()
        );

        if( !$converted )
        {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                $platform->getDateFormatString()
            );
        }

        return $converted;
    }
}
