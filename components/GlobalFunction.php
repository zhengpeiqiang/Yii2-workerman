<?php

function curlPost($url, $data, $post = true, $timeOut = false)
{
    $postData = array();

    foreach ($data as $k => $v) {
        if (is_array($v)) {
            foreach ($v as $_k => $_v) {
                $postData[] = $k . '[' . $_k . ']=' . $_v;
            }
        } else {
            $postData[] = $k . '=' . $v;
        }
    }
    $postData = implode('&', $postData);

    $headers = getAllHeaders();
    $headerArr = isset($headers['Serverfortest']) && strpos(strtolower($headers['Serverfortest']), 'test') !== false ? [
        "ServerForTest: Test"
    ] : [];

    $url = $post === true ? $url : $url . '&' . $postData;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArr);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIE, isset($headers['Cookie']) ? $headers['Cookie'] : '');
    if ($timeOut !== false) {
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);
    }
    if ($post === true) {
        // 指定post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // 添加变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }
    $result = curl_exec($ch);
    if ($result === false) { // 出错则显示错误信息
        errorLog('curl 返回错误：' . curl_error($ch)); // 输出错误 $msg
    }
    curl_close($ch);

    return $result;
}

function curl_post($url, $data)
{
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
        errorLog(curl_error($ch));
    }
    curl_close($ch);
    return $output;
}

function retMsg($code = '0', $msg = '', $data = [], $flag = false)
{
    $result = [
        'status' => strval($code),
        'msg' => $msg,
    ];
    if (!empty($data)) {
        $result['data'] = $data;
    }
    return $flag ? json_encode($result) : $result;
}