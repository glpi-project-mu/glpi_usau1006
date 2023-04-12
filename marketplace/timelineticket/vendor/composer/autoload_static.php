<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit142cd86914a51f9309eb3413eeabb709
{
    public static $files = array (
        '7bb4f001eb5212bde073bf47a4bbedad' => __DIR__ . '/..' . '/szymach/c-pchart/constants.php',
    );

    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'CpChart\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'CpChart\\' => 
        array (
            0 => __DIR__ . '/..' . '/szymach/c-pchart/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit142cd86914a51f9309eb3413eeabb709::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit142cd86914a51f9309eb3413eeabb709::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit142cd86914a51f9309eb3413eeabb709::$classMap;

        }, null, ClassLoader::class);
    }
}
