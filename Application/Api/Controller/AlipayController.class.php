<?php
namespace Api\Controller;
use Think\Controller;

//支付回调接口
class AlipayController extends Controller{
  private $aop;
  private $post;
  public function _initialize(){

    $post=I('post.');

    $this->post=$post;

    vendor('Alipay.AopSdk');

    $this->aop = new \AopClient;
    $this->aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
    $this->aop->appId = C('alipay_app_id');
    $this->aop->rsaPrivateKey = C('rsaPrivateKey') ;
    $this->aop->format = "json";
    $this->aop->charset = "utf-8";
    $this->aop->signType = "RSA2";
    $this->aop->alipayrsaPublicKey = C('alipayrsaPublicKey');

  }

  public function create_order(){
    if(!isset($this->post['token']) || !isset($this->post['user']))
        exit(json_encode(['code'=>112,'msg'=>'登录信息失效！请重新登录','data'=>'']));

    if(!is_login($this->post['token'],$this->post['user']))
        exit(json_encode(['code'=>112,'msg'=>'登录信息失效！请重新登录','data'=>'']));
    $up_level_id=$this->post['up_level_id'];
    if(!$up_level_id){
        exit(json_encode(['code'=>112,'msg'=>'获取参数失败！','data'=>'']));
    }
    //查询代理信息
    $proxy=M('sysxfmanage')->find();

    $proxy_info=unserialize($proxy['info']);

    //添加订单
    $user=M('usermanage')->where(['id'=>$this->post['user']])->find();

    if(!$user)
      exit(json_encode(['code'=>139,'msg'=>'订单用户不存在！','data'=>'']));
    if($user['u_member_id']>$up_level_id || $user['u_member_id']==$up_level_id){
      exit(json_encode(['code'=>110,'msg'=>'只能升级！','data'=>'']));
    }
    //$user_order=M('userorder')->add(['order_user_id'=>$this->post['user'],'order_amount'=>$proxy_info['member_price'],'create_at'=>date("Y-m-d H:i:s",time()),'update_at'=>date("Y-m-d H:i:s",time())]);
    //计算升级需要的金额
    $role=M('membertype')->select();
    foreach ($role as $k => $v) {
        if($v['member_id']==$user['u_member_id']){
            $now_price=$v['member_up_money'];
        }
        if($v['member_id']==$up_level_id){
            $need_total=$v['member_up_money'];
	    $new_level_name=$v['member_name'];
        }
    }
    //升级到的等级减去当前等级=升级需要的金额
    $proxy_info['member_price']=$need_total-$now_price;
    // if($this->post['user']==30){
    //    $proxy_info['member_price']=1;
    // }
    $user_order=M('userorder')->add(['order_user_id'=>$this->post['user'],'up_level_id'=>$up_level_id,'order_amount'=>$proxy_info['member_price'],'create_at'=>date("Y-m-d H:i:s",time()),'update_at'=>date("Y-m-d H:i:s",time())]);

    if(!$user_order)
        exit(json_encode(['code'=>134,'msg'=>'订单添加失败！','data'=>'']));
    $subject=C('SYSTEM_TITLE').'升级'.$new_level_name;
    //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
    $request = new \AlipayTradeAppPayRequest();
    //SDK已经封装掉了公共参数，这里只需要传入业务参数
    $bizcontent =     "{\"body\":\"会员升级\","
                    . "\"subject\": \"".$subject."\","
                    . "\"out_trade_no\": \"".$user_order."\","
                    . "\"timeout_express\": \"30m\","
                    . "\"total_amount\": \"".$proxy_info['member_price']."\","
                    . "\"product_code\":\"QUICK_MSECURITY_PAY\""
                    . "}";
    // $bizcontent =     "{\"body\":\"会员升级\","
    //                     . "\"subject\": \"会员升级支付信息\","
    //                     . "\"out_trade_no\": \"".$user_order."\","
    //                     . "\"timeout_express\": \"30m\","
    //                     . "\"total_amount\": \"0.01\","
    //                     . "\"product_code\":\"QUICK_MSECURITY_PAY\""
    //                     . "}";
    $request->setNotifyUrl(HOST."callback/aliPayCallback.php");
    $request->setBizContent($bizcontent);
    //这里和普通的接口调用不同，使用的是sdkExecute
    $response = $this->aop->sdkExecute($request);
    // var_dump($response);die;
    //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
    echo json_encode(['code'=>'200','msg'=>'支付信息生成成功！','data'=>['url'=>$response]]);//就是orderString 可以直接给客户端请求，无需再做处理。
  }
  //提现
  public function cash(){


    if(!isset($this->post['token']) || !isset($this->post['user']))
        exit(json_encode(['code'=>112,'msg'=>'登录信息失效！请重新登录','data'=>'']));

    if(!is_login($this->post['token'],$this->post['user']))
        exit(json_encode(['code'=>112,'msg'=>'登录信息失效！请重新登录','data'=>'']));

     if(!$this->post['amount'] || $this->post['amount']<0 || !is_numeric($this->post['amount']))
        exit(json_encode(['code'=>147,'msg'=>'请输入需要提现的金额！','data'=>'']));

     //获取绑定账户信息
     $user_account=M('useraccount')->where(['useraccount_code'=>'alipay','useraccount_user'=>$this->post['user']])->find();

     if(!$user_account)
        exit(json_encode(['code'=>148,'msg'=>'未绑定账户信息！','data'=>'']));

     $wallet=M('userwallet')->where(['wallet_user_id'=>$this->post['user']])->find(); //账户总余额

     //计算手续费 未满2元 按两元 大于 15  按15

     $service_charge=$this->post['amount']*0.15/100;

     if($service_charge<2)
        $service_charge=2;


     if($service_charge>15)
        $service_charge=15;


     if( $this->post['amount']+$service_charge > $wallet['wallet_amount']) //提现金额+手续费用大于 总金额 则减少提现金额
          $this->post['amount']=$this->post['amount']-$service_charge;

    if( $this->post['amount']+$service_charge > $wallet['wallet_amount']) //仍然大于提现金额
          exit(json_encode(['code'=>148,'msg'=>'账户余额不足！请重新输入','data'=>'']));

      $left=bcsub($wallet['wallet_amount'],($this->post['amount']+$service_charge),4);

      if($left<0)
          exit(json_encode(['code'=>148,'msg'=>'账户余额不足！请重新输入','data'=>'']));

      $wallets=M('userwallet');

      $wallets->startTrans();

      //重置余额
      $update=$wallets->where(['wallet_user_id'=>$this->post['user'],'wallet_amount'=>$wallet['wallet_amount']])->save(['wallet_amount'=>$left]);

      $account=$user_account['useraccount_account'];

      $account_name="支付宝";

      $state=1;

      $log_option=2;

      if(false!==$update){

          $logs=$this->add_wallet_log($wallet['user_wallet_id'],$this->post['amount'],$account,$account_name,$state,$log_option,$service_charge);

          if(false!==$update && $logs){ //添加成功

            //继续下一步操作 转账
            $request = new \AlipayFundTransToaccountTransferRequest();
            $title=C('SYSTEM_TITLE');
            $request->setBizContent("{"
                . "    \"out_biz_no\":\"".$logs."\","
                . "    \"payee_type\":\"ALIPAY_LOGONID\","
                . "    \"payee_account\":\"".$user_account['useraccount_account']."\","
                . "    \"amount\":\"".$this->post['amount']."\","
                . "    \"payer_show_name\":\"".$title."账户提现\","
                . "    \"payee_real_name\":\"".$user_account['useraccount_info']."\","
                . "    \"remark\":\"".$title."账户提现\","
                . "   }");

            $result= $this->aop->execute($request);

            //file_put_contents("a.txt",$a."++++++++++++++++++++++++".json_encode($result));

            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";

            $resultCode = $result->$responseNode->code;
            if(!empty($resultCode)&&$resultCode == 10000){ //成功

              //更新支付宝订单流水信息
              if(false!==M('userwalletlog')->where(['userwalletlog_id'=>$logs])->setField(['other_info'=>$result->$responseNode->order_id])){
                      $wallets->commit();
                      echo json_encode(['code'=>200,'msg'=>'转账成功！','data'=>['left'=>round($left,2)]]);
              }else{

                $wallets->rollback();

                echo json_encode(['code'=>149,'msg'=>'转账失败！日志添加失败！','data'=>'']);
              }

            } else { //失败 回滚

                $wallets->rollback();

                echo json_encode(['code'=>149,'msg'=>'转账失败！支付宝支付失败！','data'=>'']);

            }

          }else{

              $wallets->rollback();

              echo json_encode(['code'=>149,'msg'=>'转账失败！','data'=>'']);

          }
      }

      //账户扣减金额 -》添加日志-》执行转账-》更改log状态

  }

