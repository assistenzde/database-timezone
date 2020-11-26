<?php

declare( strict_types=1 );

namespace Assistenzde\DatabaseTimezoneBundle\Event;

use Assistenzde\DatabaseTimezoneBundle\Doctrine\DBAL\Type\DateTimeType;

class KernelEvent
{
    /**
     * @var string
     */
    protected string $timezoneName = '';

    /**
     * KernelEvent constructor.
     *
     * @param string $timezoneName
     */
    public function __construct(string $timezoneName)
    {
        $this->timezoneName = $timezoneName;
    }

    /**
     * Public method called
     * - before controller action and
     * - before console command.
     */
    public function updateKernelTimezone()
    {
        DateTimeType::setDatabaseTimezone(new \DateTimeZone($this->timezoneName));
    }
}
