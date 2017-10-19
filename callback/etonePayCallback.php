<?php
// 易通支付回调
$url ="fy.xijiakeji.com/index.php?s=/Api/Etonepay/callback";
file_put_contents('1.txt',"我被调用了");
file_put_contents('respCode.txt',json_encode($_GET['respCode']));

file_put_contents('get.txt',json_encode($_GET));
file_put_contents('request.txt',json_encode($_REQUEST));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $_GET);

$output = curl_exec($ch);
curl_close($ch);

echo($output);
