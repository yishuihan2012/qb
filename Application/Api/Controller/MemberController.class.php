<?php
namespace Api\Controller;
use Think\Controller;

class MemberController extends Controller{
  private $post=[];
  public function __construct(){
      $post=I();

      $this->post=$post;

      if(empty($this->post))
          exit(json_encode(['code'=>101,'msg'=>'非法请求！','data'=>'']));

      //
      if(!is_login($this->post['token'],$this->post['user']))
        exit(json_encode(['code'=>112,'msg'=>'登录失效！请重新登录','data'=>'']));
  }
  public function verification_test(){
      $this->post=I();
      $card=M('usercertification')->where(['usercertification_card'=>$this->post['card'],'usercertification_state'=>1])->find();

      if($card)
        exit(json_encode(['code'=>113,'msg'=>'该身份信息已经绑定其他账号！']));

      $c=M('usercertification')->where(['usercertification_user_id'=>$this->post['user']])->find();

      if($c)
          exit(json_encode(['code'=>143,'msg'=>'该账号已认证！','data'=>'']));

      //查询当前用户实名认证次数
      $checkCount=M('usermanage')->where(['id'=>$this->post['user']])->find();

      if($checkCount['user_check_count'] > C('checkCount'))
            exit(json_encode(['code'=>150,'msg'=>'实名认证次数超过限制','data'=>'']));

      //增加审核次数
      $checkCounts=$checkCount['user_check_count']+1;

      $newinfo=M('usermanage')->where(['id'=>$this->post['user']])->save(['user_check_count'=>$checkCounts]);

      if(false===$newinfo)
        exit(json_encode(['code'=>155,'msg'=>'服务器错误,更新请求次数失败','data'=>'']));

      $host = "http://idcard.market.alicloudapi.com";

      $path = "/lianzhuo/idcard";
      $method = "GET";
      $appcode = C('appcode_message');
      $headers = array();
      array_push($headers, "Authorization:APPCODE " . $appcode);
      $querys = "cardno=".$this->post['card']."&name=".urlencode($this->post['name']);
      $bodys = "";

      $url = $host . $path . "?" . $querys;

      $curl = curl_init();
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($curl, CURLOPT_FAILONERROR, false);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HEADER, false);
      if (1 == strpos("$".$host, "https://"))
      {
          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      }

      $infos=curl_exec($curl);

      curl_close($curl);

      $infos=json_decode($infos,true);


      if(!$infos)
          exit(json_encode(['code'=>114,'msg'=>'验证信息获取失败！','data'=>'']));

      if($infos['resp']['code']==5)
          exit(json_encode(['code'=>115,'msg'=>'身份证信息不匹配！','data'=>'']));

      if($infos['resp']['code']==14)
          exit(json_encode(['code'=>116,'msg'=>'无此身份信息！','data'=>$checkCount]));

      if($infos['resp']['code']==96)
          exit(json_encode(['code'=>117,'msg'=>'交易失败！请稍后重试！','data'=>'']));

