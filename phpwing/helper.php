<?php
/**
 * 助手函数
 * FILE_NAME: helper.php
 * User: OneXian
 * Date: 2020/8/10
 */

use wing\lib\{
    Config
};
use wing\facade\{
    LibFactory,
    WingFactory
};

if (!function_exists('dps')) {
    /**
     * 打印
     * @param array ...$data
     */
    function dps(...$data)
    {

        echo '<pre>';
        var_dump(...$data);
        echo '</pre>';
    }
}

if (!function_exists('config')) {
    /**
     * 获取和设置配置参数
     * @param string|array $name  参数名
     * @param mixed        $value 参数值
     * @return mixed
     */
    function config($name = '', $value = null)
    {
        if (is_array($name)) {
            return Config::set($name, $value);
        }

        return 0 === strpos($name, '?') ? Config::has(substr($name, 1)) : Config::get($name, $value);
    }
}

if (!function_exists('request')) {
    /**
     * 获取请求类对象
     * @return \wing\Request
     */
    function request()
    {
        return wing('request');
    }
}

if (!function_exists('input')) {
    /**
     * 获取输入数据 支持默认值和过滤
     * @param string $key     获取的变量名
     * @param mixed  $default 默认值
     * @param string $filter  过滤方法
     * @return mixed
     */
    function input(string $key = '', $default = null, $filter = '')
    {
        return request()::getInput($key, $default, $filter);
    }
}

if (!function_exists('json')) {
    /**
     * 获取输入数据 支持默认值和过滤
     * @param string $key     获取的变量名
     * @param mixed  $default 默认值
     * @param string $filter  过滤方法
     * @return mixed
     */
    function json($data = [], $code = 200, $header = [], $options = [])
    {
        return wing('response')::code($code)->send($data, 'json', $header, $options);
    }
}

if (!function_exists('lib')) {
    /**
     * lib 目录的操作类实例化
     * @param string $class
     * @return string|\wing\facade\LibFactory
     */
    function lib(string $class)
    {
        try{
            return new LibFactory($class);
        }catch (\Exception $e){
            exit('实例化类错误');
        }
    }
}

if (!function_exists('wing')) {
    /**
     * wing 目录的操作类实例化
     * @param string $class
     * @return string|\wing\facade\WingFactory
     */
    function wing(string $class)
    {
        try{
            return new WingFactory($class);
        }catch (\Exception $e){
            exit('实例化类错误');
        }
    }
}