<?php
return array(
	//'配置项'=>'配置值'
    //支付宝配置参数
    'alipay_config'=>array(
        'partner' =>'2088421684545155',   //这里是你在成功申请支付宝接口后获取到的PID；
        'key'=>'oyzhhyvmh7k6jbeln56vbwwgg2t9wczw',//这里是你在成功申请支付宝接口后获取到的Key
        'sign_type'=>strtoupper('MD5'),// MD5密钥，安全检验码，由数字和字母组成的32位字符串
        'input_charset'=> strtolower('utf-8'),//字符编码格式 目前支持utf-8
        'cacert'=> getcwd().'\\cacert.pem',//ca证书路径地址，用于curl中ssl校验,请保证cacert.pem文件在当前文件夹目录中
        'transport'=> 'http',//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    ),
    //以上配置项，是从接口包中alipay.config.php 文件中复制过来，进行配置；

    'alipay'   =>array(
        //这里是卖家的支付宝账号，也就是你申请接口时注册的支付宝账号
        'seller_email'=>'xyclsw@163.com',
        //这里是异步通知页面url，提交到项目的Pay控制器的notifyurl方法；
        'notify_url'=>'http://www.xyclsw.com/index.php/Home/Notify/alipaynotifyurl',
        //这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；
        'return_url'=>'http://www.xyclsw.com/index.php/Home/Notify/alipaynotifyurl',
        //支付成功跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参payed（已支付列表）
        'successpage'=>'',
        //支付失败跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参unpay（未支付列表）
        'errorpage'=>'',
    ),
    //微信
    'WX_APPID'=>"wxceadb8ca48ed02ae",
    'WX_APPSECRET'=>"ba4e6e710f9ccd4c020bc93f7f3042d0",

    //微信

    //巨辰短信接口
    'LSZ_JC_ACCOUT'=>'jnzhengh_xycl',
    'LSZ_JC_PWD'=>'Xycl8888',
    'LSZ_JC_URL'=>"http://120.24.167.205/msg/HttpSendSM",
    'LSZ_JC_CODE_LENGTH'=>4,
    'LSZ_JC_CODE_SIGN'=>'翔云长禄生物科技',
	//    'LSZ_JC_ACCOUT'=>'jnzhengh_xycl',
    'LSZ_JC_ACCOUT'=>'xyclsw',
//    'LSZ_JC_PWD'=>'Xycl8888',
    'LSZ_JC_PWD'=>'Xyclsw8',
    'LSZ_JC_URL'=>"http://www.qybor.com:8500/shortMessage",
    //结束 巨辰短信接口
	 //快递100
    'LSZ_KUAIDI100_APP_KEY'=>"",//请将XXXXXX替换成您在http://kuaidi100.com/app/reg.html申请到的KEY
    'LSZ_KUAIDI100_URL'=>"http://api.kuaidi100.com",
    'LSZ_KUAIDI100_POWERED'=>'查询数据由：<a href="http://kuaidi100.com" target="_blank">KuaiDi100.Com （快递100）</a> 网站提供 ',
    //快递100
    //网站名称：
    "WEB_NAME"=>"翔云长禄养生保健分享平台",
    "TEL"=>"400-117-5071",

);