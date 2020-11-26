<?php

declare( strict_types=1 );

namespace Assistenzde\DatabaseTimezoneBundle\DependencyInjection;

use DateTimeZone;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('datetime');

        // @formatter:off
        $treeBuilder->getRootNode()
            ->info('Database timezone bundle configuration')
                ->children()
                    ->scalarNode('database')
                        ->info('The timezone of the datetime values which are saved and will be saved in the database. If empty, the server timezone (see `date_default_timezone_get`) wil be chosen.')
                        ->defaultNull()
                        ->cannotBeEmpty()
                        ->validate()
                            ->ifTrue(function ($_value) { return $this->isInvalidTimezone($_value); })
                            ->thenInvalid('Invalid timezone "%s" for config parameter "datetime.database" set. Please check https://www.php.net/manual/en/timezones.america.php.')
                        ->end()
                    ->end()
                ->end()
            ->end();
        // @formatter:on

        return $treeBuilder;
    }

    /**
     * Returns `true` if the submitted string is *not* a valid timezone, else `false`.
     *
     * @param string|null $timezoneName
     *
     * @return boolean
     *
     * @link http://us.php.net/manual/en/timezones.others.php
     *
     */
    protected function isInvalidTimezone(?string $timezoneName): bool
    {
        return is_null($timezoneName) ? false : ( !in_array($timezoneName, DateTimeZone::listIdentifiers()) );
    }
}
