<?php
declare (strict_types=1);


namespace wing;


/**
 *
 * FILE_NAME: View.php
 * User: OneXian
 * Date: 2021.02.04
 */
class View
{

    /**
     * 是否使用缓存机制进行工作
     *
     * @var boolean $cacheEnable true-启用缓存机制；false-关闭缓存机制
     */
    public $cacheEnable = true;

    /**
     * 缓存时间
     * 超过缓存时间则自动更新缓存。
     *
     * @var  integer $cacheExpire 0 永远不过期；> 0 判断过期；< 0 始终过期；
     */
    public $cacheExpire = 0;

    /**
     * 模板使用的后缀
     *
     * @var string $viewFileExt
     */
    public $viewFileExt = 'html';

    /**
     * 模板文件的存贮目录
     *
     * @var  string $templateDir
     */
    public $templateDir = '';

    /**
     * 缓存文件的存贮目录
     *
     * @var string $cacheDir
     */
    public $cacheDir = '';

    /**
     * 序列化文件的存贮目录
     *
     * @var  string $serializeDir
     */
    public $serializeDir = '';

    /**
     * 保存模板文件中使用的变量的名、值列表
     *
     * @var  array $inViewArray
     */
    private $inViewArray = array();

    /**
     * 记住系统目录是否已经检查过并且符合要求。避免以全局方式使用时重复检查。
     *
     * @var  boolean $isViewDirReady
     */
    private $isViewDirReady = false;

    /**
     * 当前操作使用的模板文件
     *
     * @var  string $viewFile
     */
    private $viewFile = '';

    public function __construct(array $config = [])
    {
        $config = array_merge(config('view'), $config);

        $this->setConfig($config);
    }

    public function setConfig($config)
    {

        if (isset($config['cache_enable'])) {

            $this->cacheEnable = $config['cache_enable'];
        }

        if (isset($config['cache_expire'])) {

            $this->cacheExpire = $config['cache_expire'];

        }

        if (isset($config['ext'])) {

            $this->viewFileExt = $config['ext'];

        }

        $this->templateDir  = ROOT . DS . APP_NAME . DS . 'view';
        $this->cacheDir     = RUNTIME_DIR . 'view' . DS . 'cache' . DS . APP_NAME . DS;
        $this->serializeDir = RUNTIME_DIR . 'view' . DS . 'serialize' . DS . APP_NAME . DS;

        return $this;
    }

    /**
     * 指派模板文件中使用的变量
     *
     * @param string|array $varName 在模板中使用的变量名, 使用数组来传递名值列表，即变量名作为关联索引，变量值为元素值。
     * @param any $varValue PHP中支持的数据类型
     */
    public function assign($varName, $varValue = null)
    {

        if (func_num_args() == 1) {

            $varList = func_get_arg(0);

            if (gettype($varList) == 'array') {

                foreach ($varList as $name => $value) {

                    $this->inViewArray[$name] = $value;

                }

            }

        } else if (gettype($varName) == 'string') {

            $this->inViewArray[$varName] = $varValue;

        }

        return;

    }

    /**
     * 调用模板文件，生成模板中的变量，输出到浏览器
     *
     * @param string $file 模板文件名
     */
    public function display($file)
    {

        if (!$this->isViewDirReady) {

            $this->checkSysDir();

        }

        $this->viewFile = $file . "." . $this->viewFileExt;

        if (!is_file($this->templateDir . DS . $this->viewFile)) {
            throw new \Exception($this->templateDir . DS . $this->viewFile . " 文件不存在");
        }

        if ($this->cacheEnable) {

            $this->cacheOutput();

        } else {

            $this->directOutput();

        }

        return;

    }

