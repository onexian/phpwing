<?php
declare (strict_types = 1);
namespace app\controller;

/**
 *
 * FILE_NAME: Index.php
 * User: OneXian
 * Date: 2020/8/10
 */
class Index
{

    public function index()
    {

        return json_ok(['a'=>'b']);
    }
}