<?php

declare( strict_types=1 );

namespace Assistenzde\DatabaseTimezoneBundle;

use Assistenzde\DatabaseTimezoneBundle\DependencyInjection\DatabaseTimezoneBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class DatabaseTimezoneBundle
 *
 * @todo integration tests
 * @todo .gitignore (.phpunit.result.cache)
 * @todo to github, to packagist
 * @todo LICENSE
 * @todo README.md (php 7.4 (datetimeimmuteable + typed properties)
 * @todo CHANGELOG
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
