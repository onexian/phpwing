<?php
/**
 *
 * FILE_NAME: common.php
 * User: OneXian
 * Date: 2020/8/10
 */
function json_ok($data = [], $msg = '')
{
    $result = [
        'code' => 200,
        'msg'  => $msg??'ok',
        'data' => $data
    ];
    return json($result);
}

function json_no($msg = '', $data = [])
{
    $result = [
        'code' => 400,
        'msg'  => $msg??'error',
    ];
    if($data)$result['data'] = $data;

    return json($result);

}