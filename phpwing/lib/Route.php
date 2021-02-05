<?php
declare (strict_types = 1);
namespace wing\lib;


/**
 *
 * FILE_NAME: Route.php
 * User: OneXian
 * Date: 2020/8/15
 */
class Route
{

    /**
     * 路由规则列表
     * @var array
     */
    protected $rules = [];

    /**
     * 绑定路由规则
     *
     * @access public
     * @param  string $rule   路由规则
     * @param  mixed  $route  路由地址
     * @param  string $method 请求类型
     */
    private function addRule(string $rule, $route = null, string $method = '*')
    {
        $this->rules[$this->getRulesKey($method)][$this->getRuleKeyPath($rule)] = $route;
    }

    /**
     * 添加get 路由
     *
     * @param string $rule
     * @param null $route
     * @return $this
     */
    public function get(string $rule, $route = null)
    {
        $this->addRule($rule,$route, 'get');
        return $this;
    }

    /**
     * 添加post 路由
     *
     * @param string $rule
     * @param null $route
     * @return $this
     */
    public function post(string $rule, $route = null)
    {
        $this->addRule($rule,$route, 'post');
        return $this;
    }

    /**
     * 获取绑定的路由标识
     *
     * @param        $rule
     * @param string $method
     * @return array
     */
    public function getRule($rule, $method= '*')
    {

        $path = $this->rules[$this->getRulesKey($method)][$this->getRuleKeyPath($rule)] ?? '';

        if(empty($path)){ // 不匹配直接 抛出 404

            if (is_debug()) throw new \Exception("路由不匹配");

            wing('response')->code(404)->send();
        }
        return explode(DS, $path, 2);
    }

    /**
     * 获取设置或搜索路由数组的 key
     *
     * @param $method
     * @return string
     */
    private function getRulesKey($method)
    {
        return APP_NAME . strtolower("_{$method}");
    }

    /**
     * 获取设置或搜索的ct与ac 组成的路径
     *
     * @param $rule
     * @return string
     */
    private function getRuleKeyPath($rule)
    {
        return DS != $rule ? trim($rule, DS) : '';

    }
}