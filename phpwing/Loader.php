<?php

/**
 * 核心加载
 *
 * User: wx
 * Date: 2018/12/6
 * Time: 11:03
 */
namespace wing;

require ROOT . '/phpwing/global.php';
require_once ROOT . '/vendor/autoload.php'; // 加载composer类加载器
require ROOT . '/phpwing/helper.php'; // 助手函数库

class Loader
{
    // 初始目录指向
    protected static $vendorMap = [
        'wing' => ROOT . DS . 'phpwing',
    ];

    public static function init()
    {
        if (!empty($_SERVER['argv'][1]) && IS_CLI) {
            parse_str($_SERVER['argv'][1], $_GET);
        }

        // 自动加载类
        spl_autoload_register([new Loader(), 'autoload']);

        // 错误调用
        \wing\lib\Error::init();
        // 加载配置文件
        \wing\lib\Config::init();

        self::route();
    }

    /**
     * 自动加载器
     */
    private static function autoload($class)
    {
        $file = self::findFile($class);
        if (file_exists($file)) {
            include $file;
            return true;
        }

        if (is_debug()) {
            $msg = "自动加载{$class}时，文件{$file}不存在";
            throw new \Exception($msg);
        }
        return false;
    }

    /**
     * 解析文件路径
     */
    private static function findFile($class)
    {

        $vendor = substr($class, 0, strpos($class, '\\')); // 顶级命名空间
        $vendorDir = self::$vendorMap[$vendor]??ROOT . DS . APP_NAME; // 文件基目录
        $filePath = substr($class, strlen($vendor)) . EXT; // 文件相对路径
        return strtr($vendorDir . $filePath, '\\', DS); // 文件标准路径
    }

    // 路由验证
    private static function route()
    {
        if(config('open_route')){
            // 加载app路由目录
            $pathFiles = lib('file')->getList(ROUTE_DIR . APP_NAME, true, true);
            foreach($pathFiles as $item){
                require_once $item;
            }

        }

        $ct = request()->controller();
        $ac = request()->action();

        if (!preg_match("/^[a-z]+[a-z_.0-9]+$/i", $ct)) {
            // 非法的ct
            $ct = 'index';
        }
        if (!preg_match("/^[a-z]+[a-z_0-9]+$/i", $ac)) {
            // 非法的ac
            $ac = 'index';
        }

        $controllerPath = self::parseClass(CLT_DIR, $ct);
        $controllerObj = class_exists($controllerPath) ? new $controllerPath : null;

        //判断控制器是否存在
        if (!is_object($controllerObj)) {
            if (is_debug()){
                $msg = "控制器{$ct}无法自动加载";
                throw new \Exception($msg);
            }
            wing('response')->code(404)->send();
            exit;
        }

        //判断控制器的方法是否存在
        if (!method_exists($controllerObj, $ac)) {
            if (is_debug()){
                $msg = "{$ct}控制器,{$ac}方法不存在无法自动加载";
                throw new \Exception($msg);
            }
            wing('response')->code(404)->send();
            exit;
        }

        $controllerObj->$ac();
    }

    /**
     * 解析应用类的类名
     *
     * @param string $layer 层名 controller model ...
     * @param string $name 类名
     * @return string
     */
    private static function parseClass($layer, $name)
    {

        $name = str_replace(['/', '.'], '\\', $name);
        $array = explode('\\', $name);
        $class = self::parseName(array_pop($array), 1);
        $path = $array ? implode('\\', $array) . '\\' : '';
        return '\\' . APP_NAME . '\\' . $layer . '\\' . $path . $class;
    }

    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     *
     * @param string $name 字符串
     * @param integer $type 转换类型
     * @param bool $ucfirst 首字母是否大写（驼峰规则）
     * @return string
     */
    private static function parseName($name, $type = 0, $ucfirst = true)
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

// 初始化
Loader::init();