<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit05456559e610a8f8136b8c8b764b2de5
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Http\\Message\\' => 17,
            'Psr\\Http\\Client\\' => 16,
        ),
        'M' => 
        array (
            'Mzerm\\UniversalMessageHub\\' => 26,
        ),
        'J' => 
        array (
            'Jose\\Component\\KeyManagement\\' => 29,
            'Jose\\Component\\Core\\' => 20,
        ),
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
            'FG\\' => 3,
        ),
        'B' => 
        array (
            'Brick\\Math\\' => 11,
            'Base64Url\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-factory/src',
            1 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'Psr\\Http\\Client\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-client/src',
        ),
        'Mzerm\\UniversalMessageHub\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Jose\\Component\\KeyManagement\\' => 
        array (
            0 => __DIR__ . '/..' . '/web-token/jwt-key-mgmt',
        ),
        'Jose\\Component\\Core\\' => 
        array (
            0 => __DIR__ . '/..' . '/web-token/jwt-core',
        ),
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
        'FG\\' => 
        array (
            0 => __DIR__ . '/..' . '/fgrosse/phpasn1/lib',
        ),
        'Brick\\Math\\' => 
        array (
            0 => __DIR__ . '/..' . '/brick/math/src',
        ),
        'Base64Url\\' => 
        array (
            0 => __DIR__ . '/..' . '/spomky-labs/base64url/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit05456559e610a8f8136b8c8b764b2de5::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit05456559e610a8f8136b8c8b764b2de5::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit05456559e610a8f8136b8c8b764b2de5::$classMap;

        }, null, ClassLoader::class);
    }
}
