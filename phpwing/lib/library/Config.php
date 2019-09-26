<?php
/**
 * User: wx
 * Date: 2019/1/3
 * Time: 11:32
 */
namespace lib\library;


class Config
{

    // 配置参数
    private static $config = [];
    // 参数作用域
    private static $range = '_sys_';

    public static function init($root = ROOT, $mo = '')
    {

        // 定位模块目录
        $mo = $mo ? $mo . DS : '';

        // 加载初始化文件
         $configPath = $root . CONFIG_DIR . DS . $mo;

        // 加载模块配置
        $config = self::load($configPath . 'config' . EXT);

        // 读取数据库配置文件
        $filename = $configPath . 'database' . EXT;
        self::load($filename, 'database');

        // 读取扩展配置文件
        if (is_dir($configPath . 'extra')) {
            $dir   = $configPath . 'extra';
            $files = scandir($dir);
            foreach ($files as $file) {
                if (strpos($file, EXT)) {
                    $filename = $dir . DS . $file;
                    self::load($filename, pathinfo($file, PATHINFO_FILENAME));
                }
            }
        }

        // 加载公共文件
        if (is_file($configPath . 'common' . EXT)) {
            include $configPath . 'common' . EXT;
        }

        return self::get();

    }

    /**
     * 解析配置文件或内容
     * @param string $config 配置文件路径或内容
     * @param string $type 配置解析类型
     * @param string $name 配置名（如设置即表示二级配置）
     * @param string $range 作用域
     * @return mixed
     */
    public static function parse($config, $type = '', $name = '', $range = '')
    {
        $range = $range ?: self::$range;
        if (empty($type)) {
            $type = pathinfo($config, PATHINFO_EXTENSION);
        }

        $result = self::getTypeParse($type, $config);
        return self::set($result, $name, $range);
    }

    // 配置类型解析
    public static function getTypeParse($type, $config)
    {
        switch ($type) {
            case 'ini':
                if (is_file($config)) {
                    return parse_ini_file($config, true);
                } else {
                    return parse_ini_string($config, true);
                }
                break;
            case 'json':
                if (is_file($config)) {
                    $config = file_get_contents($config);
                }
                $result = json_decode($config, true);

                break;
            case 'xml':
                if (is_file($config)) {
                    $content = simplexml_load_file($config);
                } else {
                    $content = simplexml_load_string($config);
                }
                $result = (array)$content;
                foreach ($result as $key => $val) {
                    if (is_object($val)) {
                        $result[$key] = (array)$val;
                    }
                }
                break;
            default:

                return false;
        }

        return $result;

    }

    /**
     * 加载配置文件（PHP格式）
     * @param string $file 配置文件名
     * @param string $name 配置名（如设置即表示二级配置）
     * @param string $range 作用域
     * @return mixed
     */
    public static function load($file, $name = '', $range = '')
    {
        $range = $range ?: self::$range;
        if (!isset(self::$config[$range])) {
            self::$config[$range] = [];
        }
        if (is_file($file)) {
            $name = strtolower($name);
            $type = pathinfo($file, PATHINFO_EXTENSION);
            if ('php' == $type) {
                return self::set(include $file, $name, $range);
            } elseif ('yaml' == $type && function_exists('yaml_parse_file')) {
                return self::set(yaml_parse_file($file), $name, $range);
            } else {
                return self::parse($file, $type, $name, $range);
            }
        } else {
            return self::$config[$range];
        }
    }

    /**
     * 获取配置参数 为空则获取所有配置
     * @param string $name 配置参数名（支持二级配置 .号分割）
     * @param string $range 作用域
     * @return mixed
     */
    public static function get($name = null, $range = '')
    {
        $range = $range ?: self::$range;

        // 无参数时获取所有
        if (empty($name) && isset(self::$config[$range])) {
            return self::$config[$range];
        }
        if (!strpos($name, '.')) {
            $name = strtolower($name);
            return isset(self::$config[$range][$name]) ? self::$config[$range][$name] : null;
        } else {
            // 二维数组设置和获取支持
            $name = explode('.', $name, 2);
            $name[0] = strtolower($name[0]);
            return isset(self::$config[$range][$name[0]][$name[1]]) ? self::$config[$range][$name[0]][$name[1]] : null;
        }
    }

    /**
     * 设置配置参数 name为数组则为批量设置
     * @param string|array $name 配置参数名（支持二级配置 .号分割）
     * @param mixed $value 配置值
     * @param string $range 作用域
     * @return mixed
     */
    public static function set($name, $value = null, $range = '')
    {
        $range = $range ?: self::$range;
        if (!isset(self::$config[$range])) {
            self::$config[$range] = [];
        }
        if (is_string($name)) {
            if (!strpos($name, '.')) {
                self::$config[$range][strtolower($name)] = $value;
            } else {
                // 二维数组设置和获取支持
                $name = explode('.', $name, 2);
                self::$config[$range][strtolower($name[0])][$name[1]] = $value;
            }
            return;
        } elseif (is_array($name)) {
            // 批量设置
            if (!empty($value)) {
                self::$config[$range][$value] = isset(self::$config[$range][$value]) ?
                    array_merge(self::$config[$range][$value], $name) :
                    self::$config[$range][$value] = $name;
                return self::$config[$range][$value];
            } else {
                return self::$config[$range] = array_merge(self::$config[$range], array_change_key_case($name));
            }
        } else {
            // 为空直接返回 已有配置
            return self::$config[$range];
        }
    }

    /**
     * 重置配置参数
     * @param $range
     */
    public static function reset($range = '')
    {
        $range = $range ?: self::$range;
        if (true === $range) {
            self::$config = [];
        } else {
            self::$config[$range] = [];
        }
    }
}