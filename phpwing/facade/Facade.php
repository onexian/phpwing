<?php
declare (strict_types = 1);
namespace wing\facade;
use wing\Container;

/**
 * 门面容器类
 * FILE_NAME: Facade.php
 * User: OneXian
 * Date: 2020/8/11
 */
class Facade
{
    private static function createFacade()
    {
        $class = static::class;
        $facadeClass = static::getFacadeClass();
        if ($facadeClass) {
            $class = $facadeClass;
        }

        return Container::getInstance()->make($class);
    }

    protected static function getFacadeClass(){}

    public static function __callStatic($method, $params)
    {
        return call_user_func_array([static::createFacade(), $method], $params);
    }

    public function __call($method, $params)
    {
        return call_user_func_array([static::createFacade(), $method], $params);
    }
}