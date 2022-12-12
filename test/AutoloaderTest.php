<?php

namespace LaminasTest\ZendFrameworkBridge;

use Laminas\LegacyTypeHint;
use Laminas\ZendFrameworkBridge\Autoloader;
use PHPUnit\Framework\TestCase;

use function class_exists;
use function clearstatcache;
use function file_exists;
use function get_class;
use function interface_exists;
use function rename;

class AutoloaderTest extends TestCase
{
    private const PATH_TO_AUTOLOADER = __DIR__ . '/../vendor/autoload.php';

    /**
     * @return list<array{0: string, 1: string, 2?:bool}>
     */
    public static function classProvider(): array
    {
        return [
            // phpcs:disable Generic.Files.LineLength.TooLong
            // Expressive
            ['Zend\Expressive\Application',                                             'Mezzio\Application'],
            ['Zend\Expressive\Authentication\Authentication',                           'Mezzio\Authentication\Authentication'],
            ['Zend\Expressive\Authentication\ZendAuthentication\AuthenticationAdapter', 'Mezzio\Authentication\LaminasAuthentication\AuthenticationAdapter'],
            ['Zend\Expressive\Authentication\ZendAuthentication\ZendAuthentication',    'Mezzio\Authentication\LaminasAuthentication\LaminasAuthentication'],
            ['Zend\Expressive\Authorization\Authorization',                             'Mezzio\Authorization\Authorization'],
            ['Zend\Expressive\Authorization\Acl\ZendAclFactory',                        'Mezzio\Authorization\Acl\LaminasAclFactory'],
            ['Zend\Expressive\Authorization\Rbac\ZendRbac',                             'Mezzio\Authorization\Rbac\LaminasRbac'],
            ['Zend\Expressive\Router\Router',                                           'Mezzio\Router\Router'],
            ['Zend\Expressive\Router\ZendRouter',                                       'Mezzio\Router\LaminasRouter'],
            ['Zend\Expressive\Router\ZendRouter\RouterAdapter',                         'Mezzio\Router\LaminasRouter\RouterAdapter'],
            ['Zend\Expressive\ZendView\ZendViewRenderer',                               'Mezzio\LaminasView\LaminasViewRenderer'],
            ['Zend\ProblemDetails\ProblemDetails',                                      'Mezzio\ProblemDetails\ProblemDetails'],
            ['Zend\Expressive\Hal\LinkGenerator\ExpressiveUrlGenerator',                'Mezzio\Hal\LinkGenerator\MezzioUrlGenerator'],
            // phpcs:enable

            // Laminas
            ['Zend\Cache\Storage\Adapter\AbstractZendServer', 'Laminas\Cache\Storage\Adapter\AbstractZendServer'],
            ['Zend\Cache\Storage\Adapter\ZendServerDisk',     'Laminas\Cache\Storage\Adapter\ZendServerDisk'],
            ['Zend\Cache\Storage\Adapter\ZendServerShm',      'Laminas\Cache\Storage\Adapter\ZendServerShm'],
            ['Zend\Expressive',                               'Laminas\Mezzio'],
            ['Zend\Log\Writer\ZendMonitor',                   'Laminas\Log\Writer\ZendMonitor'],
            ['Zend\Main',                                     'Laminas\Main'],
            ['Zend\Psr7Bridge\Psr7Bridge',                    'Laminas\Psr7Bridge\Psr7Bridge'],
            ['Zend\Psr7Bridge\ZendBridge',                    'Laminas\Psr7Bridge\LaminasBridge'],
            ['Zend\Psr7Bridge\Zend\Psr7Bridge',               'Laminas\Psr7Bridge\Laminas\Psr7Bridge'],
            ['Zend\Psr7Bridge\Zend\ZendBridge',               'Laminas\Psr7Bridge\Laminas\LaminasBridge'],
            ['ZendService\ReCaptcha\MyClass',                 'Laminas\ReCaptcha\MyClass'],
            ['ZendService\Twitter\MyClass',                   'Laminas\Twitter\MyClass'],
            ['ZendXml\XmlService',                            'Laminas\Xml\XmlService'],
            ['ZendOAuth\OAuthService',                        'Laminas\OAuth\OAuthService'],
            ['ZendDiagnostics\Tools',                         'Laminas\Diagnostics\Tools'],
            ['ZendDeveloperTools\Tools',                      'Laminas\DeveloperTools\Tools'],
            ['ZF\ComposerAutoloading\Autoloading',            'Laminas\ComposerAutoloading\Autoloading'],
            ['ZF\DevelopmentMode\DevelopmentMode',            'Laminas\DevelopmentMode\DevelopmentMode'],

            // Apigility
            // phpcs:disable Generic.Files.LineLength.TooLong
            ['ZF\Apigility\BaseModule',        'Laminas\ApiTools\BaseModule'],
            ['ZF\BaseModule',                  'Laminas\ApiTools\BaseModule'],
            ['ZF\Apigility\Admin\Controller\ApigilityVersionController', 'Laminas\ApiTools\Admin\Controller\ApiToolsVersionController'],
            ['ZF\Apigility\ApigilityModuleInterface', 'Laminas\ApiTools\ApiToolsModuleInterface', true],
            ['ZF\Apigility\Provider\ApigilityProviderInterface', 'Laminas\ApiTools\Provider\ApiToolsProviderInterface', true],
            // phpcs:enable
        ];
    }

