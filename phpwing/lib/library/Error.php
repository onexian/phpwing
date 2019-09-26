<?php
/**
 * 错误异常类
 * User: wx
 * Date: 2018/12/7
 * Time: 18:32
 */

namespace lib\library;


class Error
{

    public static function init()
    {
        set_error_handler([__CLASS__, 'appError']);
        set_exception_handler([__CLASS__, 'appException']);
        register_shutdown_function([__CLASS__, 'appShutdown']);
    }

    public static function appError($errno, $errstr, $errfile = '', $errline = 0)
    {

        $type = 'error';
        if($errno == E_WARNING ){
            $type = 'warn';
        }
        if($errno == E_NOTICE){
            $type = 'log';
        }

        Log::save($type , $errstr, $errfile, $errline);
    }

    public static function appException($e)
    {
        echo '<pre>';
        $errArr = [
            '错误码' => $e->getCode(),
            '路径' => $e->getMessage(),
            '文件' => $e->getFile(),
            '行号' => $e->getLine(),
        ];
        var_dump($errArr);
        echo '</pre><hr>';

    }

    public static function appShutdown()
    {
        echo "<hr>";
        print_r("ALL END");
    }
}