<?php

declare( strict_types=1 );

namespace Assistenzde\DatabaseTimezoneBundle;

use Assistenzde\DatabaseTimezoneBundle\DependencyInjection\DatabaseTimezoneBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class DatabaseTimezoneBundle
 *
 * @todo add integration tests
 * @todo implement doctrine DBAL datetimeimmuteable type
 * @todo switch to symfony6 / php8, i.e. typed properties
 */
class DatabaseTimezoneBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function getContainerExtension()
    {
        return new DatabaseTimezoneBundleExtension();
    }
}