    /**
     * 检测所需目录是否存在。如果不存在，则报错并退出。
     * 所需目录为：模板文件目录、缓存文件目录、序列化文件目录
     */
    private function checkSysDir()
    {

        if ($this->templateDir == '') {

            throw new \Exception("模板文件目录未设置。");

        }

        if (!is_readable($this->templateDir)) {

            throw new \Exception("{$this->templateDir} 模板文件目录不可读。");

        }


        if ($this->cacheDir == '') {

            throw new \Exception("缓存文件目录未设置。");

        }

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        chmod($this->cacheDir, 0755);

        if (!(is_readable($this->cacheDir) && is_writable($this->cacheDir))) {

            throw new \Exception("{$this->cacheDir} 缓存文件目录不可读写。");

        }

        if ($this->serializeDir == '') {

            throw new \Exception("序列化文件目录未设置。");

        }

        if (!is_dir($this->serializeDir)) {
            mkdir($this->serializeDir, 0755, true);
        }
        chmod($this->serializeDir, 0755);

        if (!(is_readable($this->serializeDir) && is_writable($this->serializeDir))) {

            throw new \Exception("{$this->serializeDir} 序列化文件目录不可读写。");

        }


        $this->isViewDirReady = true;

        return;

    }

    /**
     * 使用缓存机制处理模板
     * 实现原理：
     * 1、取得客户端请求的链接url。缓存文件和序列化文件的文件名使用此url命名。
     * 2、将存贮当前模板变量的数组序列化为一个字符串。
     * 3、读取模板文件对应的序列化文件，得到前一个模板变量数组的序列化字符串。
     * 4、判断前后两次序列化字符串是否相等。如果相等，则直接把缓存文件的内容输出到浏览器。
     *    否则，重新生成序列化文件和缓存文件，然后把模板处理结果输出到浏览器。
     */
    private function cacheOutput()
    {

        $html = '';

        $cache_filename = @urlencode(basename(SERVER['REQUEST_URI']));

        $cur_serialize = @serialize($this->inViewArray);

        $pre_serialize = $this->readSerialize($cache_filename);


        if ($cur_serialize === $pre_serialize) {

            if (!$this->isCacheFileTimeout($cache_filename)) {

                // 读缓存文件
                $html = $this->readCacheFile($cache_filename);
            }

        }

        if (empty($html)) {
            // 缓存过期，刷新缓存

            $this->saveSerialize($cache_filename, $cur_serialize);

            extract($this->inViewArray);

            ob_start();

            @include($this->templateDir . DS . $this->viewFile);

            $html = ob_get_contents();

            ob_clean();

            $this->writeCacheFile($cache_filename, $html);

        }

        echo $html;
        return;

    }

    /**
     * 直接将模板处理结果输出到浏览器，不使用缓存机制
     */
    private function directOutput()
    {

        extract($this->inViewArray);

        ob_start();

        @include($this->templateDir . DS . $this->viewFile);

        ob_flush();

        return;

    }

    /**
     * 保存序列化字符串到文本文件中
     * @param string $file 序列化文件路径
     * @param string $content 序列化数据的字符串
     * @return   null
     */
    private function saveSerialize($file, $content)
    {

        $filename = $this->serializeDir . DS . $file;

        @file_put_contents($filename, $content);

        return;

    }

    /**
     * 读取文本文件中存贮的序列化字符串
     *
     * @param string $file 序列化文件路径
     * @return   string              序列化数据的字符串
     */
    private function readSerialize($file)
    {

        $filename = $this->serializeDir . DS . $file;

        $content = @file_get_contents($filename);

        return $content;

    }

    /**
     * 将模板处理结果写入缓存文件
     *
     * @param string $file 缓存文件路径
     * @param string $content 缓存内容
     * @return   null
     */
    private function writeCacheFile($file, $content)
    {

        $filename = $this->cacheDir . DS . $file;

        @file_put_contents($filename, $content);

        return;

    }

    /**
     * 读取缓存文件
     *
     * @param string $file 缓存文件路径
     * @return   string          缓存文件的内容
     */
    private function readCacheFile($file)
    {

        $filename = $this->cacheDir . DS . $file;

        $html = @file_get_contents($filename);

        return $html;

    }

    /**
     * 判断缓存文件是否过期
     *
     * @param string $file 缓存文件
     * @return   boolean
     */
    private function isCacheFileTimeout($file)
    {

        if ($this->cacheExpire == 0)//缓存时间为0，表示永远不过期
        {

            return false;

        }

        $cacheFile = $this->cacheDir . DS . $file;

        if (file_exists($cacheFile)) {

            $modifyTime = filemtime($cacheFile);

            $currentTime = time();

            if (($currentTime - $modifyTime) > $this->cacheExpire) {

                return true;

            }

            return false;

        }

        return true;

    }
}