<?php
declare (strict_types = 1);
namespace wing\facade;
/**
 * lib 目录工厂
 * FILE_NAME: LibFactory.php
 * User: OneXian
 * Date: 2020/8/11
 */
class LibFactory extends Facade
{
    private static $class;
    public function __construct($class)
    {
        self::$class = $class;
    }

    protected static function getFacadeClass()
    {
        return '\wing\lib\\'.self::$class;
    }
}