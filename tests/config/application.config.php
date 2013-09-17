<?php
return array(
    'modules'                 => array(
        'DoctrineModule',
        'DoctrineORMModule',
        'Content',
        'Program',
        'General',
        'Contact',
        'Project',
        'Affiliation',
        'Organisation',
        'Press',
        'Admin',
        'News',
    ),
    'module_listener_options' => array(
        'config_glob_paths' => array(
            __DIR__ . '/autoload/{,*.}{global,testing,local}.php',
        ),
        'module_paths'      => array(
            __DIR__ . '/../../src',
            __DIR__ . '/../../../vendor',
            __DIR__ . '/../../../../../module',
        ),
    ),
    'service_manager'         => array(
        'use_defaults' => true,
        'factories'    => array(),
    ),
);
