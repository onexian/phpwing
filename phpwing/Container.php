<?php
declare (strict_types = 1);
namespace wing;
/**
 * 容器库
 * FILE_NAME: Container.php
 * User: OneXian
 * Date: 2020/8/11
 */
class Container
{
    // 存放容器的数据
    public $instances = [];
    // 单例模式
    protected static $instance;

    private function __construct(){}

    public static function getInstance(){
        if(is_null(static::$instance)){
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function make($class)
    {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        try {
            $reflect = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new \ClassNotFoundException('class not exists: ' . $class, $class, $e);
        }

        $object = $reflect->newInstanceArgs([]);
        $this->set($class,$object);

        return $object;
    }

    public function set($class, $value){
        $this->instances[$class] = $value;
    }

    public function get($class){
        $new = $this->instances[$class];
        return $new;
    }

}
