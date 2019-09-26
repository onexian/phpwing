<?php
/**
 * User: wx
 * Date: 2018/12/6
 * Time: 16:14
 */

if (!defined('MO')) {
    /**
     * 定义控制器传参的模块，默认是mo，如http://www.xxx.com/?mo=admin
     */
    define('MO', 'mo');
}

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

define('DS', DIRECTORY_SEPARATOR);


/**
 * 运行时产生的配置文件、缓存等文件
 */
define('RUNTIME_DIR', dirname(ROOT) . DS . 'runtime' . DS);

/**
 * lib目录
 */
define('LIB', ROOT . DS . 'lib' . DS);

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
