<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd58efa4c413fc0100d197a4c858eefdd
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'Webkul\\Shop\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Webkul\\Shop\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd58efa4c413fc0100d197a4c858eefdd::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd58efa4c413fc0100d197a4c858eefdd::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitd58efa4c413fc0100d197a4c858eefdd::$classMap;

        }, null, ClassLoader::class);
    }
}