    /** @dataProvider classProvider */
    public function testLegacyClassIsAliasToLaminas(string $legacy, string $actual, bool $isInterface = false): void
    {
        self::assertTrue($isInterface ? interface_exists($legacy) : class_exists($legacy));
        if (! $isInterface) {
            self::assertSame($actual, get_class(new $legacy()));
        }
    }

    public function testTypeHint(): void
    {
        self::assertTrue(class_exists('Laminas\LegacyTypeHint'));
        new LegacyTypeHint(new \Laminas\Example());
    }

    /**
     * @psalm-return array<array-key, array{0: string, 1: string}>
     */
    public static function reverseClassProvider(): array
    {
        return [
            // Apigility
            ['Laminas\ApiTools\Admin\Example',         'ZF\Apigility\Admin\Example'],
            ['Laminas\ApiTools\Doctrine\Example',      'ZF\Apigility\Doctrine\Example'],
            ['Laminas\ApiTools\Documentation\Example', 'ZF\Apigility\Documentation\Example'],
            ['Laminas\ApiTools\Example\Example',       'ZF\Apigility\Example\Example'],
            ['Laminas\ApiTools\Provider\Example',      'ZF\Apigility\Provider\Example'],
            ['Laminas\ApiTools\Welcome\Example',       'ZF\Apigility\Welcome\Example'],
            ['Laminas\ApiTools\Other\ApiToolsClass',   'ZF\Other\ApigilityClass'],
            ['Laminas\ApiTools\Other\Example',         'ZF\Other\Example'],
            ['Laminas\ApiTools\Example',               'ZF\Example'],

            // Expressive
            ['Mezzio\ProblemDetails\Example', 'Zend\ProblemDetails\Example'],
            ['Mezzio\Other\Example',          'Zend\Expressive\Other\Example'],
            ['Mezzio\Other\MezzioClass',      'Zend\Expressive\Other\ExpressiveClass'],
            ['Mezzio\Example',                'Zend\Expressive\Example'],

            // Laminas
            ['Laminas\ReCaptcha\Example',                        'ZendService\ReCaptcha\Example'],
            ['Laminas\Twitter\Example',                          'ZendService\Twitter\Example'],
            ['Laminas\Cache\Storage\Adapter\AbstractZendServer', 'Zend\Cache\Storage\Adapter\AbstractZendServer'],
            ['Laminas\Cache\Storage\Adapter\ZendServerDisk',     'Zend\Cache\Storage\Adapter\ZendServerDisk'],
            ['Laminas\Cache\Storage\Adapter\ZendServerShm',      'Zend\Cache\Storage\Adapter\ZendServerShm'],
            ['Laminas\ComposerAutoloading\Example',              'ZF\ComposerAutoloading\Example'],
            ['Laminas\DevelopmentMode\Example',                  'ZF\DevelopmentMode\Example'],
            ['Laminas\Diagnostics\Example',                      'ZendDiagnostics\Example'],
            ['Laminas\Log\Writer\ZendMonitor',                   'Zend\Log\Writer\ZendMonitor'],
            ['Laminas\OAuth\Example',                            'ZendOAuth\Example'],
            ['Laminas\Xml\Example',                              'ZendXml\Example'],
            ['Laminas\Other\LaminasExample',                     'Zend\Other\ZendExample'],
            ['Laminas\Other\Example',                            'Zend\Other\Example'],
            ['Laminas\Example',                                  'Zend\Example'],
            ['Laminas\DeveloperTools\Example',                   'ZendDeveloperTools\Example'],
            ['Laminas\Router\LaminasRouterClass',                'Zend\Router\ZendRouterClass'],
        ];
    }

    /** @dataProvider reverseClassProvider */
    public function testReverseAliasCreated(string $actual, string $legacy): void
    {
        self::assertTrue(class_exists($actual));
        self::assertTrue(class_exists($legacy));
    }

    public function testCanHandleNonExistentAutoloadFile(): void
    {
        self::assertTrue(file_exists(self::PATH_TO_AUTOLOADER));
        $pathToAutoloaderBackup = sprintf('%s.bak', self::PATH_TO_AUTOLOADER);
        rename(self::PATH_TO_AUTOLOADER, $pathToAutoloaderBackup);
        clearstatcache();
        self::assertFalse(file_exists(self::PATH_TO_AUTOLOADER));

        try {
            Autoloader::load();
        } finally {
            rename($pathToAutoloaderBackup, self::PATH_TO_AUTOLOADER);
        }

        clearstatcache();
        self::assertTrue(file_exists(self::PATH_TO_AUTOLOADER));
    }
}
