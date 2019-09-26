<?php
/**
 * User: wx
 * Date: 2019/1/3
 * Time: 14:44
 */

function p(...$data)
{
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

function get_ret_obj()
{
    return new \lib\Request();
}

function get_res_obj()
{
    return new \lib\Response();
}
