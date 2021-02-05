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
    'open_route' => false, // true,false

    // 模板参数配置
    'view' => [
        // 是否使用缓存机制进行工作
        'cache_enable' => false,
        // 超过缓存时间则自动更新缓存。0 永远不过期；> 0 判断过期；< 0 始终过期；
        'cache_expire' => 30, // 秒
        // 模板后缀
        'ext' => 'htm'
    ],

];