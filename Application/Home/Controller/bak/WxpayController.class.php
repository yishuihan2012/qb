<?php
/* 
+------------------------------------------------------+
| 设计开发：Webster	Tel:17095135002	邮箱：312549912@qq.com	   |
+------------------------------------------------------+
*/
namespace Home\Controller;
use Think\Controller;
class WxpayController extends Controller {
	//初始化
	public function _initialize()
	{
		//获取来源地址
		$URL['PHP_SELF'] = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : $_SERVER['ORIG_PATH_INFO']);   //当前页面名称
		$URL['DOMAIN'] = $_SERVER['SERVER_NAME'];  //域名(主机名)
		$URL['QUERY_STRING'] = $_SERVER['QUERY_STRING'];   //URL 参数
		$URL['URI'] = $URL['PHP_SELF'].($URL['QUERY_STRING'] ? "?".$URL['QUERY_STRING'] : "");
		$this->fromurl = "http://".$URL['DOMAIN'].$URL['PHP_SELF'].($URL['QUERY_STRING'] ? "_".$URL['QUERY_STRING'] : ""); //完整URL地址
	}
	public function payment(){
		//全局引入微信支付类
		Vendor('Wxpay.WxPayPubHelper.WxPayPubHelper');
		//使用jsapi接口
		$jsApi = new \JsApi_pub();
		//=========步骤1：网页授权获取用户openid============
		//通过code获得openid
		if (!isset($_GET['code'])) {
			//触发微信返回code码
			$url = $jsApi->createOauthUrlForCode($this->fromurl);
			Header("Location: $url");
		} else {
			//获取code码，以获取openid
			$code = $_GET['code'];
			$jsApi->setCode($code);
			$openid = $jsApi->getOpenId();
		}

		//=========步骤2：使用统一支付接口，获取prepay_id============
        //订单查询
        if($code) {
            $id=session("OrderID");
            $MP_sql=M("ordermanage")->where("id=".$id)->find();

            $MP_address=M("addressmanage")->where("id=".$MP_sql["o_aid"])->find();
            $MP_sql["o_ginfo"]=unserialize($MP_sql["o_ginfo"]);
            foreach($MP_sql["o_ginfo"] as $key=>$val){
                $MP_goods[$key]=M("goodsmanage")->where("id=".$val["gid"])->field("g_title")->find();
                $MP_goods[$key]["gnum"]=$val["gnum"];
                $MP_sql["o_gnum"]+=$val["gnum"];
            }
            if($MP_address['a_provice']=="新疆维吾尔自治区"||$MP_address['a_provice']=="西藏自治区"){
                $MP_sql['o_price']=$MP_sql['o_price']+15*$MP_sql["o_gnum"];
            }
        }
		//使用统一支付接口
		$unifiedOrder = new \UnifiedOrder_pub();
		//设置统一支付接口参数
		//设置必填参数
		//appid已填,商户无需重复填写
		//mch_id已填,商户无需重复填写
		//noncestr已填,商户无需重复填写
		//spbill_create_ip已填,商户无需重复填写
		//sign已填,商户无需重复填写
		$unifiedOrder->setParameter("openid", $openid);//商品描述
		$unifiedOrder->setParameter("body", "订单支付".$MP_sql["o_sn"]);//商品描述
		//自定义订单号，此处仅作举例
		$unifiedOrder->setParameter("out_trade_no", $MP_sql["o_sn"]);//商户订单号
        $total_fee=$MP_sql["o_price"]*100;
		$unifiedOrder->setParameter("total_fee", $total_fee);//总金额
//		$unifiedOrder->setParameter("total_fee", 1);//总金额
		$unifiedOrder->setParameter("notify_url", "http://www.xyclsw.com/index.php/Home/Notify/wxnotify");//通知地址
		$unifiedOrder->setParameter("trade_type", "JSAPI");//交易类型
		$prepay_id = $unifiedOrder->getPrepayId();
		//=========步骤3：使用jsapi调起支付============
		$jsApi->setPrepayId($prepay_id);
		$jsApiParameters = $jsApi->getParameters();
		$this->assign('jsApiParameters', $jsApiParameters);
        $this->assign("goods_list",$MP_goods);
        $this->assign("o",$MP_sql);
		$this->display();
	}
	//分代理支付
    public function trose(){
        //全局引入微信支付类
        Vendor('Wxpay.WxPayPubHelper.WxPayPubHelper');
        //使用jsapi接口
        $jsApi = new \JsApi_pub();
        //=========步骤1：网页授权获取用户openid============
        //通过code获得openid
        if (!isset($_GET['code'])) {
            //触发微信返回code码
            $url = $jsApi->createOauthUrlForCode($this->fromurl);
            Header("Location: $url");
        } else {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $jsApi->setCode($code);
            $openid = $jsApi->getOpenId();
        }

        //=========步骤2：使用统一支付接口，获取prepay_id============
        //订单查询
        if($code) {
            $id=session("FOrderID");
            $MP_sql=M("fordermanage")->where("id=".$id)->find();
        }
        //使用统一支付接口
        $unifiedOrder = new \UnifiedOrder_pub();
        //设置统一支付接口参数
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //spbill_create_ip已填,商户无需重复填写
        //sign已填,商户无需重复填写
        $unifiedOrder->setParameter("openid", $openid);//商品描述
        $unifiedOrder->setParameter("body", "订单支付".$MP_sql["f_sn"]);//商品描述
        //自定义订单号，此处仅作举例
        $unifiedOrder->setParameter("out_trade_no", $MP_sql["f_sn"]);//商户订单号
        $total_fee=$MP_sql["f_price"]*100;
        $unifiedOrder->setParameter("total_fee", $total_fee);//总金额
//		$unifiedOrder->setParameter("total_fee", 1);//总金额
        $unifiedOrder->setParameter("notify_url", "http://www.xyclsw.com/index.php/Home/Notify/wxnotify");//通知地址
        $unifiedOrder->setParameter("trade_type", "JSAPI");//交易类型
        $prepay_id = $unifiedOrder->getPrepayId();
        //=========步骤3：使用jsapi调起支付============
        $jsApi->setPrepayId($prepay_id);
        $jsApiParameters = $jsApi->getParameters();
        $this->assign('jsApiParameters', $jsApiParameters);
        $this->assign("f",$MP_sql);
        $this->display();
    }
	//JSAPI支付通知
	public function notify(){
		//使用通用通知接口
		$notify = new \Notify_pub();
	
		//存储微信的回调
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		$notify->saveData($xml);
	
		//验证签名，并回应微信。
		//对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
		//微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
		//尽可能提高通知的成功率，但微信不保证通知最终能成功。
		if($notify->checkSign() == FALSE){
			$notify->setReturnParameter("return_code","FAIL");//返回状态码
			$notify->setReturnParameter("return_msg","签名失败");//返回信息
		}else{
			$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
		}
		$returnXml = $notify->returnXml();
		echo $returnXml;
	
		//==商户根据实际情况设置相应的处理流程，此处仅作举例=======
	
		//以log文件形式记录回调信息
		//         $log_ = new Log_();
		$log_name= __ROOT__."/Public/Weixin/notify_url.log";//log文件路径
	
		log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");
	
		if($notify->checkSign() == TRUE)
		{
			if ($notify->data["return_code"] == "FAIL") {
				//此处应该更新一下订单状态，商户自行增删操作
				log_result($log_name,"【通信出错】:\n".$xml."\n");
			}
			elseif($notify->data["result_code"] == "FAIL"){
				//此处应该更新一下订单状态，商户自行增删操作
				log_result($log_name,"【业务出错】:\n".$xml."\n");
			}
			else{
				//此处应该更新一下订单状态，商户自行增删操作
				log_result($log_name,"【支付成功】:\n".$xml."\n");
			}
	
			//商户自行增加处理流程,
			//例如：更新订单状态
			//例如：数据库操作
			//例如：推送支付完成信息
		}
	}
	//扫码微信支付模式一
	public function native_pay()
    {
    	//全局引入微信支付类
    	Vendor('Wxpay.WxPayPubHelper.WxPayPubHelper');
        //设置静态链接
        $nativeLink = new \NativeLink_pub();
        
        //设置静态链接参数
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //time_stamp已填,商户无需重复填写
        //sign已填,商户无需重复填写
        $product_id = \WxPayConf_pub::APPID."static";//自定义商品id
        $nativeLink->setParameter("product_id",$product_id);//商品id
        //获取链接
        $product_url = $nativeLink->getUrl();
        //使用短链接转换接口
        $shortUrl = new \ShortUrl_pub();
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //sign已填,商户无需重复填写
        $shortUrl->setParameter("long_url",$product_url);//URL链接
        //获取短链接
        $codeUrl = $shortUrl->getShortUrl();
        $this->assign('product_url',$product_url);
        $this->assign('codeUrl',$codeUrl);
        $this->display();
    }
	//扫码支付通知
	public function todoPost()
    {
    	//全局引入微信支付类
    	Vendor('Wxpay.WxPayPubHelper.WxPayPubHelper');
        //以log文件形式记录回调信息，用于调试
        $log_name = __ROOT__."/Public/Weixin/native_call.log";
        //使用native通知接口
        $nativeCall = new \NativeCall_pub();
        
        //接收微信请求
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        log_result($log_name,"【接收到的native通知】:\n".$xml."\n");
        $nativeCall->saveData($xml);
        
        if($nativeCall->checkSign() == FALSE){
            $nativeCall->setReturnParameter("return_code","FAIL");//返回状态码
            $nativeCall->setReturnParameter("return_msg","签名失败");//返回信息
        }
        else
        {
            //提取product_id
            $product_id = $nativeCall->getProductId();
        
            //使用统一支付接口
            $unifiedOrder = new \UnifiedOrder_pub();
        
            //根据不同的$product_id设定对应的下单参数，此处只举例一种
            switch ($product_id)
            {
                
                case \WxPayConf_pub::APPID."static"://与native_call_qrcode.php中的静态链接二维码对应
                    //设置统一支付接口参数
                    //设置必填参数
                    //appid已填,商户无需重复填写
                    //mch_id已填,商户无需重复填写
                    //noncestr已填,商户无需重复填写
                    //spbill_create_ip已填,商户无需重复填写
                    //sign已填,商户无需重复填写
                    $unifiedOrder->setParameter("body","贡献一分钱");//商品描述
                    //自定义订单号，此处仅作举例
                    $timeStamp = time();
                    $out_trade_no = \WxPayConf_pub::APPID.$timeStamp;
                    $unifiedOrder->setParameter("out_trade_no",$out_trade_no);//商户订单号             $unifiedOrder->setParameter("product_id","$product_id");//商品ID
                    $unifiedOrder->setParameter("total_fee","1");//总金额
                    $unifiedOrder->setParameter("notify_url",\WxPayConf_pub::NOTIFY_URL);//通知地址
                    $unifiedOrder->setParameter("trade_type","NATIVE");//交易类型
                    $unifiedOrder->setParameter("product_id",$product_id);//用户标识
                    //非必填参数，商户可根据实际情况选填
                    //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
                    //$unifiedOrder->setParameter("device_info","XXXX");//设备号
                    //$unifiedOrder->setParameter("attach","XXXX");//附加数据
                    //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
                    //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
                    //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
                    //$unifiedOrder->setParameter("openid","XXXX");//用户标识
        
                    //获取prepay_id
                    $prepay_id = $unifiedOrder->getPrepayId();
                    //设置返回码
                    //设置必填参数
                    //appid已填,商户无需重复填写
                    //mch_id已填,商户无需重复填写
                    //noncestr已填,商户无需重复填写
                    //sign已填,商户无需重复填写
                    $nativeCall->setReturnParameter("return_code","SUCCESS");//返回状态码
                    $nativeCall->setReturnParameter("result_code","SUCCESS");//业务结果
                    $nativeCall->setReturnParameter("prepay_id",$prepay_id);//预支付ID
        
                    break;
                default:
                    //设置返回码
                    //设置必填参数
                    //appid已填,商户无需重复填写
                    //mch_id已填,商户无需重复填写
                    //noncestr已填,商户无需重复填写
                    //sign已填,商户无需重复填写
                    $nativeCall->setReturnParameter("return_code","SUCCESS");//返回状态码
                    $nativeCall->setReturnParameter("result_code","FAIL");//业务结果
                    $nativeCall->setReturnParameter("err_code_des","此商品无效");//业务结果
                    break;
            }
        
        }
        
        //将结果返回微信
        $returnXml = $nativeCall->returnXml();
        log_result($log_name,"【返回微信的native响应】:\n".$returnXml."\n");

        echo $returnXml;
    }
	//微信扫码支付 模式2
	public function native_pays(){
		//全局引入微信支付类
		Vendor('Wxpay.WxPayPubHelper.WxPayPubHelper');
		//使用统一支付接口
        $unifiedOrder = new \UnifiedOrder_pub();
        
        //设置统一支付接口参数
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //spbill_create_ip已填,商户无需重复填写
        //sign已填,商户无需重复填写
        $unifiedOrder->setParameter("body","贡献一分钱");//商品描述
        //自定义订单号，此处仅作举例
        $timeStamp = time();
        $out_trade_no = \WxPayConf_pub::APPID.$timeStamp;
        $unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号 
        $unifiedOrder->setParameter("total_fee","1");//总金额
        $unifiedOrder->setParameter("notify_url", \WxPayConf_pub::NOTIFY_URL);//通知地址 
        $unifiedOrder->setParameter("trade_type","NATIVE");//交易类型
        //非必填参数，商户可根据实际情况选填
        //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号  
        //$unifiedOrder->setParameter("device_info","XXXX");//设备号 
        //$unifiedOrder->setParameter("attach","XXXX");//附加数据 
        //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
        //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间 
        //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记 
        //$unifiedOrder->setParameter("openid","XXXX");//用户标识
        //$unifiedOrder->setParameter("product_id","XXXX");//商品ID
        
        //获取统一支付接口结果
        $unifiedOrderResult = $unifiedOrder->getResult();
        
        //商户根据实际情况设置相应的处理流程
        if ($unifiedOrderResult["return_code"] == "FAIL") 
        {
            //商户自行增加处理流程
            echo "通信出错：".$unifiedOrderResult['return_msg']."<br>";
        }
        elseif($unifiedOrderResult["result_code"] == "FAIL")
        {
            //商户自行增加处理流程
            echo "错误代码：".$unifiedOrderResult['err_code']."<br>";
            echo "错误代码描述：".$unifiedOrderResult['err_code_des']."<br>";
        }
        elseif($unifiedOrderResult["code_url"] != NULL)
        {
            //从统一支付接口获取到code_url
            $code_url = $unifiedOrderResult["code_url"];
            //商户自行增加处理流程
            //......
        }
        $this->assign('out_trade_no',$out_trade_no);
        $this->assign('code_url',$code_url);
        $this->assign('unifiedOrderResult',$unifiedOrderResult);
        
        $this->display();
	}
	//刷卡支付
	public function micropay()
	{
		Vendor('Wxpay.WxPayMicropayHelper.WxPayMicropayHelper');
		//自定义订单号，此处仅作举例
		$timeStamp = time();
		$out_trade_no = \WxPayConf_micropay::APPID.$timeStamp;
	
		//获取用户一维码
		if (isset($_POST["auth_code"]))
		{
			$auth_code = $_POST["auth_code"];
	
			//使用被扫支付接口
			$micropayCall = new \MicropayCall();
	
			//设置被扫支付接口参数
			//设置必填参数
			//appid已填,商户无需重复填写
			//mch_id已填,商户无需重复填写
			//noncestr已填,商户无需重复填写
			//spbill_create_ip已填,商户无需重复填写
			//sign已填,商户无需重复填写
			$micropayCall->setParameter("body","贡献一分钱");//商品描述
			$micropayCall->setParameter("out_trade_no","$out_trade_no");//商户订单号
			$micropayCall->setParameter("total_fee","1");//总金额
			$micropayCall->setParameter("auth_code","$auth_code");//交易类型
			//非必填参数，商户可根据实际情况选填
			//$micropayCall->setParameter("sub_mch_id","XXXX");//子商户号
			//$micropayCall->setParameter("device_info","XXXX");//设备号
			//$micropayCall->setParameter("attach","XXXX");//附加数据
			//$micropayCall->setParameter("time_start","XXXX");//交易起始时间
			//$micropayCall->setParameter("time_expire","XXXX");//交易结束时间
			//$micropayCall->setParameter("goods_tag","XXXX");//商品标记
			//$micropayCall->setParameter("openid","XXXX");//用户标识
			//$micropayCall->setParameter("product_id","XXXX");//商品ID
	
			//提交订单
			$micropayCallResult = $micropayCall->getResult();
	
			//商户根据实际情况设置相应的处理流程,此处仅作举例
			if ($micropayCallResult["return_code"] == "FAIL")
			{
				echo "通信出错：".$micropayCallResult['return_msg']."<br>";
			}
			elseif($micropayCallResult["result_code"] == "FAIL")
			{
				echo "出错"."<br>";
				echo "错误代码：".$micropayCallResult['err_code']."<br>";
				echo "错误代码描述：".$micropayCallResult['err_code_des']."<br>";
			}
			else
			{
				echo "用户标识：".$micropayCallResult['openid']."<br>";
				echo "是否关注公众账号：".$micropayCallResult['is_subscribe']."<br>";
				echo "交易类型：".$micropayCallResult['trade_type']."<br>";
				echo "付款银行：".$micropayCallResult['bank_type']."<br>";
				echo "总金额：".$micropayCallResult['total_fee']."<br>";
				echo "现金券金额：".$micropayCallResult['coupon_fee']."<br>";
				echo "货币种类：".$micropayCallResult['fee_type']."<br>";
				echo "微信支付订单号：".$micropayCallResult['transaction_id']."<br>";
				echo "商户订单号：".$micropayCallResult['out_trade_no']."<br>";
				echo "商家数据包：".$micropayCallResult['attach']."<br>";
				echo "支付完成时间：".$micropayCallResult['time_end']."<br>";
			}
		}
		else
		{
			$this->assign('out_trade_no',$out_trade_no);
			$this->display();
		}
	}
	//发送现金红包
    public function sendRedpack()
    {
    	//全局引入微信支付类
    	Vendor('Wxpay.WxPayPubHelper.WxPayPubHelper');
        //调用请求接口基类
        $Redpack = new \Redpack_pub();
        
        //=========步骤1：网页授权获取用户openid============
        //通过code获得openid
        if (!isset($_GET['code']))
        {
            //触发微信返回code码
            //$reduct_uri = WEB_HOST."/index.php/Home/WxCashRedPack/sendRedpack";
            $url = $Redpack->createOauthUrlForCode($this->fromurl);
            Header("Location: $url");
        }else
        {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $Redpack->setCode($code);
            $openid = $Redpack->getOpenId();
        }
         
        
        
        //商户订单号
        $Redpack->setParameter('mch_billno', \WxPayConf_pub::APPID."static");
        //提供方名称
        $Redpack->setParameter('nick_name', "江苏七八九");
        //商户名称
        $Redpack->setParameter('send_name', "江苏七八九");
        //用户openid
//         $Redpack->setParameter('re_openid', $parameterValue);
        //付款金额
        $Redpack->setParameter('total_amount', 100);
        //最小红包金额
        $Redpack->setParameter('min_value', 100);
        //最大红包金额
        $Redpack->setParameter('max_value', 100);
        //红包发放总人数
        $Redpack->setParameter('total_num', 1);
        //红包祝福语
        $Redpack->setParameter('wishing', "现金红包教程祝大家写代码快乐");
        //活动名称
        $Redpack->setParameter('act_name', "现金红包教程");
        //备注
        $Redpack->setParameter('remark', "现金红包教程祝大家写代码快乐");
        //以下是非必填项目
        //子商户号  
//         $Redpack->setParameter('sub_mch_id', $parameterValue);
//        //商户logo的url
//         $Redpack->setParameter('logo_imgurl', $parameterValue);
//         //分享文案
//         $Redpack->setParameter('share_content', $parameterValue);
//         //分享链接
//         $Redpack->setParameter('share_url', $parameterValue);
//         //分享的图片
//         $Redpack->setParameter('share_imgurl', $parameterValue);
        
        
        
        $result = $Redpack->sendRedpack();
        
        dump($result);
    }
    //发送裂变红包
    public function sendRedpacks()
    {
    	//全局引入微信支付类
    	Vendor('Wxpay.WxPayPubHelper.WxPayPubHelper');
    	//调用请求接口基类
    	$Redpack = new \Groupredpack_pub();
    
    	//=========步骤1：网页授权获取用户openid============
    	//通过code获得openid
    	if (!isset($_GET['code']))
    	{
    		//触发微信返回code码
    		//$reduct_uri = WEB_HOST."/index.php/Home/WxGroupRedPack/sendRedpack";
    		$url = $Redpack->createOauthUrlForCode($this->fromurl);
    		Header("Location: $url");
    	}else
    	{
    		//获取code码，以获取openid
    		$code = $_GET['code'];
    		$Redpack->setCode($code);
    		$openid = $Redpack->getOpenId();
    	}
    	//商户订单号
    	$timeStamp = time();
    	$Redpack->setParameter('mch_billno', \WxPayConf_pub::APPID.$timeStamp);
    	//商户名称
    	$Redpack->setParameter('send_name', "gaoyl101");
    	//接受红包的种子用户
    	//$Redpack->setParameter('re_openid', $parameterValue);
    	//付款金额
    	$Redpack->setParameter('total_amount', 300);
    	//红包发放总人数
    	$Redpack->setParameter('total_num', 3);
    	$Redpack->setParameter('amt_type','ALL_RAND');
    	//红包祝福语
    	$Redpack->setParameter('wishing', "现金红包教程祝大家写代码快乐");
    	//活动名称
    	$Redpack->setParameter('act_name', "现金红包教程");
    	//备注
    	$Redpack->setParameter('remark', "现金红包教程祝大家写代码快乐");
    	//以下是非必填项目
    	//子商户号
    	//         $Redpack->setParameter('sub_mch_id', $parameterValue);
    	//        //商户logo的url
    	//         $Redpack->setParameter('amt_list', '200|100|100');
    
    
    	$result = $Redpack->sendRedpack();
    
    	dump($result);
    }
}