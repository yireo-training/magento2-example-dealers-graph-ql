<?php
declare(strict_types=1);

namespace Yireo\ExampleDealersGraphQl\Test\Integration;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\ModuleList;

use Magento\TestFramework\Helper\Bootstrap;

use PHPUnit\Framework\TestCase;

/**
 * Class ModuleTest
 * @package Yireo\Yireo_ExampleDealersGraphQl\Test\Integration
 */
class ModuleTest extends TestCase
{
    const MODULE_NAME = 'Yireo_ExampleDealersGraphQl';

    /**
     * Test if the module is registered
     */
    public function testIfModuleIsRegistered()
    {
        $registrar = new ComponentRegistrar();
        $paths = $registrar->getPaths(ComponentRegistrar::MODULE);
        $this->assertArrayHasKey(self::MODULE_NAME, $paths);
    }

    /**
     * Test if the module is known and enabled
     */
    public function testIfModuleIsKnownAndEnabled()
    {
        $objectManager = Bootstrap::getObjectManager();
        $moduleList = $objectManager->create(ModuleList::class);
        $this->assertTrue($moduleList->has(self::MODULE_NAME));
    }
}

