<?php

use DG\BypassFinals;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// BYPASS FINAL CLASSES
BypassFinals::setWhitelist([
    '*/Doctrine/ORM/Query.php',
    '*/symfony/http-kernel/Event/ControllerEvent.php',
]);
BypassFinals::enable(false, true);

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
