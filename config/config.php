<?php
/**
 *
 * FILE_NAME: config.php
 * User: OneXian
 * Date: 2020/8/10
 */

return [
    'cookie' => [
        // cookie 保存时间
        'expire'   => 3600,
        // cookie 保存路径
        'path'     => '/',
        // cookie 有效域名
        'domain'   => '',
        //  cookie 启用安全传输
        'secure'   => false,
        // httponly设置
        'httponly' => false,
        // samesite 设置，支持 'strict' 'lax'
        'samesite' => '',
    ],

    // 是否使用路由
    'open_route' => true,

];