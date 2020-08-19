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
    public function addRule(string $rule, $route = null, string $method = '*')
    {
        $rule = DS != $rule ? trim($rule, DS) : '';
        $this->rules[APP_NAME . "_{$method}"][$rule] = $route;
    }

    public function get(string $rule, $route = null)
    {
        $this->addRule($rule,$route, 'get');
        return $this;
    }

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
        $path = $this->rules[APP_NAME . strtolower("_{$method}")][$rule] ?? '';
        if(empty($path)){ // 不匹配直接 抛出 404
            wing('response')::code(404)->send();
        }
        return explode(DS, $path, 2);
    }
}