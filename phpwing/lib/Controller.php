<?php

/**
 * 控制器的基类
 * User: wx
 * Date: 2018/12/6
 * Time: 10:59
 */
namespace lib;

class Controller
{

    /**
     * @var string 当前调试
     */
    public $mo; // 模板
    public $ct; // 控制器
    public $ac; // 方法

    // 模板引擎对象
    private static $smartyObj;

    public function __construct()
    {
        $this->mo = Loader::$mo;
        $this->ct = Loader::$ct;
        $this->ac = Loader::$ac;
        $this->smartyInit();
    }

    // 模板引擎配置
    private function smartyInit()
    {
        if (empty(self::$smartyObj)) {

            //smarty实例化
            $smarty = new \Smarty;

            $tplDir = APP_ROOT . $this->mo . DS . VIEW_DIR;
            $tplRuntimeDir = RUNTIME_DIR . CACHE_DIR . DS . APP_NAME;
            $smarty->setTemplateDir($tplDir); // 模板目录
            $smarty->setCompileDir($tplRuntimeDir); // 缓存目录
            $smarty->setCacheDir($tplRuntimeDir);

            $smarty->left_delimiter = "{";
            $smarty->right_delimiter = "}";
            $smarty->error_reporting = 0;
            $smarty->compile_check = true;
            $smarty->escape_html = true;

            if (Debug::check()) {
                $smarty->caching = false;
                $smarty->cache_lifetime = 0;
            } else {
                $smarty->caching = true;
                $smarty->cache_lifetime = 120;
            }

            self::$smartyObj = $smarty;
        }

        return self::$smartyObj;
    }

    public function assign($tpl_var, $value = null, $nocache = false)
    {
        return self::$smartyObj->assign($tpl_var, $value, $nocache);
    }

    public function show($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        if (empty($template)) {
            $template = "{$this->ct}/{$this->ac}.tpl";
        }

        return self::$smartyObj->display($template, $cache_id, $compile_id, $parent);
    }
}