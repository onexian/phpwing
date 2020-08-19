<?php
/**
 * User: wx
 * Date: 2018/12/6
 * Time: 16:14
 */
header("Content-Type:text/html; charset=utf-8");

if (!defined('CT')) {
    /**
     * 定义控制器传参的name，默认是ct，如http://www.xxx.com/?ct=user
     */
    define('CT', 'ct');
}

if (!defined('AC')) {
    /**
     * 定义控制器方法传参的name，默认是ac，如http://www.xxx.com/?ct=user&ac=login
     */
    define('AC', 'ac');
}

if (!defined('URL_INFO')) {
    /**
     * 定义路由简写方式，如http://www.xxx.com/?s=/user/login
     */
    define('URL_INFO', 's');
}

define('DS', DIRECTORY_SEPARATOR);


/**
 * 运行时产生的配置文件、缓存等文件
 */
define('RUNTIME_DIR', ROOT . DS . 'runtime' . DS);

/**
 * 路由目录
 */
define('ROUTE_DIR', ROOT . DS . 'route' . DS);

/**
 * 控制器的目录
 */
define('CLT_DIR', 'controller');

/**
 * 模型的目录
 */
define('MOD_DIR', 'model');

/**
 * 模板目录
 */
define('VIEW_DIR', 'view');

/**
 * 缓存目录
 */
define('CACHE_DIR', 'cache');

/**
 * 配置文件目录
 */
define('CONFIG_DIR', 'config');

/**
 * aux权限后台 sessionkey
 */
define('AUC', '_phpwing');

//定义时区
if (defined('TIME_ZONE')) {
    date_default_timezone_set(TIME_ZONE);
} else {
    date_default_timezone_set('Asia/Shanghai');
}

define('EXT', '.php');

define('IS_CLI', PHP_SAPI == 'cli' ? true : false);


