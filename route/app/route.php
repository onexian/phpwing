<?php
/**
 *
 * FILE_NAME: route.php
 * User: OneXian
 * Date: 2020/8/15
 */

lib('route')::get('login', 'auth/login')
    ->post('login', 'auth/login')
    ->get('index/bbb', 'index/index')
    ->get('parseadld', 'command.ParseAdLd/index')

;