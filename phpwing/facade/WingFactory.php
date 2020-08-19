<?php
declare (strict_types = 1);
namespace wing\facade;
/**
 *
 * FILE_NAME: WingFactory.php
 * User: OneXian
 * Date: 2020/8/13
 */
class WingFactory extends Facade
{

    private static $class;
    public function __construct($class)
    {
        self::$class = $class;
    }

    protected static function getFacadeClass()
    {
        return '\wing\\' . self::$class;
    }
}