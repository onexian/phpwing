<?php
declare (strict_types = 1);
namespace wing;
/**
 *
 * FILE_NAME: Response.php
 * User: OneXian
 * Date: 2020/8/12
 */
class Response
{
    /**
     * 默认输出设置参数
     * @var array
     */
    protected static $assocOptions = [
        'json_encode_param' => JSON_UNESCAPED_UNICODE
    ];

    /**
     * 输出头部 Content-Type
     * @var array
     */
    protected static $contenTypeText = [
        'json' => 'application/json',
    ];
    /**
     * 字符集
     * @var string
     */
    protected $charset = 'utf-8';
    /**
     * header参数
     * @var array
     */
    protected $header = [];
    /**
     * 其它输出设置参数
     * @var array
     */
    protected $options = [];
    /**
     * 状态码
     * @var integer
     */
    protected $code = 200;

    /**
     * 设置返回code
     * @param int $code
     * @return $this
     */
    public function code(int $code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * 设置返回页面的编码
     * @param string $charset
     * @return $this
     */
    public function charset(string $charset)
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * 发送数据到客户端
     * @param string|array $data
     * @param string       $type
     * @param array        $header
     * @param array        $options
     */
    public function send($data = '', string $type = 'html', $header = [], $options = []): void
    {
        $data = $this->getContent($data, $type, $header, $options);
        if (!headers_sent() && !empty($this->header)) {
            // 发送状态码
            http_response_code($this->code);
            // 发送头部信息
            foreach ($this->header as $name => $val) {
                header($name . (!is_null($val) ? ':' . $val : ''));
            }
        }
        // 显示数据
        echo $data;
        if (function_exists('fastcgi_finish_request')) {
            // 提高页面响应
            fastcgi_finish_request();
        }
    }

    /**
     * 获取输出数据、设置头部和其它参数
     * @param        $data
     * @param string $type
     * @param        $header
     * @param        $options
     * @return string
     */
    private function getContent($data, string $type, $header, $options): string
    {
        $contentType = self::$contenTypeText[$type]??'text/html';
        $resHeader = [
            'Content-Type' => $contentType . ';charset=' . $this->charset,
        ];
        $this->header = array_merge($resHeader, $header ? array_change_key_case($header) : []);
        $this->options = array_merge(self::$assocOptions, $options);
        $output = "{$type}Output";
        if (method_exists($this, $output)) {
            $content = $this->$output($data);
        } else {
            $content = $data;
        }
        if (null !== $content && !is_string($content) && !is_numeric($content) && !is_callable([
                $content,
                '__toString',
            ])
        ) {
            throw new \InvalidArgumentException(sprintf('variable type error： %s', gettype($content)));
        }
        return (string)$content;
    }

    /**
     * json处理数据
     * @access protected
     * @param  mixed $data 要处理的数据
     * @return string
     * @throws \Exception
     */
    protected function jsonOutput($data): string
    {
        try {
            // 返回JSON数据格式到客户端 包含状态信息
            $data = json_encode($data, $this->options['json_encode_param']);
            if (false === $data) {
                throw new \InvalidArgumentException(json_last_error_msg());
            }
            return $data;
        } catch (\Exception $e) {
            if ($e->getPrevious()) {
                throw $e->getPrevious();
            }
            throw $e;
        }
    }

}