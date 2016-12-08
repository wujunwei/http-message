<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb2db2f4c38aa2655b3c9d2fee288790f
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Http\\Message\\' => 17,
        ),
        'H' => 
        array (
            'Http\\Message\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'Http\\Message\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb2db2f4c38aa2655b3c9d2fee288790f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb2db2f4c38aa2655b3c9d2fee288790f::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