  //后台用户提现
  public function adminster_cash(){

    $get=I('get.');

    if(!$get['id'])
        $this->error('未知提现信息！');

    $usercashs=M('usercash');
    //获取提现信息
    $cashs=$usercashs->join('userwallet uw on uw.wallet_user_id=usercash.cash_user_id','left')->where(['cash_id'=>$get['id']])->find();
    $user=M('usermanage')->where(['id'=>$cashs['cash_user_id']])->find();
    if(!$cashs)
        $this->error('未知提现信息！');

     //获取绑定账户信息
     $user_account=M('useraccount')->where(['useraccount_code'=>'alipay','useraccount_user'=>$cashs['cash_user_id']])->find();

     if(!$user_account)
        $this->error('请先绑定相关账户！');

      $usercashs->startTrans();

      //添加日志
      $logs=$this->add_wallet_log($cashs['user_wallet_id'],$cashs['cash_amount'],$user_account['useraccount_account'],'支付宝',1,2,$cashs['service_charge']);

      if($logs){
        //继续下一步操作 转账
        $request = new \AlipayFundTransToaccountTransferRequest();
        $show_title=C('SYSTEM_TITLE');
        $title=C('SYSTEM_TITLE').$user['u_mobile'].'提现'.round($cashs['cash_amount'],2).'元';
        $request->setBizContent("{"
                  . "    \"out_biz_no\":\"".$cashs['cash_id']."\","
                  . "    \"payee_type\":\"ALIPAY_LOGONID\","
                  . "    \"payee_account\":\"".$user_account['useraccount_account']."\","
                  . "    \"amount\":\"".round($cashs['cash_amount'],2)."\","
                  . "    \"payer_show_name\":\"".$show_title."\","
                  . "    \"payee_real_name\":\"".$user_account['useraccount_info']."\","
                  . "    \"remark\":\"".$title."\","
                  . "   }");

        $result= $this->aop->execute($request);

        file_put_contents("haha.txt",json_encode($result));
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";

        $resultCode = $result->$responseNode->code;

        if(!empty($resultCode)&&$resultCode == 10000){ //成功

            //更新支付宝订单流水信息
            $userwalletlog=M('userwalletlog')->where(['userwalletlog_id'=>$logs])->setField(['other_info'=>$result->$responseNode->order_id]);

            //更新提现状态
            $cash_state=$usercashs->where(['cash_id'=>$get['id']])->setField(['cash_state'=>1]);

            if(false!=$userwalletlog && false!=$cash_state){

                  $usercashs->commit();

                  $this->success('提现成功！');

            }else{

                $usercashs->rollback();

                $this->error('转账失败！日志添加失败！请重新操作');
            }

      } else { //失败 回滚

            $usercashs->rollback();

            $this->error('转账失败！'.$result->$responseNode->sub_msg.'！请重新操作');

        }
      }else{
          $this->error('转账失败！请重新操作');
      }
      //账户扣减金额 -》添加日志-》执行转账-》更改log状态
  }
  public function callbackTest(){
     

  }
  public function callback(){
    
    $flag = $this->aop->rsaCheckV1($_POST, NULL, "RSA2");

    if(!$flag)
        exit(json_encode(['code'=>135,'msg'=>'签名验证失败！','data'=>'']));

    if($_POST['trade_status']=='WAIT_BUYER_PAY')
        exit(json_encode(['code'=>136,'msg'=>'等待支付...','data'=>'']));

    if($_POST['trade_status']=='TRADE_CLOSED')
        exit(json_encode(['code'=>137,'msg'=>'未付款交易超时!','data'=>'']));

    if($_POST['trade_status']=='TRADE_FINISHED')
        exit(json_encode(['code'=>138,'msg'=>'交易结束!','data'=>'']));
    
    if($_POST['trade_status']=='TRADE_SUCCESS'){ //支付成功 修改订单状态


        $order_update=M('userorder')->where(['user_order_id'=>$_POST['out_trade_no']])->setField(['order_state'=>1,'order_serial'=>$_POST['trade_no'],'order_type'=>'alipay','update_at'=>date('Y-m-d H:i:s',time()),'actual_amount'=>$_POST['receipt_amount']]); //修改订单状态 为1 支付成功 未分成

        if(false===$order_update){

          exit("fail");

        }

        $orders=M('userorder');

        $orders->startTrans();

        //查询订单信息
        $order_info=$orders->where(['user_order_id'=>$_POST['out_trade_no']])->find();

        //修改用户级别——————————————————————————————
        $u_member_id=$order_info['up_level_id'];
        $old_user=M('usermanage')->where(['id'=>$order_info['order_user_id']])->find();
        $old_level=$old_user['u_member_id'];
        $old_level_info=M('membertype')->where(array('member_id'=>$old_level))->find();
        $old_level_name=$old_level_info['member_name'];
        M('usermanage')->where(['id'=>$order_info['order_user_id']])->setField(['u_member_id'=>$u_member_id]);


        $level=M('userlevel')->where(['user_id'=>$order_info['order_user_id']])->getField('path_3rd'); //获取 分成列表（上三级）

        $levels=explode(",",$level);

        $proxy=M('sysxfmanage')->find(); //查询分成规则
        $proxy=$proxy['info'];
        $proxys=unserialize($proxy);
        //$_POST['receipt_amount'] 实际到账金额
        $real_drivd_money=$direct_money=$_POST['receipt_amount']*$proxys['direct_rate']/100;
        // 应需求将升级金额加为整数再计算，如1980按2000算
        $real_drivd_money=ceil($real_drivd_money/100)*100;
        // var_dump($real_drivd_money);
        // var_dump($proxys['direct_rate']);
        $total_derect=0;
        if($proxys['type']==1){ //按百分比分成

          $direct[]=$real_drivd_money*$proxys['direct_total']/100;//上级获取金额

          $total_derect+=$proxys['direct_total'];
          $direct[]=$real_drivd_money*$proxys['indirect_total']/100; //次级上级 获的的金额

          $total_derect+=$proxys['indirect_total'];
          $direct[]=$real_drivd_money*$proxys['indirect_3rd_total']/100; //三级 获的的金额

          $total_derect+=$proxys['indirect_3rd_total'];
          if($total_derect>100){ //大于百分百 回滚退出

              $orders->rollback();

              exit("fail");

          }

        }else{ //按固定额度分成

          $direct[]=$proxys['direct_total'];//上级获取金额

          $total_derect+=$proxys['direct_total'];

          $direct[]=$proxys['indirect_total']; //次级上级 获的的金额

          $total_derect+=$proxys['indirect_total'];

          $direct[]=$proxys['indirect_3rd_total']; //三级 获的的金额

          $total_derect+=$proxys['indirect_3rd_total'];

          if($total_derect>$_POST['receipt_amount']){ //大于实际到账总金额 回滚退出
            // $orders->rollback();
            file_put_contents('a.txt','这是回滚信息 - 大于实际到账'.date("Y-m-d H:i:s"));
            //   exit("fail");
          }

        }

        $account="会员升级";

        $account_name="支付宝支付";
        $user_detail=M('usermanage')->where(['id'=>$order_info['order_user_id']])->find();
        $member_detail=M('membertype')->where(array('member_id'=>$order_info['up_level_id']))->find();
        $log_describe=$user_detail['u_mobile'].'升级为'.$member_detail['member_name'];
        $state=1;
        // var_dump($total_derect);
        // var_dump(($direct));die;
        //判断levels 来生成不同分成信息
        if(count($levels)==3){ //三级

            foreach($levels as $key=>$val){

                $wallet_id=$this->update_wallet($val,$direct[$key],1);  //更新钱包信息

                if(!$wallet_id){

                  $orders->rollback();

                  exit("fail");

                }else{

                  $wall_log=$this->add_wallet_log($wallet_id,$direct[$key],$account,$account_name,$state,1,0,$order_info['user_order_id'],$log_describe); //添加日志

                  $commission_log=$this->add_commission_log($val,$direct[$key],$_POST['out_trade_no']); //分成记录

                  if(!$wall_log || !$commission_log){ //存在失败信息 回滚退出

                       $orders->rollback();

                       exit("fail");
                    }

                }

            }

          }else if(count($levels)==2){ //两级

            $amount=bcadd($direct['0'],$direct['2'],4);

            $wallet_id=$this->update_wallet($levels['0'],$amount,1);  //更新钱包信息

            if(!$wallet_id){

              $orders->rollback();

              exit("fail");

            }else{

              $wall_log=$this->add_wallet_log($wallet_id,$amount,$account,$account_name,$state,1,0,$order_info['user_order_id'],$log_describe); //添加日志

              $commission_log=$this->add_commission_log($levels['0'],$amount,$_POST['out_trade_no']); //分成记录

              if(!$wall_log || !$commission_log){ //存在失败信息 回滚退出

                   $orders->rollback();


                   exit("fail");
                }

            }

            //2级
            $amount=$direct['1'];

            $wallet_id=$this->update_wallet($levels['1'],$amount,1);  //更新钱包信息

            if(!$wallet_id){

              $orders->rollback();

              exit("fail");

            }else{

              $wall_log=$this->add_wallet_log($wallet_id,$amount,$account,$account_name,$state,1,0,$order_info['user_order_id'],$log_describe); //添加日志

              $commission_log=$this->add_commission_log($levels['1'],$amount,$_POST['out_trade_no']); //分成记录

              if(!$wall_log || !$commission_log){ //存在失败信息 回滚退出

                   $orders->rollback();

                   exit("fail");
                }

            }

        }else{ // 一级

          //2级
          $amount=$direct['0']+$direct['1']+$direct['2'];

          $wallet_id=$this->update_wallet($levels['0'],$amount,1);  //更新钱包信息

          if(!$wallet_id){

            $orders->rollback();

            exit("fail");

          }else{

            $wall_log=$this->add_wallet_log($wallet_id,$amount,$account,$account_name,$state,1,0,$order_info['user_order_id'],$log_describe); //添加日志

            $commission_log=$this->add_commission_log($levels['0'],$amount,$_POST['out_trade_no']); //分成记录

            if(!$wall_log || !$commission_log){ //存在失败信息 回滚退出

                 $orders->rollback();

                 exit("fail");

              }

          }

        }

        //更新订单状态 为2 已支付并分成
        $updates=M('userorder')->where(['user_order_id'=>$_POST['out_trade_no']])->setField(['order_state'=>2,'update_at'=>date("Y-m-d H:i:s",time())]);
        
        //记录升级日志
        
        $arr=array(
                'lel_uid'=>$order_info['user_order_id'],
                'lel_old_level'=>$old_level,
                'lel_new_level'=>$u_member_id,
                'lel_old_name'=>$old_level_name,
                'lel_new_name'=>$member_detail['member_name'],
                'lel_money'=>$_POST['receipt_amount'],
                'lel_type'=>1,//0后台升级1自主升级
                'lel_time'=>time(),
        );
        M("level_up_log")->add($arr);
        
        if(false===$updates){

            $orders->rollback();

            exit("fail");

        }else{

          $orders->commit(); //提交信息

          file_put_contents('success.txt','success');

        echo "success";

        }
    }

  }

