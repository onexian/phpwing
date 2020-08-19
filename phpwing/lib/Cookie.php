<?php
declare (strict_types = 1);
namespace wing\lib;
/**
 *
 * FILE_NAME: Cookie.php
 * User: OneXian
 * Date: 2020/8/11
 */
class Cookie
{

    /**
     * 配置参数
     * @var array
     */
    protected $config = [
        // cookie 保存时间
        'expire'   => 0,
        // cookie 保存路径
        'path'     => '/',
        // cookie 有效域名
        'domain'   => '',
        //  cookie 启用安全传输
        'secure'   => false,
        // httponly设置
        'httponly' => false,
        // samesite 设置，支持 'strict' 'lax'
        'samesite' => '',
    ];

    /**
     * Cookie 保存
     *
     * @access public
     * @param  string $name  cookie名称
     * @param  string $value cookie值
     * @param  null  $option 可选参数
     * @return void
     */
    public function set(string $name, string $value, $option = null): void
    {
        // 参数设置(会覆盖黙认设置)
        if (!is_null($option)) {
            if (is_numeric($option) || $option instanceof \DateTimeInterface) {
                $option = ['expire' => $option];
            }

            $config = array_merge($this->config, array_change_key_case($option));
        } else {
            $config = array_merge($this->config, Config::get('cookie')?array_change_key_case(Config::get('cookie')):[]);
        }

        if ($config['expire'] instanceof \DateTimeInterface) {
            $expire = $config['expire']->getTimestamp();
        } else {
            $expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
        }

        $this->saveCookie(
            $name,
            $value,
            $expire,
            $config['path'],
            $config['domain'],
            $config['secure'] ? true : false,
            $config['httponly'] ? true : false,
            $config['samesite']
        );
    }

    /**
     * 保存Cookie
     * @access public
     * @param  string $name cookie名称
     * @param  string $value cookie值
     * @param  int    $expire cookie过期时间
     * @param  string $path 有效的服务器路径
     * @param  string $domain 有效域名/子域名
     * @param  bool   $secure 是否仅仅通过HTTPS
     * @param  bool   $httponly 仅可通过HTTP访问
     * @param  string $samesite 防止CSRF攻击和用户追踪
     * @return void
     */
    protected function saveCookie(string $name, string $value, int $expire, string $path, string $domain, bool $secure, bool $httponly, string $samesite): void
    {
        if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
            setcookie($name, $value, [
                'expires'  => $expire,
                'path'     => $path,
                'domain'   => $domain,
                'secure'   => $secure,
                'httponly' => $httponly,
                'samesite' => $samesite,
            ]);
        } else {
            setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        }
    }
}