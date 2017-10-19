<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-09-14
 * Time: 09:04
 */

namespace Home\Controller;
use Think\Controller;
class SmscodeController extends ConController
{
    public function sendCode(){

        $mobile=I('post.mobile');
        session('L_code','');
        header('content-type:text/html;charset=utf-8');
        $Client = new \Org\Util\HttpClient();
//请求的页面地址
        $url =C('LSZ_JC_URL');
        $code = '';
        $length=C('LSZ_JC_CODE_LENGTH');
        for($i=0;$i<$length;$i++){
            $code .= mt_rand(0,9);
        }

        session('L_code',$code);
        $params = array(
            'account'=>C('LSZ_JC_ACCOUT'),	//账号
            'pswd'=>C('LSZ_JC_PWD'),		//密码
            'mobile'=>$mobile,		//手机号码
            'msg'=>"你的验证码为:{$code},请妥善保管.【".C('LSZ_JC_CODE_SIGN')."】",	//短信内容
            'needstatus'=>'true',	//是否需要状态报告
            'product'=>''	//留空
        );
		$params = array(
            //'account'=>C('LSZ_JC_ACCOUT'),	//账号
			'username' => C('LSZ_JC_ACCOUT'),	//账号
            //'pswd'=>C('LSZ_JC_PWD'),		//密码
			'passwd' => C('LSZ_JC_PWD'),		//密码
            'phone'=>$mobile,		//手机号码
            'msg'=>"你的验证码为:{$code},请妥善保管.【".C('LSZ_JC_CODE_SIGN')."】",	//短信内容
            'needstatus'=>'true',	//是否需要状态报告
            'product'=>''	//留空
        );
        if(is_numeric($mobile)){
            $pageContents = $Client->quickPost($url,$params);
            echo $pageContents;
        }else{
            $this->ajaxReturn('手机号格式错误');
        }

    }

    /**
     * 忘记密码 发送验证码
     */
    public function forgetSendCode(){
        session('L_code','');
        $mobile=I('post.mobile');
        header('content-type:text/html;charset=utf-8');
        $Client = new \Org\Util\HttpClient();
//请求的页面地址
        $url =C('LSZ_JC_URL');
        $code = '';
        $length=C('LSZ_JC_CODE_LENGTH');
        for($i=0;$i<$length;$i++){
            $code .= mt_rand(0,9);
        }
        session('L_code',$code);
        $params = array(
            'account'=>C('LSZ_JC_ACCOUT'),	//账号
            'pswd'=>C('LSZ_JC_PWD'),		//密码
            'mobile'=>$mobile,		//手机号码
            'msg'=>"你的验证码为:{$code},请妥善保管.【".C('LSZ_JC_CODE_SIGN')."】",	//短信内容
            'needstatus'=>'true',	//是否需要状态报告
            'product'=>''	//留空
        );
        if(is_numeric($mobile)){

            $pageContents = $Client->quickPost($url,$params);
           //echo   $code."提交返回=".$pageContents;
        }else{
            $this->ajaxReturn('手机号格式错误');
        }

    }

    /**
     * 验证忘记密码 修改密码时获得的验证码是否正确
     * LSZ 20160914
     */
    public function forgetVerifyCode(){
        if(I('post.vcode')==""){
            echo -1;
            die;
        }
        if(I('post.vcode')!=session('L_code')){
            echo 0;
        }else{
            echo 1;
        }
    }
}