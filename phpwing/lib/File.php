<?php
declare (strict_types = 1);
namespace wing\lib;
/**
 *
 * FILE_NAME: File.php
 * User: OneXian
 * Date: 2020/8/15
 */
class File
{
    /**
     * 遍历某个文件夹所有文件
     * @param string  $path    文件夹的路径
     * @param boolean $allPath 是否返回全路径
     * @param boolean $allFile 是否递归
     * @return array 文件数组
     */
    public static function getList($path, $allPath = true, $allFile = false)
    {
        $path = substr($path, -1, 1) == DS ? $path : $path . DS;
        if (!is_dir($path)) return [];
        $_handle = opendir($path);
        $files = [];
        while (false !== ($file = readdir($_handle))) {
            if (!in_array($file, ['.', '..'])) {
                if (is_dir($path . $file) && $allFile) {
                    $files = array_merge($files, self::getList($path . $file, $allPath, $allFile));
                } elseif (!is_dir($path . $file)) {
                    $files[] = $allPath ? $path . $file : $file;
                }
            }
        }
        return $files;
    }

}