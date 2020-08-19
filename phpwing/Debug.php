<?php
/**
 * User: wx
 * Date: 2018/12/8
 * Time: 10:28
 */

namespace wing;


class Debug
{

    public static function check()
    {
        return defined('DEBUG') ??  false;
    }

    /**
     * 记录调试信息
     * @param mixed  $msg  调试信息
     * @param string $type 信息类型
     * @return void
     */
    private static function record($msg, $type = 'log')
    {

        $traces = debug_backtrace();

        $file = '';
        $line = '';
        if(!empty($traces[1]['file'])){
            $file = $traces[1]['file'];
            $line = $traces[1]['line'];
        }

        \wing\lib\Log::save($type,$msg, $file, $line);
    }

    public static function log($msg) {
        self::record($msg);
    }

    public static function error($msg) {
        self::record($msg, __FUNCTION__);
    }

    public static function info($msg) {
        self::record($msg, __FUNCTION__);
    }

    public static function sql($msg) {
        self::record($msg, __FUNCTION__);
    }

    public static function notice($msg) {
        self::record($msg, __FUNCTION__);
    }

    public static function alert($msg) {
        self::record($msg, __FUNCTION__);
    }

    public static function debug($msg) {
        self::record($msg, __FUNCTION__);
    }
}