      if($infos['resp']['code']===0){ //匹配成功 存储信息

          echo json_encode(['code'=>200,'msg'=>'认证通过！','data'=>'']);
      }
  }
  public function verification(){ //实名认证
    //
    $card=M('usercertification')->where(['usercertification_card'=>$this->post['card'],'usercertification_state'=>1])->find();

    if($card)
      exit(json_encode(['code'=>113,'msg'=>'该身份信息已经绑定其他账号！']));

    $c=M('usercertification')->where(['usercertification_user_id'=>$this->post['user']])->find();

    if($c)
        exit(json_encode(['code'=>143,'msg'=>'该账号已认证！','data'=>'']));

    //查询当前用户实名认证次数
    $checkCount=M('usermanage')->where(['id'=>$this->post['user']])->find();

    if($checkCount['user_check_count'] > C('checkCount'))
          exit(json_encode(['code'=>150,'msg'=>'实名认证次数超过限制','data'=>'']));

    //增加审核次数
    $checkCounts=$checkCount['user_check_count']+1;

    $newinfo=M('usermanage')->where(['id'=>$this->post['user']])->save(['user_check_count'=>$checkCounts]);

    if(false===$newinfo)
      exit(json_encode(['code'=>155,'msg'=>'服务器错误,更新请求次数失败','data'=>'']));

    $host = "http://idcard.market.alicloudapi.com";

    $path = "/lianzhuo/idcard";
    $method = "GET";
    $appcode = C('appcode_message');
    $headers = array();
    array_push($headers, "Authorization:APPCODE " . $appcode);
    $querys = "cardno=".$this->post['card']."&name=".urlencode($this->post['name']);
    $bodys = "";

    $url = $host . $path . "?" . $querys;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_FAILONERROR, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    if (1 == strpos("$".$host, "https://"))
    {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }

    $infos=curl_exec($curl);

    curl_close($curl);

    $infos=json_decode($infos,true);


    if(!$infos)
        exit(json_encode(['code'=>114,'msg'=>'验证信息获取失败！','data'=>'']));

    if($infos['resp']['code']==5)
        exit(json_encode(['code'=>115,'msg'=>'身份证信息不匹配！','data'=>'']));

    if($infos['resp']['code']==14)
        exit(json_encode(['code'=>116,'msg'=>'无此身份信息！','data'=>$checkCount]));

    if($infos['resp']['code']==96)
        exit(json_encode(['code'=>117,'msg'=>'交易失败！请稍后重试！','data'=>'']));

    if($infos['resp']['code']===0){ //匹配成功 存储信息

      $files=json_encode([
        'card_forhead'  =>$this->post['card_forhead'],
        'card_back'     =>$this->post['card_back']
      ]);

       $address=explode('-', $infos['data']['address']);
      $mobile=M('usermanage')->field('u_mobile')->where(array('id'=>$this->post['user']))->find();
        $certinfo=array(
            'usercertification_user_id'=>$this->post['user'],
            'usercertification_card'=>$this->post['card'],
            'usercertification_name'=>$this->post['name'],
            'usercertification_info'=>$files,
            'usercertification_state'=>1,
            'create_at'=>date('Y-m-d H:i:s',time()),
            'return_message'=>json_encode($infos),
            'province'=>$address[0]?$address[0]:'',
            'city'=>$address[1]?$address[1]:'',
            'town'=>$address[2]?$address[2]:'',
            'sex'=>$infos['data']['sex'],
            'birth'=>$infos['data']['birthday'],
            'mobile'=>$mobile['u_mobile'],
            'identity_front'=>$this->post['identity_front']?$this->post['identity_front']:'',
            'identity_back'=>$this->post['identity_back']?$this->post['identity_back']:'',
            'bankCard_front'=>$this->post['bankCard_front']?$this->post['bankCard_front']:'',
            'bankCard_back'=>$this->post['bankCard_back']?$this->post['bankCard_back']:'',
            'people_bankCard'=>$this->post['people_bankCard']?$this->post['people_bankCard']:'',
        );
        $usercertifications=M('usercertification')->add($certinfo); 
      
       M('usermanage')->where(array('id'=>$this->post['user']))->setField(array('is_cert'=>1));
        //修改 父类 下级会员数量（用户分润等级）
        $parent=M('userlevel')->where(['user_id'=>$this->post['user']])->getField('parent_id'); //获取其父类

        $subordinatecounts=M('usersubordinatecounts')->where(['user_id'=>$parent])->find(); //获取父类统计信息

        $counts=M('userlevel')->join('usercertification uc on uc.usercertification_user_id=userlevel.user_id','right')->where(['parent_id'=>$parent])->count(); //统计父类数量 实名信息

        if($subordinatecounts){ //更新父类数量
          M('usersubordinatecounts')->where(['user_id'=>$parent])->setField(['counts'=>$counts]);
        }else{       //添加父类数量
          M('usersubordinatecounts')->add(['counts'=>$counts,'user_id'=>$parent]);
        }

     if(!$usercertifications)
         exit(json_encode(['code'=>118,'msg'=>'认证信息！添加失败！','data'=>'']));

       //给用户实名红包
       // $this->addCretRedPackets($checkCount['id']);
      echo json_encode(['code'=>200,'msg'=>'认证通过！','data'=>'']);
    }

  }
  /**
   * 实名认证红包
   */
  public function addCretRedPackets(){
      $id=$this->post['user'];
      #0检测是否领取过实名红包
      $user_info=M('usermanage m')->join('userwallet w on w.wallet_user_id=m.id')->where(array('id'=>$id))->find();
      $wallet_id=$user_info['user_wallet_id'];
      #2查询用户有没有实名认证
      if($user_info['is_cert']==0){
           exit(json_encode(['code'=>110,'msg'=>'该用户尚未实名认证！','data'=>'']));
      }
      //查询红包的金额
      $configs=M('sysxfmanage')->find();
      if($configs){
           $config=unserialize($configs['info']);
           $amount=$config['cert_redpackets_total'];
          if($user_info['isget_cert_redpackets'] ==0){
              #添加余额
               $update = M('userwallet')->where(['user_wallet_id' => $wallet_id, 'wallet_amount' => $user_info['wallet_amount']])->save(['wallet_amount' => bcadd($user_info['wallet_amount'], $amount, 2), 'update_at' => date("Y-m-d H:i:s", time())]);
              #1添加日志log
              $log_param=array(
                'log_wallet_id'=>$wallet_id,
                'log_option'=>5, //具体操作（1：分佣金，2：提现，3：分润 4.充值 5：实名红包 6：收刷红包）
                'create_at'=>date('Y-m-d H:i:s',time()),
                'log_amount'=>$amount,
                'account'=>'实名红包',
                'account_name'=>'',
                'log_state'=>1,
                'service_charge'=>'',
                'log_describe'=>$user_info['u_mobile'].'实名认证红包',
                'other_info'=>'',
              );
              $wallet_log=M('userwalletlog')->add($log_param);
              #3修改状态
              $result=M('usermanage')->where(array('id'=>$id))->save(array('isget_cert_redpackets'=>1));
              exit(json_encode(['code'=>200,'msg'=>'领取成功','data'=>['amount'=>$amount]]));
          }else{
              exit(json_encode(['code'=>110,'msg'=>'该用户已经领取过红包！','data'=>'']));
          }
      }
  }
  /**
   * 首刷红包
   * @param  [type] $id [description]
   * @return [type]     [description]
   */
  public function firstSwipeRedPackets(){
      $id=$this->post['user'];
       #0检测是否领取过红包
      $user_info=M('usermanage m')->join('userwallet w on w.wallet_user_id=m.id')->where(array('id'=>$id))->find();
      #1查询用户是否刷卡成功过
      $order=M('quickpayorder')->where(array('qp_uid'=>$id))->where('qp_qfsatus ="IN_PROCESS" or qp_qfsatus="SUCCESS" ')->find();
      if(!$order){
          exit(json_encode(['code'=>101,'msg'=>'该用户尚未刷卡！','data'=>'']));
      }
      $wallet_id=$user_info['user_wallet_id'];
      //查询红包的金额
      $configs=M('sysxfmanage')->find();
      if($configs){
           $config=unserialize($configs['info']);
           $amount=$config['firstflush_packet_total'];
          if($user_info['isget_firstflush_redpackets'] ==0){
              #添加余额
               $update = M('userwallet')->where(['user_wallet_id' => $wallet_id, 'wallet_amount' => $user_info['wallet_amount']])->save(['wallet_amount' => bcadd($user_info['wallet_amount'], $amount, 2), 'update_at' => date("Y-m-d H:i:s", time())]);
              #1添加日志log
              $log_param=array(
                'log_wallet_id'=>$wallet_id,
                'log_option'=>6, //具体操作（1：分佣金，2：提现，3：分润 4.充值 5：实名红包 6：收刷红包）
                'create_at'=>date('Y-m-d H:i:s',time()),
                'log_amount'=>$amount,
                'account'=>'首次刷卡红包',
                'account_name'=>'',
                'log_state'=>1,
                'service_charge'=>'',
                'log_describe'=>$user_info['u_mobile'].'首次刷卡红包',
                'other_info'=>'',
              );
              $wallet_log=M('userwalletlog')->add($log_param);
              #3修改状态
              $result=M('usermanage')->where(array('id'=>$id))->save(array('isget_firstflush_redpackets'=>1));
               exit(json_encode(['code'=>200,'msg'=>'领取成功','data'=>['amount'=>$amount]]));
          }else{
              exit(json_encode(['code'=>110,'msg'=>'该用户已经领取过红包！','data'=>'']));
          }
      }
  }
  /**
   * 获取钱包余额
   * @return [type] [description]
   */
  public function getWalletRemain(){
      $id=$this->post['user'];
      $user=M('usermanage u')->join('userwallet w on w.wallet_user_id=u.id')->where(array('u.id'=>$id))->find();
      if($user['wallet_amount']){
           exit(json_encode(['code'=>200,'msg'=>'获取成功','data'=>['amount'=>$user['wallet_amount']]]));
      }else{
          exit(json_encode(['code'=>200,'msg'=>'获取失败','data'=>'']));
      }
  }
  public function password(){ //重制密码相关接口

      if(!$this->post['passwd_old'])
          exit(json_encode(['code'=>119,'msg'=>'请输入愿密码！','data'=>'']));

      if(!$this->post['passwd_new'])
          exit(json_encode(['code'=>120,'msg'=>'请输入新的密码！','data'=>'']));

      if(password_strength($this->post['passwd_new'])<3)
          exit(json_encode(['code'=>110,'msg'=>'密码设置过于简单！请重新设置！','data'=>'']));

      $passwd=M('usermanage')->where(['id'=>$this->post['user']])->getField('u_pass');

      if(md5pass($this->post['passwd_old'])!=$passwd)
          exit(json_encode(['code'=>105,'msg'=>'愿密码错误！请重新填写！','data'=>'']));

      if(md5pass($this->post['passwd_new'])==$passwd)
          exit(json_encode(['code'=>121,'msg'=>'你的设置的密码与之前相同！修改失败！','data'=>'']));


      $passwd=M('usermanage')->where(['id'=>$this->post['user']])->setField(['u_pass'=>md5pass($this->post['passwd_new'])]);

      if(false==$passwd)
          exit(json_encode(['code'=>122,'msg'=>'密码修改失败！','data'=>'']));

      echo json_encode(['code'=>200,'msg'=>'您的密码已经更新！','data'=>'']);
  }

  public function nick_name(){ //修改昵称

      if(!$this->post['nick_name'])
          exit(json_encode(['code'=>123,'msg'=>'请填写您的昵称！','data'=>'']));

       $nick_name=M('usermanage')->where(['id'=>$this->post['user']])->setField(['u_nick'=>$this->post['nick_name']]);

       if(false==$nick_name)
            exit(json_encode(['code'=>124,'msg'=>'昵称更改失败！','data'=>'']));

        echo json_encode(['code'=>200,'msg'=>'昵称更改成功！','data'=>'']);
  }

  public function avata(){ //头像修改昵称

      if(!$this->post['avata'])
          exit(json_encode(['code'=>125,'msg'=>'请选择您的头像','data'=>'']));

       $nick_name=M('usermanage')->where(['id'=>$this->post['user']])->setField(['u_image'=>$this->post['avata']]);

       if(false==$nick_name)
            exit(json_encode(['code'=>126,'msg'=>'头像更改失败！','data'=>'']));

        echo json_encode(['code'=>200,'msg'=>'头像更改成功！','data'=>'']);
  }

  public function passwd_for_pay(){ //设置 修改 支付密码

    if(!$this->post['passwd'])
        exit(json_encode(['code'=>127,'msg'=>'请输入您的支付密码！','data'=>'']));

    if(!$this->post['passwd_old'] && $this->post['method']=='reset')
        exit(json_encode(['code'=>128,'msg'=>'请输入原支付密码！','data'=>'']));

    $passwd=M('usermanage')->where(['id'=>$this->post['user']])->getField('u_pass_for_pay');

    if($this->post['method']=='reset' && $passwd!==md5pass($this->post['passwd_old']))
        exit(json_encode(['code'=>129,'msg'=>'原支付密码不正确！','data'=>'']));

    $passwd=M('usermanage')->where(['id'=>$this->post['user']])->setField(['u_pass_for_pay'=>md5pass($this->post['passwd'])]);

    if(false==$passwd)
        exit(json_encode(['code'=>130,'msg'=>'支付密码修改失败！','data'=>'']));

    echo json_encode(['code'=>200,'msg'=>'支付密码修改成功！','data'=>'']);

  }

  public function suggestion(){ //反馈信息
      if(!$this->post['info'])
          exit(json_encode(['code'=>140,'msg'=>'请填写反馈信息！','data'=>'']));

      $suggest=M('usersuggestion')->add(['suggestion_info'=>$this->post['info'],'user_id'=>$this->post['user'],'create_at'=>date('Y-m-d H:i:s',time())]);

      if (!$suggest)
          exit(json_encode(['code'=>141,'msg'=>'反馈信息发送失败！请重试','data'=>'']));

      echo json_encode(['code'=>200,'msg'=>'您的反馈已经提交！谢谢您的建议','data'=>'']);
  }

  public function bind_account(){ //第三方账户绑定

      if(!$this->post['pay_type_code'] || !$this->post['pay_type_name'])
          exit(json_encode(['code'=>144,'msg'=>'未知的账户类型！','data'=>'']));

      $accounts=M('useraccount')->where(['useraccount_account'=>$this->post['account']])->find();

      if($accounts)
          exit(json_encode(['code'=>145,'msg'=>'该账户已经绑定其他账户！','data'=>'']));

      $account=M('useraccount')->add(['useraccount_user'=>$this->post['user'],'useraccount_code'=>$this->post['pay_type_code'],'useraccount_name'=>$this->post['pay_type_name'],'useraccount_account'=>$this->post['account'],'useraccount_info'=>$this->post['other_info']?$this->post['other_info']:'weipay','create_at'=>date("Y-m-d H:i:s",time())]);

      if(!$account)
          exit(json_encode(['code'=>146,'msg'=>'账户绑定失败！','data'=>'']));

      echo json_encode(['code'=>200,'msg'=>'账户绑定成功！','data'=>'']);

  }
  //提现 生成提现订单
  public function cash(){

       if(!$this->post['amount'] || $this->post['amount']<0 || !is_numeric($this->post['amount']))
          exit(json_encode(['code'=>147,'msg'=>'请输入需要提现的金额！','data'=>'']));

        $userinfo=M('usermanage m')->join('usercertification c on c.usercertification_user_id= m.id','left')
                 
                                    ->where(array('id'=>$this->post['user']))->find();
        if(!$userinfo['usercertification_name']){
            exit(json_encode(['code'=>148,'msg'=>'未实名认证','data'=>'']));
        }
       //获取绑定账户信息
       $user_account=M('useraccount')->where(['useraccount_code'=>'alipay','useraccount_user'=>$this->post['user']])->find();

       if(!$user_account){
          exit(json_encode(['code'=>148,'msg'=>'未绑定账户信息！','data'=>'']));
       }else{
           $param['useraccount_user']=$this->post['user'];
           $param['useraccount_code']='alipay';
           $account=M('useraccount')->where($param)->find();
           if($account){
               if($userinfo['usercertification_name']!=$account['useraccount_info']){
                  exit(json_encode(['code'=>148,'msg'=>'实名账号与支付宝账号不符！','data'=>'']));
               }
            }
       }

       $wallet=M('userwallet')->where(['wallet_user_id'=>$this->post['user']])->find();//账户总余额
       $configs=M('sysxfmanage')->find();
       if($configs){
           $config=unserialize($configs['info']);
           $min_getcash_money=$config['min_getcash_money'];
           $min_rate_cash=$config['min_rate_cash'];
           $max_rate_cash=$config['max_rate_cash'];
           $getcash_rate=$cofig['getcash_rate'];
           if($min_getcash_money!=0 && $min_getcash_money>$this->post['amount']){
                  exit(json_encode(['code'=>149,'msg'=>'最小提现金额:'.$min_getcash_money,'data'=>'']));
           }
        }else{
           $min_rate_cash=2;
           $max_rate_cash=15;
           $getcash_rate=0.15;
        }
       //计算手续费 未满2元 按两元 大于 15  按15

       $service_charge=$this->post['amount']*$getcash_rate/100;

       if($service_charge<$min_rate_cash)
          $service_charge=$min_rate_cash;


       if($service_charge>$max_rate_cash)
          $service_charge=$max_rate_cash;

        if($wallet['wallet_amount']<$min_rate_cash)
           exit(json_encode(['code'=>147,'msg'=>'余额不足，至少大于'.$min_rate_cash.'元才能提现！','data'=>'']));

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

        //添加到提现申请列表 0申请中 1处理陈功 2 处理失败 3 被驳回 4 拒绝提现
        $cash=M('usercash')->add(['cash_user_id'=>$this->post['user'],'create_at'=>date('Y-m-d H:i:s'),'cash_amount'=>$this->post['amount'],'cash_state'=>0,'service_charge'=>$service_charge]);

        if(false!==$update && $cash){

            $wallets->commit();

            echo json_encode(['code'=>200,'msg'=>'提现申请已经提交！等待审核！','data'=>['count'=>$left]]);

        }else{

          $wallets->rollback();

          echo json_encode(['code'=>149,'msg'=>'提现申请失败！','data'=>['count'=>$wallet['wallet_amount']]]);
        }

  }

  //解绑微信支付宝账号
  public function unbundle()
  {

    if(!$this->post['phone'])
      exit(json_encode(['code'=>102,'msg'=>'手机号不能为空,解绑失败','data'=>'']));

    if(!$this->post['code'])
      exit(json_encode(['code'=>103,'msg'=>'验证码不能为空,解绑失败','data'=>'']));

    if(!verification_code($this->post['phone'],$this->post['code']))
        exit(json_encode(['code'=>105,'msg'=>'验证码错误,解绑失败','data'=>'']));

    if(!$this->post['type'])
        exit(json_encode(['code'=>120,'msg'=>'缺少解绑类型,解绑失败','data'=>'']));

    $row=M('useraccount')->where(['useraccount_user'=>$this->post['user'],'useraccount_code'=>$this->post['type']])->find();

    if(!$row)
      exit(json_encode(['code'=>130,'msg'=>'找不到绑定信息,解绑失败','data'=>'']));

    $isdroup=M('useraccount')->where(['useraccount_user'=>$this->post['user'],'useraccount_code'=>$this->post['type']])->delete();

    if(!$isdroup)
        exit(json_encode(['code'=>140,'msg'=>'解绑出现未知账错误,解绑失败','data'=>'']));

    echo json_encode(['code'=>200,'msg'=>'解绑成功','data'=>'解除绑定成功']);

  }
  /**
   * 获取我的等级
   * @return [type] [description]
   */
  public function userLevel(){
      $level=M('usermanage')->join('membertype t on t.member_id=usermanage.u_member_id')->where(['id'=>$this->post['user']])->find();
      if(!$level)
          exit(json_encode(['code'=>140,'msg'=>'未找到用户信息！','data'=>'']));

      echo json_encode(['code'=>200,'msg'=>'找到信息','data'=>['level'=>$level]]);
  }
  /**
   * 获取我的团队信息
   * @return [type] [description]
   */
  public function getMyTeam(){
     $user=$this->post['user']; 
     if($u_member_id=$this->post['member_id']){
       $where['member_id']=$u_member_id;
     }
     if($type=$this->post['type']){
        if($type==2){
            $where['is_cert']=1;
        }
        if($type==3){
            $where['is_cert']=0;
        }
        
     }
     // $where['path_3rd']=array('like','%'.$user.'%');
     $where['_string']='FIND_IN_SET('.$user.', path_3rd)';
     $res=M('userlevel')
            ->join('usermanage m on m.id=userlevel.user_id','left')
            ->join('membertype t on t.member_id=m.u_member_id','left')
            // ->join('usercertification c on c.usercertification_user_id= m.id','left') 
            ->where($where)
            ->select();
    //根据类型分类
    if($res){
         echo json_encode(['code'=>200,'msg'=>'获取成功','data'=>['list'=>$res]]);
    }else{
        echo json_encode(['code'=>100,'msg'=>'未找到下级','data'=>[]]);
    }
  }
  /**
   * 我的团队统计
   * @return [type] [description]
   */
  public function getMyTeamCount(){
     $user=$this->post['user']; 
     // $where['path_3rd']=array('like','%'.$user.'%');
     $where['_string']='FIND_IN_SET('.$user.', path_3rd)';
     $list=M('membertype')->select();
     foreach ($list as $k => $v) {
         $option['u_member_id']=$where['u_member_id']=$v['member_id'];
         $result=M('userlevel')
            ->join('usermanage m on m.id=userlevel.user_id','left')
            ->join('membertype t on t.member_id=m.u_member_id')
            ->where($where)
            ->count();
        $option['parent_id']=$user;
        $res=M('userlevel')
            ->join('usermanage m on m.id=userlevel.user_id','left')
            ->join('membertype t on t.member_id=m.u_member_id')
            ->where($option)
            ->count();
        $member_list[$k]['member_id']=$v['member_id'];
        $member_list[$k]['img']=$v['member_head_img'];
        $member_list[$k]['name']=$v['member_name'];
        $member_list[$k]['value']=$result;
        $member_list[$k]['first']=$res;
        $member_list[$k]['second']=$result-$res;
     }
    foreach ($member_list as $k => $v) {
        $count+=$v['value'];
    }
    if($member_list){
         echo json_encode(['code'=>200,'msg'=>'获取成功','data'=>['list'=>$member_list,'count'=>$count]]);
    }else{
        echo json_encode(['code'=>100,'msg'=>'未找到下级','data'=>[]]);
    }
  }
  /**
   * 获取我的分润 分佣
   * @return [type] [description]
   */
  public function getMyWalletLog(){
      $user=$this->post['user'];
      $type=$this->post['type']; // 1分佣 2提现 3分润4充值
      
      $list['list']=M('userwalletlog')
            ->join('userwallet w on w.user_wallet_id=userwalletlog.log_wallet_id','left')
            ->join('usermanage m on m.id=w.wallet_user_id')
            ->where(array('log_option'=>$type,'wallet_user_id'=>$user))
            ->order('create_at desc')
            ->select();
      $list['count']=M('userwalletlog')
            ->join('userwallet w on w.user_wallet_id=userwalletlog.log_wallet_id','left')
            ->where(array('log_option'=>$type,'wallet_user_id'=>$user))->sum('log_amount');
       if(!$list['count']){
        $list['count']=0;
      }
        //累计分润
       //累计分佣
      $list['profit_count']=M('userwalletlog')
            ->join('userwallet w on w.user_wallet_id=userwalletlog.log_wallet_id','left')
            ->where(array('log_option'=>1,'wallet_user_id'=>$user))->sum('log_amount');
       if(!$list['profit_count']){
        $list['profit_count']=0;
      }
        //累计分润
      $list['commission_count']=M('userwalletlog')
            ->join('userwallet w on w.user_wallet_id=userwalletlog.log_wallet_id','left')
            ->where(array('log_option'=>3,'wallet_user_id'=>$user))->sum('log_amount');
      if(!$list['commission_count']){
        $list['commission_count']=0;
      }
      if($list['count']){
           echo json_encode(['code'=>200,'msg'=>'获取成功','data'=>['list'=>$list]]);
      }else{
           echo json_encode(['code'=>100,'msg'=>'暂无数据','data'=>[]]);
      }
  }
  /**
   * 获取验证次数
   * @return [type] [description]
   */
  public function getCertCount(){
    $user=M('usermanage')->where(array('id'=>$this->post['user']))->find();
    $use=$user['user_check_count'];
    $total=C("checkCount");
    echo json_encode(['code'=>200,'msg'=>'获取成功','data'=>['use'=>$use,'total'=>$total]]);
  }
  /**
   * 获取我的实名认证信息
   * @return [type] [description]
   */
  public function getMyCert(){
     $info=M('usercertification')->where(array('usercertification_user_id'=>$this->post['user']))->find();
     if($info){
        echo json_encode(['code'=>200,'msg'=>'获取成功','data'=>['list'=>$info]]);
     }else{
         echo json_encode(['code'=>200,'msg'=>'暂无绑定实名信息','data'=>[]]);
     }
  }
  /**
   * 获取我的刷卡订单
   * @return [type] [description]
   */
  public function getMyQuickPayOrder(){
      $list=M('quickpayorder')->join('usermanage m on m.id=quickpayorder.qp_uid')->where(array('qp_uid'=>$this->post['user']))->order('qp_create_time desc')->select();
      if($list){
        echo json_encode(['code'=>200,'msg'=>'获取成功','data'=>['list'=>$list]]);
     }else{
         echo json_encode(['code'=>200,'msg'=>'暂无订单','data'=>[]]);
     }
  }
  
}

 ?>
