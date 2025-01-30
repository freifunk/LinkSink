<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/.env')) {
    (new Dotenv())->usePutenv()->loadEnv(dirname(__DIR__).'/.env');
}

$env = $_SERVER['APP_ENV'] ?? 'dev';
if ($env === 'prod' && file_exists(dirname(__DIR__).'/.env.prod')) {
    (new Dotenv())->usePutenv()->overload(dirname(__DIR__).'/.env.prod');
}
if ($env === 'test' && file_exists(dirname(__DIR__).'/.env.test')) {
    (new Dotenv())->usePutenv()->overload(dirname(__DIR__).'/.env.test');
}