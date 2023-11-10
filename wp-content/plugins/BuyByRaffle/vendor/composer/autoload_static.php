<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit55297975f168d517e05d3d1c240601de
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Sgs\\Buybyraffle\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Sgs\\Buybyraffle\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit55297975f168d517e05d3d1c240601de::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit55297975f168d517e05d3d1c240601de::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit55297975f168d517e05d3d1c240601de::$classMap;

        }, null, ClassLoader::class);
    }
}