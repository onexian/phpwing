<?php
/**
 * 日志记录类
 * User: wx
 * Date: 2018/12/6
 * Time: 11:09
 */

namespace wing\lib;


class Log
{

    // 日志状态
    const ERROR_LOG = 'error';
    const DEBUG_LOG = 'debug';
    const INFO_LOG  = 'info';

    protected static $allMessage = [];

    public static function save($type, string $message, $errfile = null, $errline = null)
    {

        self::$allMessage[$type][] = [
            'file' => $errfile,
            'line' => $errline,
            'message' => $message,
        ];

        return true;

    }

    public static function end()
    {

        //设置目录
        $logDir = RUNTIME_DIR . 'log' . DS. date('Ym') . DS;

        // 检查日志目录是否可写
        if (!file_exists($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        chmod($logDir, 0755);

        // 是否可写
        if (!is_writable($logDir)) {
            throw new \Exception("{$logDir} 日志不可写");
        }

        clearstatcache();

        foreach (self::$allMessage as $key=>$value) {

            // 内容
            $content = date('[Y-m-d H:i:s]') . ucfirst($key) . "\r\n";

            foreach ($value as $_v) {

                $errfile = $_v['file'] ?? '';
                $errline = $_v['line'] ?? '';
                $message = $_v['message'] ?? '';

                if(empty($errfile) && !IS_CLI){
                    $errfile = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                }

                $content .= "[file: {$errfile}] [line: {$errline}]\r\n";
                $content .= "{$message}\r\n";

            }

            $day = date('d');

            // 根据类型设置日志目标位置
            switch ($key) {
                case self::DEBUG_LOG:
                    $logPath = $day . '_debug.log';
                    break;
                case self::ERROR_LOG:
                    $logPath = $day . '_error.log';
                    break;
                case self::INFO_LOG:
                    $logPath = $day . '_info.log';
                    break;
                default:
                    $logPath = $day . '_info.log';
                    break;
            }

            error_log($content, 3, $logDir . $logPath);

        }

        return true;

    }

    public static function __callStatic($method, $params)
    {
        if (!in_array($method, [self::DEBUG_LOG, self::ERROR_LOG, self::INFO_LOG])) {
            throw new \Exception("class ". __CLASS__ ." does not have a method '{$method}'");
        }

        $traces = debug_backtrace();
        $errfile = $traces[0]['file'] ?? '';
        $errline = $traces[0]['line'] ?? '';
        return self::save($method, implode(', ', $params), $errfile, $errline);
    }

    public function __call($method, $params)
    {
        if (!in_array($method, [self::DEBUG_LOG, self::ERROR_LOG, self::INFO_LOG])) {
            throw new \Exception("class ". __CLASS__ ." does not have a method '{$method}'");
        }

        $traces = debug_backtrace();
        $errfile = $traces[0]['file'] ?? '';
        $errline = $traces[0]['line'] ?? '';
        return self::save($method, implode(', ', $params), $errfile, $errline);
    }

}
