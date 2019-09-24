<?php

namespace app\controllers;

use yii\web\Controller;
use yii\web\Response;

class CommonController extends Controller
{
    public $retArr = [
        'status' => 1,
        'state' => 0,//业务状态
        'msg' => '',
        'data' => [],
    ];

    public function init()
    {
        // 设置输出JSON格式数据
        \Yii::$app->response->format = Response::FORMAT_JSON;
    }

    //返回信息
    public function retMsg($status = 1, $msg = '', $data = [], $state = 0)
    {
        $this->retArr = [
            'status' => $status,
            'state' => $state,
            'msg' => $msg,
            'data' => $data,
        ];
        return $this->retArr;
    }
}