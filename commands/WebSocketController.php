<?php
namespace app\commands;
use PHPSocketIO\SocketIO;
use Workerman\Lib\Timer;
use Workerman\Worker;
use yii\console\Controller;

class WebSocketController extends Controller
{
    // 全局数组保存uid在线数据
    public $uidConnectionMap = array();
    // 记录最后一次广播的在线用户数
    public $last_online_count = 0;
    // 记录最后一次广播的在线页面数
    public $last_online_page_count = 0;

    public $sender_io;

    public $websocketIp;

    public function init()
    {
        $this->websocketIp = \Yii::$app->params["websocketIp"];
    }

    public function actionTest(){
        // PHPSocketIO服务
        $sender_io = new SocketIO(2120);
        $this->sender_io = $sender_io;

        // 客户端发起连接事件时，设置连接socket的各种事件回调
        $sender_io->on('connection', function($socket){
            // 当客户端发来登录事件时触发
            $socket->on('login', function ($uid)use($socket){
                // 已经登录过了
                if(isset($socket->uid)){
                    return;
                }
                // 更新对应uid的在线数据
                $uid = (string)$uid;
                if(!isset($this->uidConnectionMap[$uid]))
                {
                    $this->uidConnectionMap[$uid] = 0;
                }
                // 这个uid有++$uidConnectionMap[$uid]个socket连接
                ++$this->uidConnectionMap[$uid];
                // 将这个连接加入到uid分组，方便针对uid推送数据
                $socket->join($uid);
                $socket->uid = $uid;
                // 更新这个socket对应页面的在线数据
                $socket->emit('update_online_count', "当前<b>{$this->last_online_count}</b>人在线，共打开<b>{$this->last_online_page_count}</b>个页面");
            });

            // 当客户端断开连接是触发（一般是关闭网页或者跳转刷新导致）
            $socket->on('disconnect', function () use($socket) {
                if(!isset($socket->uid))
                {
                    return;
                }
                // 将uid的在线socket数减一
                if(--$this->uidConnectionMap[$socket->uid] <= 0)
                {
                    unset($this->uidConnectionMap[$socket->uid]);
                }
            });
        });

        // 当$sender_io启动后监听一个http端口，通过这个端口可以给任意uid或者所有uid推送数据
        $sender_io->on('workerStart', function(){
            // 监听一个http端口
            //$inner_http_worker = new Worker('http://192.168.0.171:2121');
            $websocketIp = $this->websocketIp;
            $inner_http_worker = new Worker("http://$websocketIp:2121");
            // 当http客户端发来数据时触发
            $inner_http_worker->onMessage = function($http_connection, $data){
                $_POST = $_POST ? $_POST : $_GET;
                // 推送数据的url格式 type=publish&to=uid&content=xxxx
                switch(@$_POST['type']){
                    case 'ecmspublishecms777Zahsgdjsgd':
                        $to = @$_POST['to'];
                        $_POST['content'] = htmlspecialchars(@$_POST['content']);
                        // 有指定uid则向uid所在socket组发送数据
                        if($to){
                            $this->sender_io->to($to)->emit('new_msg', $_POST['content']);
                            // 否则向所有uid推送数据
                        }else{
                            $this->sender_io->emit('new_msg', @$_POST['content']);
                        }
                        // http接口返回，如果用户离线socket返回fail
                        if($to && !isset($this->uidConnectionMap[$to])){
                            return $http_connection->send('offline');
                        }else{
                            return $http_connection->send('ok');
                        }
                }
                return $http_connection->send('fail');
            };
            // 执行监听
            $inner_http_worker->listen();

            // 一个定时器，定时向所有uid推送当前uid在线数及在线页面数
            Timer::add(1, function(){
                $online_count_now = count($this->uidConnectionMap);
                $online_page_count_now = array_sum($this->uidConnectionMap);
                // 只有在客户端在线数变化了才广播，减少不必要的客户端通讯
                if($this->last_online_count != $online_count_now || $this->last_online_page_count != $online_page_count_now)
                {
                    $this->sender_io->emit('update_online_count', "当前<b>{$online_count_now}</b>人在线，共打开<b>{$online_page_count_now}</b>个页面");
                    $this->last_online_count = $online_count_now;
                    $this->last_online_page_count = $online_page_count_now;
                }
            });
        });

        if(!defined('GLOBAL_START'))
        {
            Worker::runAll();
        }
    }

    public function actionSend(){
        $websocketIp = $this->websocketIp;
        $data["type"] = "ecmspublishecms777Zahsgdjsgd";
        $data["content"] = "你好，我是你爹";

        $url = "http://$websocketIp:2121";
        $header = array(
            "Content-type: application/json;charset='utf-8'",
        );
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0(compatible;MSIE 5.01;Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        if (curl_errno($ch)) {
            var_dump(curl_error($ch));
        }
        curl_close($ch);
        return $output;
    }

}