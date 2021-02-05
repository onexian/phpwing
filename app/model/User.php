<?php
declare (strict_types = 1);
namespace app\model;
use wing\lib\Mysql;

/**
 *
 * FILE_NAME: User.php
 * User: OneXian
 * Date: 2020/8/10
 */
class User extends Mysql
{
    public $db_tablename = 'ns_member';
    public $pk = 'uid';


    public function addCookie($uid,$username,$salt,$email,$pass,$type,$expire="1",$userdid='')
    {

        return 1111;
        $expire_date=intval($expire) * 86400;
        lib('cookie')::set("uid",$uid,$expire_date);
        lib('cookie')::set("shell",md5($username.$pass.$salt), $expire_date);
        lib('cookie')::set("usertype",$type,$expire_date);
        lib('cookie')::set("userdid",$userdid,$expire_date);

        $chat = substr(uniqid(strval(rand())), -6);
        lib('cookie')::set("chat",$chat,$expire_date);
    }

}