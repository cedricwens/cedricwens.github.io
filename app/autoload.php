<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
//met apc installed:
/*
require __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/ClassLoader/ApcUniversalClassLoader.php';
use Symfony\Component\ClassLoader\ApcUniversalClassLoader;
$loader = new ApcUniversalClassLoader('apc.loader');
*/

$loader = require __DIR__.'/../vendor/autoload.php';


AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;

// app/autoload.php

