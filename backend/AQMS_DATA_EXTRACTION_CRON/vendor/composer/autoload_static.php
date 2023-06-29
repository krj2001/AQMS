<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb380fc21a8182c6528e83d03589d713c
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb380fc21a8182c6528e83d03589d713c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb380fc21a8182c6528e83d03589d713c::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb380fc21a8182c6528e83d03589d713c::$classMap;

        }, null, ClassLoader::class);
    }
}