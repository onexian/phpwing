<?php
/**
 * User: wx
 * Date: 2019/1/3
 * Time: 14:27
 */

return [
    'mysql' => [
        'master' => [
            'host' => '127.0.0.1',
            'port' => '3306',
            'dbname' => 'admin',
            'username' => 'root',
            'password' => 'root',
        ],
        'sso' => [
            'host' => '127.0.0.1',
            'port' => '3306',
            'dbname' => 'sso',
            'username' => 'root',
            'password' => 'root',
        ],
    ],
    'pgsql' => [
        'master' => [
            'host' => '127.0.0.1',
            'port' => '5432',
            'dbname' => 'data',
            'username' => 'data_job',
            'password' => 'dfgdfgsfgsdfs',
        ],

    ],
    'ssdb' => [
        'master' => [
            'host' => '127.0.0.1',
            'port' => '8888',
            'auth' => '',
            'timeout_ms' => 2000,
        ],

    ],
    'redis' => [
        'master' => [
            'host' => '127.0.0.1',
            'port' => '6379',
            'auth' => '',
        ],

    ],
];