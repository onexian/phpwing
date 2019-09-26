<?php
/**
 * 日志记录类
 * User: wx
 * Date: 2018/12/6
 * Time: 11:09
 */

namespace lib\library;


class Log
{

    public static function save($type, $message, $errfile = null, $errline = null)
    {
        //设置目录
        $logPath = RUNTIME_DIR . 'log' . DS . APP_NAME . DS. date('Ym') . DS;

        // 检查日志目录是否可写
        if (!file_exists($logPath)) {
            @mkdir($logPath, 0755, true);
        }
        chmod($logPath, 0755);

        if (!is_writable($logPath)) exit($logPath . ' is not writeable !');
        $thisDay = date('Y_m_d');
        $thisTime = date('[Y-m-d H:i:s]');

        // 根据类型设置日志目标位置
        switch ($type) {
            case 'debug':
                $logPath .= 'Debug_' . $thisDay . '.log';
                break;
            case 'error':
                $logPath .= 'Err_' . $thisDay . '.log';
                break;
            case 'log':
                $logPath .= 'Log_' . $thisDay . '.log';
                break;
            default:
                $logPath .= 'Log_' . $thisDay . '.log';
                break;
        }

        clearstatcache();

        // 写日志, 返回成功与否
        $content = "\r\n-----------------------------------\r\n";

        if(empty($errfile)){
            $errfile = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        $content .= "{$thisTime}" . ucfirst($type) . ": {$errfile} {$errline}\r\n";

        if(!empty($_POST)){
            $_save = array();
            foreach($_POST as $name => $value){
                if(is_string($value) && strlen($value) > 200){
                    $_save[$name] = substr($value, 0, 200) . '...[LONG TEXT]';
                }else{
                    $_save[$name] = $value;
                }
            }
            $content .= "\r\nPOST: " . var_export($_save, true);
        }
        if(!empty($_COOKIE)){
            $content .= "\r\nCOOKIE: " . var_export($_COOKIE, true);
        }

        $content .= "{$message}\r\n";

        return error_log($content, 3, $logPath);
    }

}
