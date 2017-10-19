<?php

/**

 * Created by PhpStorm.

 * User: Administrator

 * Date: 2016/6/7

 * Time: 14:49

 */

function scan($dir){

    $files = array();

    // Is there actually such a folder/file?

    if(file_exists($dir)){

        foreach(scandir($dir) as $f) {

            if(!$f || $f[0] == '.') {

                continue; // Ignore hidden files

            }

            if(is_dir($dir . '/' . $f)) {

                // The path is a folder

                $files[] = array(

                    "name" => $f,

                    "type" => "folder",

                    "path" => $f,

                    "times" => date("Y-m-d",filemtime($dir . '/' . $f)),

                    "items" => scan($dir . '/' . $f) // Recursively get the contents of the folder

                );

            }

            else {

                // It is a file

                $files[] = array(

                    "name" => $f,

                    "type" => substr($f,strrpos($f,".")+1),

                    "path" => $f,

                    "times" => date("Y-m-d",filemtime($dir . '/' . $f)),

                    "size" => filesize($dir . '/' . $f) // Gets the size of this file

                );

            }

        }

    }

    return $files;

}

function returnJsondata($data)

{

    exit(json_encode($data));

}

function check_verify($code, $id = ""){

    $verify = new \Think\Verify();

    return $verify->check($code, $id);

}

function trimall($str)//删除空格

{

    $qian=array(" ","　","\t","\n","\r");

    $hou=array("","","","","");

    return str_replace($qian,$hou,$str);

}



function del_array_unique($array)

{

    $out = array();

    foreach ($array as $key=>$value) {

        if (!in_array($value, $out))

        {

            $out[$key] = $value;

        }

    }

    return $out;

}



function com_zip($fileNameArr)

{

// 最终生成的文件名（含路径）

    $filename = "./Uploads/" . date('YmdHis') . ".zip";

// 生成文件

    $zip = new ZipArchive ();

    if ($zip->open($filename, ZIPARCHIVE::CREATE) !== TRUE) {

        exit ('无法打开文件，或者文件创建失败');

    }

//$fileNameArr 就是一个存储文件路径的数组 比如 array('./a/1.jpg','./a/2.jpg'....);

//注意，这里用的都是相对路径./表示当前目录

    foreach ($fileNameArr as $val) {

        // 第二个参数是放在压缩包中的文件名称，如果文件可能会有重复，就需要注意一下

        $zip->addFile($val, basename($val));

    }

// 关闭

    $zip->close();

    $url=substr($filename,1);

    Header("location:$url");

    /*vendor("dw.Download");

    $dw=new \download($filename); //下载文件

    $dw->getfiles();

    unlink($filename); //下载完成后要进行删除*/

}

//生成随机字符串

function rand_zifu($what,$number){

    $string='';

    for($i = 1; $i <= $number; $i++){

//混合

        $panduan=1;

        if($what == 3){

            if(rand(1,2)==1){

                $what=1;

            }else{

                $what=2;

            }

            $panduan=2;

        }

//数字

        if($what==1){

            $string.=rand(0,9);

        }elseif($what==2){

//字母

            $rand=rand(0,24);

            $b='a';

            for($a =0;$a <=$rand;$a++){

                $b++;

            }

            $string.=$b;

        }

        if($panduan==2)$what=3;

    }

    return $string;

}

//CURL GET请求HTTPS

function curl_get($http){

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在

    curl_setopt($ch,CURLOPT_URL,$http);

    curl_setopt($ch,CURLOPT_HEADER,0);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );

    $res = curl_exec($ch);

    curl_close($ch);

    $json_obj = json_decode($res,true);

    return $json_obj;

}



//在线交易订单支付处理函数

//函数功能：根据支付接口传回的数据判断该订单是否已经支付成功；

//返回值：如果订单已经成功支付，返回true，否则返回false；

function checkorderstatus($ordid){

    $Ord=M('ordermanage');

    $ordstatus=$Ord->where("o_sn='{$ordid}'")->getField('o_status');

    if($ordstatus==1){

        return true;

    }else{

        return false;

    }

}

function fcheckorderstatus($ordid){

    $Ord=M('fordermanage');

    $ordstatus=$Ord->where("f_sn='{$ordid}'")->getField('f_status');

    if($ordstatus==1){

        return true;

    }else{

        return false;

    }

}



//处理订单函数

//更新订单状态，写入订单支付后返回的数据

function orderhandle($parameter){

    $ordid=$parameter['out_trade_no'];

    $set_info=array(

        "o_status"=>1,

        "o_paytime"=>time()

    );

    $Ord=M('ordermanage');

    $Ord->where("o_sn='{$ordid}'")->setField($set_info);



    $o_uid=$Ord->where("o_sn='{$ordid}'")->getField("o_uid");

    $check_dis=M("usermanage")->where("id=".$o_uid)->getField("u_level");

    if($check_dis==0){

        $dis_set=M("usermanage")->where("id=".$o_uid)->setField("u_level",3);

    }

}

function forderhandle($parameter){

    $ordid=$parameter['out_trade_no'];

    $Ord=M('fordermanage');

    $Ord->where("f_sn='{$ordid}'")->setField("f_status",1);

}



//清除html标记

function html_preg($htmlstr){

    $str=preg_replace("/<([a-zA-Z]+)[^>]*>/","<\\1>",htmlspecialchars_decode($htmlstr));

    return $str;

}



