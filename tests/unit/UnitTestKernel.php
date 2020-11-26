<?php

declare( strict_types=1 );

namespace Assistenzde\DatabaseTimezoneBundle\UnitTest;

use Assistenzde\DatabaseTimezoneBundle\DatabaseTimezoneBundle;
use Assistenzde\DatabaseTimezoneBundle\Event\KernelEvent;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class UnitTestKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        $debug = array_key_exists('TEST_DEBUG', $_ENV) && boolval($_ENV[ 'TEST_DEBUG' ]);
        parent::__construct('test', $debug);
    }

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new DatabaseTimezoneBundle(),
        ];
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        // load parameter
        $container->loadFromExtension('database_timezone', [
            'database' => 'Pacific/Tahiti',
        ]);

        // register kernel event
        $container->register(KernelEvent::class, KernelEvent::class)
            ->addTag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'updateKernelTimezone'])
            ->addTag('kernel.event_listener', ['event' => 'console.command', 'method' => 'updateKernelTimezone'])
            ->setArguments(['%database_timezone.database%'])
            ->setPublic(true);
    }
}
