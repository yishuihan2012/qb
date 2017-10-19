<?php
$result=$_POST;
$data=file_get_contents("php://input"); 
$data=trim($data);
file_put_contents('filecontent.txt',$data);
// $res=$GLOBALS['HTTP_RAW_POST_DATA'];
// file_put_contents('res.txt',$res);
file_put_contents('time.txt','----'.date('Y-m-d H:i:s'),time(),FILE_APPEND);
$data=json_decode($data,true);
if($data['state']=='Successful'){
	file_put_contents('success.txt',$data['state']);
	echo 'success';
}