function md5pass($str){

    $MP_str=md5(md5($str).C('rand_str'));

    return $MP_str;

}



function updaterose($arr,$roseid,$u_rose){

    $num=count($arr);

    if($num){

        foreach($arr as $k=>$v) {

            $where["u_thea|u_theb|u_thec"] = $v["id"];

            $where["u_rose"]=$u_rose;

            $where["u_isrose"]=array("eq",0);

            $info["u_rose"] = $roseid;

            $update = M("usermanage")->where($where)->save($info);

            $fordata=M("usermanage")->where("u_thea=".$v["id"])->field("id")->select();

            updaterose($fordata,$roseid);

        }

    }

}

function getExpress($id){



    //$express=M('expressmanage')->field('e_name')->find($id);

    $express=M('expressmanage')->where('id='.$id)->getField('e_name');

    return $express;

}

function getParent($id){

    $u_nick=M('usermanage')->field('u_nick,u_account,u_mobile')->find($id);

    if(empty($u_nick['u_mobile'])){

        return "无上级";

    }else{

        return $u_nick['u_mobile'];

    }



   /* if($u_nick['u_account']==""){

        return $u_nick['u_nick'];

    }else if($u_nick['u_nick']==""&&$u_nick['u_account']==""){

        return "空";

    }else{

        return $u_nick['u_account'];

    }*/



}

function changeTimes($times,$check){

    $arr=explode('-',$times);



    if($check==1){//开始

        $arr= "0,0,0,".$arr[1].",".$arr[2].",".$arr[0];

    }

    if($check==2){//结束

        $arr= "23,59,59,".$arr[1].",".$arr[2].",".$arr[0];

    }



    return mktime($arr);

}

function str_format_time($timestamp = '',$check)

{

    if (preg_match("/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}/i", $timestamp))

    {

        list($year,$month,$day)=explode("-",$timestamp);

        if($check==1){//开始

            $timestamp=mktime(0,0,0,$month,$day,$year);

        }

        if($check==2){//结束

            $timestamp=mktime(23,59,59,$month,$day,$year);

        }



    }

    return $timestamp;

}

function get_status($status){

    switch($status){

        case 0 :return "未付款";break;

        case 1 :return "已付款";break;

        case 2 :return "待收货";break;

        case 3 :return "已完成";break;

        case 4 :return "已关闭";break;

        case 5 :return "待退款";break;

        case 6 :return "已退款";break;

        case 7 :return "驳回退款";break;

    }



}

function get_gtitle($id){

    $gn=M('goodsmanage')->field('g_title')->find($id);

    return $gn['g_title'];

}

function get_fy($status){

    if($status==3){

        return "<span  style='color: #555555'>（已奖励）</span>";

    }else{

        return "<span style='color: red'>（未奖励）</span>";

    }

}

function expotExcel(){

    $Letter="ABCDEFGHIJKLMNOPQRSTUVWXYZ";

    $Letter=explode($Letter,'');





}



//检验密码强度代码

function password_strength($str){

  $score = 0;

  if(preg_match("/[0-9]+/",$str))

  {

     $score ++;

  }

  if(preg_match("/[0-9]{3,}/",$str))

  {

     $score ++;

  }

  if(preg_match("/[a-z]+/",$str))

  {

     $score ++;

  }

  if(preg_match("/[a-z]{3,}/",$str))

  {

     $score ++;

  }

  if(preg_match("/[A-Z]+/",$str))

  {

     $score ++;

  }

  if(preg_match("/[A-Z]{3,}/",$str))

  {

     $score ++;

  }

  if(preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)]+/",$str))

  {

     $score += 2;

  }

  if(preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)]{3,}/",$str))

  {

     $score ++ ;

  }

  if(strlen($str) >= 10)

  {

     $score ++;

  }

  return $score;

}

//判断验证码是否正确

function verification_code($phone,$code){



     if(!preg_match("/^1[34578]{1}\d{9}$/",$phone) || !$phone)

         return false;



     $codes=M('userverification')->where(['phone'=>$phone,'update_at'=>['gt',time()-600]])->order('update_at desc')->find(); //验证码有效期 10分钟



     if(empty($codes) || !$code || $code!=$codes['code'])

         return false;



     return true;

 }

 //检测用户是否已经被注册

 function is_registered($phone){

    $users=M('usermanage')->where(['u_account'=>$phone])->find();

    if($users)

        return $users; //已经被注册 返回注册信息



    return false; //可以继续

 }

 //验证登录状态

 function is_login($token,$user){

   if(!$token || !$user)

      return false;



   $login_info=M('usermanage')->where(['id'=>$user,'user_token'=>$token])->find();



   if(!$login_info)

      return false;



    return ture;

 }

//EtonePay支付需要

 //16进制ascii码转字符串

 function hexToStr($hex){

     $string='';

     for ($i=0; $i < strlen($hex)-1; $i+=2)

     {

         $string .= chr(hexdec($hex[$i].$hex[$i+1]));

     }

     return $string;

 }



 //字符串转16进制ascii码

  function strToHex($string){

     $hex='';

     for ($i=0; $i < strlen($string); $i++)

     {

         $hex .= dechex(ord($string[$i]));

     }

     return strtoupper($hex);

 }

