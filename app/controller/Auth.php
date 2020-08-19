<?php
declare (strict_types = 1);
namespace app\controller;

use app\model\User;

/**
 *
 * FILE_NAME: Auth.php
 * User: OneXian
 * Date: 2020/8/10
 */
class Auth
{

    public function login()
    {
        $username = input('post.username');
        $password = input('post.password');

        if (!$username || !$password)
            return json_no('账号不存在');

        $mod = new User();
        $user = $mod->getOne(['username' => $username], ['username', 'usertype', 'password', 'uid', 'salt', 'status', 'did', 'login_date']);
        if ($user) {
            $res = md5(md5($password) . $user['salt']) == $user['password'] ? true : false;
            if (!$res)
                return json_no('账号或密码错误');
        } else {
            return json_no('账号或密码错误');
        }
        if (!$user['status'])
            return json_no('已被禁止，请联系管理员');

        $ip = request()::getIp();
        $params = [
            "login_ip" => $ip,
            "login_date" => time(),
            "login_hits"=>"`login_hits`+1",
        ];
        $res = $mod->update(
            $params,
            $user['uid']
        );

        $mod->addCookie($user['uid'],$user['username'],$user['salt'],$user['email'],$user['password'],$user['usertype'],1,$user['did']);

        return json_ok(['token' => 'token'], '登录成功');
    }
}