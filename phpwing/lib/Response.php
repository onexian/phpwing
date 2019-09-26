<?php
/**
 * User: wx
 * Date: 2019/1/9
 * Time: 10:07
 */

namespace lib;


class Response
{

    public function success($msg = '', array $data = [])
    {
        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data
        ];

        return $result;
    }

    public function successJson($msg = '', array $data = [])
    {
        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data
        ];

        echo json_encode($result);
        return;
    }

    public function error($msg = '', array $data = [])
    {
        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data
        ];

        return $result;
    }

    public function errorJson($msg = '', array $data = [])
    {
        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data
        ];

        echo json_encode($result);
        return;
    }
}