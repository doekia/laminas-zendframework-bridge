<?php

namespace LaminasTest\ZendFrameworkBridge;

use Laminas\ZendFrameworkBridge\Module;
use PHPUnit\Framework\TestCase;

use function sprintf;

class ModuleTest extends TestCase
{
    public function testInitRegistersListenerWithEventManager(): void
    {
        $eventManager = new TestAsset\EventManager();
        $moduleManager = new TestAsset\ModuleManager($eventManager);
        $module = new Module();

        $module->init($moduleManager);

        $this->assertSame(
            ['mergeConfig' => [[$module, 'onMergeConfig']]],
            $eventManager->getListeners()
        );
    }

    /**
     * @return iterable
     */
    public static function configurations(): iterable
    {
        yield 'Acelaya Expressive Slim Router' => ['ExpressiveSlimRouterConfig.php'];
        yield 'mwop.net App module config' => ['MwopNetAppConfig.php'];
    }

    /**
     * @dataProvider configurations
     *
     * @param string $configFile
     */
    public function testOnMergeConfigProcessesAndReplacesConfigurationPulledFromListener(string $configFile): void
    {
        $configFile = sprintf('%s/TestAsset/ConfigPostProcessor/%s', __DIR__, $configFile);
        $expectationsFile = $configFile . '.out';
        $config = require $configFile;
        $expected = require $expectationsFile;

        $listener = new TestAsset\ConfigListener($config);
        $event = new TestAsset\ModuleEvent($listener);
        $module = new Module();

        $this->assertNull($module->onMergeConfig($event));

        $this->assertSame($expected, $listener->getMergedConfig());
    }
}
