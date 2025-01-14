<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    'jquery' => [
        'version' => '3.7.1',
    ],
    'bootstrap' => [
        'version' => '5.3.3',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.3',
        'type' => 'css',
    ],
    '@selectize/selectize' => [
        'version' => '0.15.2',
    ],
    'bootstrap-icons/font/bootstrap-icons.min.css' => [
        'version' => '1.11.3',
        'type' => 'css',
    ],
    '@fontsource/lato' => [
        'version' => '5.1.0',
    ],
    '@fontsource/lato/index.min.css' => [
        'version' => '5.1.0',
        'type' => 'css',
    ],
    '@selectize/selectize/dist/css/selectize.bootstrap5.css' => [
        'version' => '0.15.2',
        'type' => 'css',
    ],
    '@fontsource/lato/700.css' => [
        'version' => '5.1.0',
        'type' => 'css',
    ],
    '@fontsource/lato/900.css' => [
        'version' => '5.1.0',
        'type' => 'css',
    ],
];
