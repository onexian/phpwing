<?php
/**
 * User: wx
 * Date: 2018/10/17
 * Time: 20:50
 */
namespace app\admin\controller;

use app\common\controller\Common;

class C_Index extends Common
{

    public function index()
    {


        Debug::info(111);
        p(1);
        $this->assign('title', '依有个框架');
        $this->show('index.tpl');



    }
}