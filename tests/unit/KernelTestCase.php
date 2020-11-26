<?php

declare( strict_types=1 );

namespace Assistenzde\DatabaseTimezoneBundle\UnitTest;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;

class KernelTestCase extends BaseKernelTestCase
{
    /**
     * {@inheritDoc}
     */
    protected static function getKernelClass()
    {
        $_SERVER[ 'KERNEL_CLASS' ] = UnitTestKernel::class;

        return parent::getKernelClass();
    }
}
