<?php

/**
 * 核心加载
 * User: wx
 * Date: 2018/12/6
 * Time: 11:03
 */
namespace lib;

header("Content-Type:text/html; charset=utf-8");

/**
 * 项目所在的目录
 */
define('ROOT', dirname(dirname(__FILE__)));

define('IS_CLI', PHP_SAPI == 'cli' ? true : false);


class Loader
{

    /**
     * @var string 当前模块路径
     */
    public static $mo = 'admin';

    /**
     * @var string 控制器
     */
    public static $ct = 'index';

    /**
     * @var string 方法
     */
    public static $ac = 'index';

    // 初始目录指向
    protected static $vendorMap = [
        'app' => APP_ROOT,
        'phpwing' => ROOT,
        'lib' => ROOT . DS . 'lib',
    ];

    /**
     * 初始化入口
     */
    public static function init()
    {

        if (!empty($_SERVER['argv'][1]) && IS_CLI) {
            parse_str($_SERVER['argv'][1], $_GET);
        }

        // 引入通用配置
        self::requireFile(ROOT . '/config/global.php');

        // 加载composer类加载器
        $composerAutoload = ROOT . '/lib/library/composer/vendor/autoload.php';
        if (is_file($composerAutoload)) {
            self::requireFile($composerAutoload);
        }

        // 自动加载类
        spl_autoload_register([new Loader(), 'autoload']);

        // 错误调用
        \lib\library\Error::init();
        // 加载配置文件
        \lib\library\Config::init(ROOT . DS); // 主框架
        \lib\library\Config::init(APP_ROOT); // APP

        self::route();
    }

    // 路由验证
    private static function route()
    {

        $request = new Request();
        $getData = $request->getInput('get.');

        $mo = empty($getData[MO]) ? self::$mo : $getData[MO];
        $ct = empty($getData[CT]) ? self::$ct : $getData[CT];
        $ac = empty($getData[AC]) ? self::$ac : $getData[AC];
        if (!IS_CLI) {

        }

        if (!preg_match("/^[a-z]+[a-z_0-9]+$/i", $mo)) {
            // 非法的mo
            Debug::alert("非法的mo：{$mo}");
            $mo = 'admin';
        }
        if (!preg_match("/^[a-z]+[a-z_0-9]+$/i", $ct)) {
            // 非法的ct
            Debug::alert("非法的ct：{$ct}");
            $ct = 'index';
        }
        if (!preg_match("/^[a-z]+[a-z_0-9]+$/i", $ac)) {
            // 非法的ac
            Debug::alert("非法的ac：{$ac}");
            $ac = 'index';
        }

        self::$mo = $mo;
        self::$ct = $ct;
        self::$ac = $ac;

        $controller = 'C_' . $ct;
        $controllerPath = self::parseClass($mo, CLT_DIR, $controller);
        $action = $ac;
        $controllerObj = new $controllerPath;

        //判断控制器是否存在
        if (!is_object($controllerObj)) {
            $msg = "控制器{$controller}无法自动加载";
            Debug::error($msg);

            if (Debug::check()) throw new \Exception($msg);
            exit;
        }

        //判断控制器的方法是否存在
        if (!method_exists($controllerObj, $action)) {
            $msg = "控制器{$controller}不存在方法：{$action}";
            Debug::error($msg);

            if (Debug::check()) throw new \Exception($msg);
            exit;
        }

        $controllerObj->$action();
    }

    /**
     * 自动加载器
     */
    public static function autoload($class)
    {

        $file = self::findFile($class);

        if (file_exists($file)) {
            self::includeFile($file);
            return true;
        }

        $msg = "自动加载{$class}时，文件{$file}不存在";
        Debug::error($msg);
        if (Debug::check()) throw new \Exception($msg);
        return false;
    }

    /**
     * 解析文件路径
     */
    private static function findFile($class)
    {

        $vendor = substr($class, 0, strpos($class, '\\')); // 顶级命名空间
        $vendorDir = self::$vendorMap[$vendor]; // 文件基目录
        $filePath = substr($class, strlen($vendor)) . EXT; // 文件相对路径
        return strtr($vendorDir . $filePath, '\\', DS); // 文件标准路径
    }

    /**
     * 引入文件
     */
    private static function includeFile($file)
    {
        if (is_file($file)) {
            include $file;
        }
    }

    private static function requireFile($file)
    {
        if (is_file($file)) {
            require $file;
        }
    }

    /**
     * 解析应用类的类名
     * @param string $module 模块名
     * @param string $layer 层名 controller model ...
     * @param string $name 类名
     * @return string
     */
    public static function parseClass($module, $layer, $name)
    {
        $name = str_replace(['/', '.'], '\\', $name);
        $array = explode('\\', $name);
        $class = self::parseName(array_pop($array), 1);
        $path = $array ? implode('\\', $array) . '\\' : '';
        return '\\' . APP_NAME . '\\' . ($module ? $module . '\\' : '') . $layer . '\\' . $path . $class;
    }

    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @param string $name 字符串
     * @param integer $type 转换类型
     * @param bool $ucfirst 首字母是否大写（驼峰规则）
     * @return string
     */
    public static function parseName($name, $type = 0, $ucfirst = true)
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[0]);
            }, $name);
            return $ucfirst ? ucfirst($name) : lcfirst($name);
        } else {
            return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
    }
}

// 初始化调用
Loader::init();