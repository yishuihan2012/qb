<?php
namespace Api\Controller;
use Think\Controller;

//etong支付回调接口
class EtonepayController extends Controller{

  private $post;

  public function _initialize(){

    $post=I('post.');

    $this->post=$post;

  }
  //生成订单信息
  public function create_order(){

    exit(json_encode(['code'=>100,'msg'=>'正在升级中..','data'=>'']));

    if(!isset($this->post['token']) || !isset($this->post['user']))
        exit(json_encode(['code'=>112,'msg'=>'登录信息失效！请重新登录','data'=>'']));

    if(!is_login($this->post['token'],$this->post['user']))
        exit(json_encode(['code'=>112,'msg'=>'登录信息失效！请重新登录','data'=>'']));

    //添加订单
    $user=M('usermanage')->where(['id'=>$this->post['user']])->find();

    if(!$user)
      exit(json_encode(['code'=>139,'msg'=>'订单用户不存在！','data'=>'']));

    $user_order=M('userorder')->add(['order_user_id'=>$this->post['user'],'order_amount'=>$this->post['amount'],'create_at'=>date("Y-m-d H:i:s",time()),'update_at'=>date("Y-m-d H:i:s",time())]); //订单信息

    if(!$user_order)
        exit(json_encode(['code'=>134,'msg'=>'订单添加失败！','data'=>'']));

    $tranAmt=$this->post['amount']*100;//交易金额 （单位分）
    //获取支付二维码
    $orderInfo="";//订单信息说明
    $url=C('EtonePay.payUrl')."?";
    //签名字符串
    $txnString=C('EtonePay.version')
      ."|".C('EtonePay.transCode')
      ."|".C('EtonePay.merchantId')
      ."|".$user_order
      ."|".C("EtonePay.bussId")
      ."|".$tranAmt
      ."|".$this->post['user']
      ."|".date("YmdHis")
      ."|".C("EtonePay.currencyType")
      ."|".C("EtonePay.merUrl")
      ."|".C("EtonePay.backUrl")
      ."|".strToHex($orderInfo)
      ."|";

      //file_put_contents('txnstring.txt',$txnString);

    $signValue = md5($txnString.C('EtonePay.datakey'));

    //file_put_contents('signValue.txt',$signValue."=|||||=".C('EtonePay.datakey'));

    $url.="version=".C('EtonePay.version');
    $url.="&transCode=".C('EtonePay.transCode');
    $url.="&merchantId=".C('EtonePay.merchantId');
    $url.="&merOrderNum=".$user_order;
    $url.="&bussId=".C('EtonePay.bussId');
    $url.="&tranAmt=".$tranAmt;
    $url.="&sysTraceNum=".$this->post['user'];
    $url.="&tranDateTime=".date("YmdHis");
    $url.="&currencyType=".C('EtonePay.currencyType');
    $url.="&merURL=".C('EtonePay.merUrl');
    $url.="&backURL=".C('EtonePay.backUrl');
    $url.="&orderInfo=".$orderInfo;
    $url.="&userId=";
    $url.="&bankId=888880600002900";
    $url.="&stlmId=";
    $url.="&userIp=";
    $url.="&entryType=1";
    $url.="&activeTime=120";
    $url.="&signValue=".$signValue;

    $curl = curl_init();

    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);

    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 0);

    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //显示获得的数据
    //处理返回信息
    $return=explode('&',$data);
    $result="";
    foreach($return as $key=>$val){
        $ruturn_m=explode('=',$val);
        $result[$ruturn_m[0]]=$ruturn_m[1];
    }

    file_put_contents('request.txt',$data."<br />".$url);
    if($result['respCode']=='0000'){
        echo json_encode(['code'=>200,'msg'=>'订单已经创建！','data'=>['url'=>$result['codeUrl'],'code'=>$result['codeImg']]]);
    }else{
        echo json_encode(['code'=>100,'msg'=>'订单创建失败！','data'=>'']);
    }
  }
  //提现操作
  public function cash(){

  }

  public function success(){ //成功提示页面

    $this->display();

  }

  public function callback(){ //支付信息回调

    $response=I('post.');

    //file_put_contents('3.txt',json_encode($response));

    if($response['respCode']=='0000'){
       //验签
      $txnString=$response['transCode']
          ."|".$response['merchantId']
          ."|".$response['respCode']
          ."|".$response['sysTraceNum']
          ."|".$response['merOrderNum']
          ."|".$response['orderId']
          ."|".$response['bussId']
          ."|".$response['tranAmt']
          ."|".$response['orderAmt']
          ."|".$response['bankFeeAmt']
          ."|".$response['integralAmt']
          ."|".$response['vaAmt']
          ."|".$response['bankAmt']
          ."|".$response['bankId']
          ."|".$response['integralSeq']
          ."|".$response['vaSeq']
          ."|".$response['bankSeq']
          ."|".$response['tranDateTime']
          ."|".$response['payMentTime']
          ."|". $response['settleDate']
          ."|".$response['currencyType']
          ."|".$response['orderInfo']
          ."|".$response['userId'];

      $signValue = md5($txnString.C('EtonePay.datakey'));

      file_put_contents('sign.txt',$signValue);

      if($signValue==$response['signValue']){ //验签
        //处理逻辑 分润
        //判断用户等级
        $user=M('usermanage')
            ->join('usersubordinatecounts usc on usc.user_id=usermanage.id','left')
            ->where(['id'=>$response['sysTraceNum']])
            ->find();

        $rete_money=$response['tranAmt']/100*0.49/100; //费率(元)  员工

        $money=($response['tranAmt']/100-$rete_money);  //到账金额 单位 元

        $money_commission_1st=$rete_money-($response['tranAmt']/100*0.43/100); //费用(元) 分润

        $money_commission_2nd=$money_commission_1st-($response['tranAmt']/100*0.41/100); //老板分润

        $wallet_obj=M('userwallet');

        $wallet_obj->startTrans();

        if($wallet){ //update

          //计算分成金额
          if($user['counts']>=10){ //老板

          $wallet=$wallet_obj->where(['wallet_user_id'=>$response['sysTraceNum']])->find();

          $wallet_amount=bcadd($wallet['wallet_amount'],$money,4);

          $commission=bcadd($money_commission_1st,$money_commission_2nd,4);

          //compair and save 钱包金额修改
          $update=$wallet_obj->where(['user_wallet_id'=>$wallet['user_wallet_id'],'wallet_amount'=>$wallet['wallet_amount']])->save(['wallet_amount'=>bcadd($wallet_amount,$commission,4),'update_at'=>date("Y-m-d H:i:s",time())]);

          //添加日志
          $logs=M('userwalletlog')->add(['log_wallet_id'=>$wallet['user_wallet_id'],'log_amount'=>$response['tranAmt']/100,'service_charge'=>$rete_money,'account'=>'会员收款','account_name'=>'会员收款','log_option'=>3,'other_info'=>$response['orderId'],'log_state'=>1,'create_at'=>date('Y-m-d H:i:s',time())]);

          $logs_commission=M('userwalletlog')->add(['log_wallet_id'=>$wallet['user_wallet_id'],'log_amount'=>$commission,'service_charge'=>0,'account'=>'会员收款','account_name'=>'会员分润','log_option'=>3,'other_info'=>$response['orderId'],'log_state'=>1,'create_at'=>date('Y-m-d H:i:s',time())]);


          }else if($user['u_commission']>=3){ //店长

            $wallet=$wallet_obj->where(['wallet_user_id'=>$response['sysTraceNum']])->find();

            $wallet_amount=bcadd($wallet['wallet_amount'],bcadd($money,$money_commission_1st,4),4);

            $commission=$money_commission_2nd;

            //compair and save 钱包金额修改
            $update=$wallet_obj->where(['user_wallet_id'=>$wallet['user_wallet_id'],'wallet_amount'=>$wallet['wallet_amount']])->save(['wallet_amount'=>$wallet_amount,'update_at'=>date("Y-m-d H:i:s",time())]);

            //上级分润添加
            M('userlevel')->where(['user_id'=>$response['sysTraceNum']])->getField('parent_id');

            //判断是否有上级
            $parent=M('userlevel')->where(['user_id'=>$response['sysTraceNum']])->getField('parent_id');


            //添加日志
            $logs=M('userwalletlog')->add(['log_wallet_id'=>$wallet['user_wallet_id'],'log_amount'=>$response['tranAmt']/100,'service_charge'=>$rete_money,'account'=>'会员收款','account_name'=>'会员收款','log_option'=>3,'other_info'=>$response['orderId'],'log_state'=>1,'create_at'=>date('Y-m-d H:i:s',time())]);
          }else{ //员工

          }

        }else{ //insert

          $update=$wallet_obj->add(['wallet_amount'=>$wallet_amount,'wallet_user_id'=>$response['sysTraceNum'],'wallet_type'=>1,'update_at'=>date("Y-m-d H:i:s",time())]);

          //添加日志
          $logs=M('userwalletlog')->add(['log_wallet_id'=>$update,'log_amount'=>$wallet_amount,'service_charge'=>$rete_money,'account'=>'会员收款','account_name'=>'会员收款','log_option'=>3,'other_info'=>$response['orderId'],'log_state'=>1,'create_at'=>date('Y-m-d H:i:s',time())]);


        }

        //file_put_contents('4.txt',$wallet_obj->getLastSql());
        //file_put_contents('5.txt',M('userwalletlog')->getLastSql());

        if(false!==$update && $logs){
          $wallet_obj->commit();
          file_put_contents('success.txt','成功过了'.date("Y-m-d H:i:s"));
          echo "success";
        }else{
          $wallet_obj->rollback();
          file_put_contents('error.txt','食物会滚'.date("Y-m-d H:i:s"));
          echo "fail";
        }
      }else{
        file_put_contents('error.txt','验签错误'.date("Y-m-d H:i:s"));
        echo "fail";
      }

    }else{
        file_put_contents('error.txt','支付失败'.date("Y-m-d H:i:s"));
        echo "fail";
    }

  }

}
 ?>