  public function update_wallet($user_id,$amount,$type){ //更新钱包金额

      $wallet=M('userwallet')->where(['wallet_user_id'=>$user_id,'wallet_type'=>$type])->find();

      if(!$wallet){ //不存在钱包信息 新增

          $wallets=M('userwallet')->add(['wallet_user_id'=>$user_id,'wallet_type'=>$type,'wallet_amount'=>$amount,'update_at'=>date("Y-m-d H:i:s",time())]);

          if($wallets){

            return $wallets;

          }else{

            return false;

          }

      }else{

        $update_amount=bcadd($wallet['wallet_amount'],$amount,4);


        $wallets=M('userwallet')->where(['user_wallet_id'=>$wallet['user_wallet_id']])->setField(['wallet_amount'=>$update_amount]);

        if(false===$wallets){

          return false;

        }else{

          return $wallet['user_wallet_id'];

        }

      }

  }

  public function add_wallet_log($wallet_id,$amount,$account,$account_name,$state,$option=1,$service_charge=0,$other_info='',$log_describe=""){ //添加钱包日志
    file_put_contents('option.txt', $option);
    $wallet_log=M('userwalletlog')->add(['log_wallet_id'=>$wallet_id,'log_option'=>$option,'create_at'=>date('Y-m-d H:i:s',time()),'log_amount'=>$amount,'account'=>$account,'account_name'=>$account_name,'log_state'=>$state,'service_charge'=>$service_charge,'log_describe'=>$log_describe,'other_info'=>$other_info]);

    return $wallet_log;

  }

  public function add_commission_log($user_id,$amount,$order){ //添加分成信息

      $commssion=M('orderfy_commission')->add(['commission_order'=>$order,'commission_des'=>'下级升级分佣','commission_user_id'=>$user_id,'commission_amount'=>$amount,'create_at'=>date('Y-m-d H:i:s',time())]);

      return $commssion;

    }

}
 ?>
