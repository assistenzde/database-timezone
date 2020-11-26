<?php

declare( strict_types=1 );

namespace Assistenzde\DatabaseTimezoneBundle\DependencyInjection;

use Assistenzde\DatabaseTimezoneBundle\Doctrine\DBAL\Type\DateTimeType;
use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 *
 * @link http://symfony.com/doc/current/bundles/extension.html
 */
class DatabaseTimezoneBundleExtension extends Extension implements PrependExtensionInterface
{

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return 'database_timezone';
    }

    public function prepend(ContainerBuilder $container)
    {
        // get all bundles
        $bundles = $container->getParameter('kernel.bundles');


        // determine if doctrine is registered
        if( !array_key_exists('DoctrineBundle', $bundles) )
        {
            return;
        }

        foreach( $container->getExtensionConfig('doctrine') as $_config )
        {
            if( !array_key_exists('dbal', $_config) )
            {
                $_config[ 'dbal' ] = [];
            }

            if( !array_key_exists('types', $_config[ 'dbal' ]) )
            {
                $_config[ 'dbal' ][ 'types' ] = [];
            }

            if( array_key_exists('datetime', $_config[ 'dbal' ][ 'types' ]) )
            {
                throw new \RuntimeException('DBAL type "datetime" was already set - please check config parameter "doctrine.dbal.types.datetime".');
            }
            elseif( array_key_exists('datetimetz', $_config[ 'dbal' ][ 'types' ]) )
            {
                throw new \RuntimeException('DBAL type "datetimetz" was already set - please check config parameter "doctrine.dbal.types.datetimetz".');
            }

            $_config[ 'dbal' ][ 'types' ][ 'datetime' ]   = DateTimeType::class;
            $_config[ 'dbal' ][ 'types' ][ 'datetimetz' ] = DateTimeType::class;

            $container->prependExtensionConfig('doctrine', $_config);
        }

    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        /** @var LoaderInterface $loader */
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        // the timezone parameter has to be saved in a parameter to be loaded via an event listener
        // all other approaches (i.e. load via $container->getDefinition()->addMethodCall()) have failed
        $container->setParameter('database_timezone.database', is_null($config[ 'database' ]) ? date_default_timezone_get() : $config[ 'database' ]);
    }

}
