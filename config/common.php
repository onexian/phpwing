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
        'code' => 0,
        'msg'  => $msg??'ok',
        'data' => $data
    ];
    return json($result);
}

function json_no($msg = '', $data = [])
{
    $result = [
        'code' => -1,
        'msg'  => $msg??'error',
    ];
    if($data)$result['data'] = $data;

    return json($result);

}