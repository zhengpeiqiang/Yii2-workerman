<?php

namespace app\controllers;

class ContentController extends CommonController
{
    public $enableCsrfValidation = false;

    public function actionTest()
    {
        $websocketIp    = \Yii::$app->params["websocketIp"];
        $type           = \Yii::$app->request->post('type',\Yii::$app->params["websocketPublish"]);
        $content        = \Yii::$app->request->post('content',"");
        $to             = \Yii::$app->request->post('to',0);
        if ($to){
            $url = "http://$websocketIp:2121/?type=$type&content=$content&to=$to";
        }else{
            $url = "http://$websocketIp:2121/?type=$type&content=$content";
        }
        $res = curlPost($url,[],"get");
        return $this->retMsg("1","",$res);
    }
}