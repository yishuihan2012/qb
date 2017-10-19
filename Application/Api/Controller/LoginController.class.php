<?php
namespace Api\Controller;
use Think\Controller;

class LoginController extends Controller{

  protected $post=[];
  public function __construct(){
    header("Content type:text/html;charset=utf-8");
    if(!IS_POST)
         exit(json_encode(['code'=>101,'msg'=>'非法请求','data'=>'']));

    $post=I('post.');
    $this->post=$post;

  }
  /**
    *  用户登录
    */
  public function Login(){
      if(empty($this->post['login_name']) || empty($this->post['login_passwd']))
          exit(json_encode(['code'=>102,'msg'=>'用户名或密码必须填写！','data'=>'']));

      // if(empty($this->post['verification']) || '123123' != $this->post['verification'])
      //     exit(json_encode(['code'=>103,'msg'=>'验证码信息错误！','data'=>'']));

      $user=M('usermanage um')
            ->join('usercertification uc on uc.usercertification_user_id = um.id','left')
            ->join('membertype t on t.member_id=um.u_member_id','left')
            ->where(['u_account'=>$this->post['login_name']])
            ->find();

      if(empty($user))
          exit(json_encode(['code'=>104,'msg'=>'不存在的用户！','data'=>'']));

      if($user['status']==0){
        exit(json_encode(['code'=>106,'msg'=>'您的张号已被管理员禁用，请联系客服','data'=>'']));
      }

      if($user['u_pass']!==md5pass($this->post['login_passwd']))
          exit(json_encode(['code'=>105,'msg'=>'登录密码错误！','data'=>'']));
      $configs=M('sysxfmanage')->find();
       if($configs){
           $config=unserialize($config['info']);
           $is_use_device=$config['is_use_device'];
        }else{
          $is_use_device=0;
        }
      if($is_use_device){
        if(isset($this->post['device_number'])){
              if($user['device_number'] && $user['device_number']!=$this->post['device_number'] ){
                     exit(json_encode(['code'=>107,'msg'=>'当前设备不是您常用的登录设备，为了您的张号安全，已禁止登录，请联系客服。','data'=>'']));
              }
              if(!$user['device_number']){
                    $res=M('usermanage')->where(array('id'=>$user['id']))->save(array('device_number'=>$this->post['device_number']));
              }
        }
      }
      //余额
      $left_money=M('userwallet')->where(['wallet_user_id'=>$user['id'],'wallet_type'=>1])->sum('wallet_amount'); //余额信息

      //累计分润信息
      //$profit=M('userwallet')->where(['wallet_user_id'=>$user['id'],'wallet_type'=>2])->sum('wallet_amount');

      //累计返佣
      $commissions=M('orderfy_commission')->where(['commission_user_id'=>$user['id']])->sum('commission_amount');

      //获取我的上级信息
      $parents=M('userlevel ul')->join('usermanage um on um.id=ul.parent_id')->where(['user_id'=>$user['id']])->find();

      //获取上上户第三返账户绑定信息
      $account=M('useraccount')->where(['useraccount_user'=>$user['id']])->select();
      // $account_3rd="";
      // foreach($account as $key=>$val){
      //     $account_3rd[]['weipay']=
      //
      // }
      $accounts=['weipay'=>['state'=>0,'account'=>'','info'=>''],'alipay'=>['state'=>0,'account'=>'','info'=>'']];
      if($account){
        foreach($account as $key=>$val){
          if($val['useraccount_code']){
              $accounts[$val['useraccount_code']]['state']=1;
              $accounts[$val['useraccount_code']]['account']=$val['useraccount_account'];
              $accounts[$val['useraccount_code']]['info']=$val['useraccount_info'];
          }
        }
      }

      $token=rand_zifu(1,16);

      //更新token信息以及最后登录时间
      M('usermanage')->where(['id'=>$user['id']])->setField(['user_token'=>$token,'update_at'=>date("Y-m-d H:i:s",time())]);

      $data=[
        'nick_name'             =>  empty($user['usercertification_name'])?$user['u_nick']:$user['usercertification_name'],
        'avata'                 =>  $user['u_image']?$user['u_image']:"http://xijia.oss-cn-shanghai.aliyuncs.com/images/appavatar/uc_defaultavatar%402x.png",
        'phone'                 =>  $user['u_mobile'],
        'certification_state'   =>empty($user['usercertification_state'])?'0':'1', //认证状态
        'left_money'   => sprintf("%.2f", $left_money), //余额
        'commissions'  =>sprintf("%.2f",$commissions), //返佣
        'profit'        =>'0.00',//round($profit,2),//  分润
        'parent_id'     =>$parents['id'], //邀请人信息
        'parent_tel'    =>$parents['u_mobile'], //邀请人手机信息
        'role'          =>$user['member_name'],  //角色名称
        'role_id'       =>$user['u_member_id'], //角色
        'user_no'       =>$user['user_no'],  //商户
        'account_info'  =>$accounts,
        'token'         =>$token,
        'user_id'       =>$user['id']
      ];

      echo json_encode(['code'=>200,'msg'=>'数据获取成功！','data'=>$data]);

  }
/**
  *  用户注册
  */
  public function Registers(){

      if(empty($this->post['login_name']) || empty($this->post['login_passwd']))
          exit(json_encode(['code'=>102,'msg'=>'用户名或密码必须填写！','data'=>'']));

      $users=M('usermanage')->where(['u_account'=>$this->post['login_name']])->find();

      if($users)
          exit(json_encode(['code'=>107,'msg'=>'该手机已注册！请登陆！','data'=>'']));

      // if(password_strength($this->post['login_passwd'])<3)
      //     exit(json_encode(['code'=>110,'msg'=>'密码设置过于简单！为了你的账户安全，请重写设置！','data'=>'']));

      $verification=verification_code($this->post['login_name'],$this->post['verification']);//验证验证码信息

      // if(empty($this->post['verification']) || !$verification)
      //     exit(json_encode(['code'=>103,'msg'=>'验证码信息错误！','data'=>'']));

      if(!preg_match("/^1[34578]{1}\d{9}$/",$this->post['login_name']))
          exit(json_encode(['code'=>106,'msg'=>'手机格式不正确！请重新输入！','data'=>'']));

       $configs=M('sysxfmanage')->find();
       $config=unserialize($configs['info']);

      // if(!$this->post['invite']){
          
      //      $default_ceil_mobile=$config['default_ceil_mobile'];
      //     $this->post['invite']=$default_ceil_mobile;
      // }
      
      if($this->post['invite']){   
          if(!preg_match("/^1[34578]{1}\d{9}$/",$this->post['invite'])){
            exit(json_encode(['code'=>106,'msg'=>'邀请人手机格式不正确！请重新输入！','data'=>'']));
          }
          $users=M('usermanage um')->where(['u_account'=>$this->post['invite']])->join('userlevel ul on ul.user_id=um.id','left')->find();
          //判断邀请码是否存在
          if(!$users){
            exit(json_encode(['code'=>111,'msg'=>'邀请人信息不存在！请重新填写！','data'=>'']));
          }
           
       }else{
          if($config['is_have_recommend']){
             exit(json_encode(['code'=>111,'msg'=>'请填写邀请人信息','data'=>'']));
          }
       }
       $u_code=rand_zifu(2,8);

      //生成二维码

      $user=M('usermanage');

      $user->startTrans();      
      #用户注册写入数据库
      $base_level=M('membertype')->where(array('member_sort'=>1))->find();//注意！默认sort为1的数最低级的。
      $register=$user
            ->add(['u_account'=>$this->post['login_name'],'u_member_id'=>$base_level['member_id'],'u_mobile'=>$this->post['login_name'],'u_pass'=>md5pass($this->post['login_passwd']),'u_nick'=>$this->post['login_name'],'u_code'=>$u_code,'u_times'=>date('Y-m-d H:i:s',time()),'update_at'=>date('Y-m-d H:i:s',time())]);

      //生成二维码
      $qrcode=$this->getQrcode(HOST.U('Home/Wsite/join_us',['invite'=>$this->post['login_name']]));

      //生成商户号码
      $no=date("ymd").str_pad($register,12,0,STR_PAD_LEFT);

      $user->where(['id'=>$register])->setField(['user_no'=>$no,'u_ewm'=>$qrcode]); //更新商户号
       // 添加钱包
      $wallets=M('userwallet')->add(['wallet_user_id'=>$register,'wallet_type'=>1,'update_at'=>date("Y-m-d H:i:s",time())]);

      if($this->post['invite']){   
        //取出当前用户的上三级信息
        $path_3rd=array_reverse(explode(',',$users['paths'].",".$users['id']));

        $new_3rd=array_chunk($path_3rd,3);

        $new_3rd_str=implode(',',current($new_3rd));

        $paths=$users['paths'].",".$users['id'];
        //确定用户层级关系
        $levels=M('userlevel')->add(['user_id'=>$register,'parent_id'=>$users['id'],'paths'=>trim($paths,','),'path_3rd'=>trim($new_3rd_str,',')]);
      }else{
        $levels=M('userlevel')->add(['user_id'=>$register,'parent_id'=>'','paths'=>'','path_3rd'=>'']);
      }

     
      if($levels && $register){
          M()->commit();
      }else{
          M()->rollback();
      }

      if(!$register || !$levels)
          exit(json_encode(['code'=>109,'msg'=>'注册失败！请重新输入','data'=>'']));

       echo json_encode(['code'=>200,'msg'=>'注册成功！','data'=>'']);

  }

  public function forgetPasswd(){ //忘记密码

    if(!$this->post['phone'])
        exit(json_encode(['code'=>131,'msg'=>'请输入手机信息！','data'=>'']));

    if(!preg_match("/^1[34578]{1}\d{9}$/",$this->post['phone']))
        exit(json_encode(['code'=>106,'msg'=>'手机格式不正确！请重新输入！','data'=>'']));

    $verification=verification_code($this->post['phone'],$this->post['code']);//验证验证码信息

    if(!$this->post['code'] || !$verification)
        exit(json_encode(['code'=>103,'msg'=>'验证码信息错误！','data'=>'']));

    if(!$this->post['passwd'])
        exit(json_encode(['code'=>120,'msg'=>'请输入新的密码！','data'=>'']));

    if(password_strength($this->post['passwd'])<3)
        exit(json_encode(['code'=>110,'msg'=>'密码设置过于简单！为了你的账户安全，请重写设置！','data'=>'']));

    $passwd=M('usermanage')->where(['u_account'=>$this->post['phone']])->setField(['u_pass'=>md5pass($this->post['passwd'])]);

    if(false==$passwd)
        exit(json_encode(['code'=>122,'msg'=>'密码修改失败！','data'=>'']));

    echo json_encode(['code'=>200,'msg'=>'密码修改成功！','data'=>'']);
  }

  private function getQrcode($data){  //生成二维码 info 二维码内容

            vendor("phpqrcode.phpqrcode");

            $lv = "L";//容错级别L,M,Q,H
            $size = 10;//大小1~10
            $path = "./Uploads/".date("Y-m-d",time())."/";//图片保存地址
            if(!file_exists($path)){
                mkdir($path);
            }
            $filename = "yt" . time() . $size .$user_id. ".png";//图片名称

            \QRcode::png($data, $path.$filename, $lv, $size);

            $logo=LOGO;

            $QR=$path.$filename;

            $QR = imagecreatefromstring(file_get_contents($QR));

            $logo = imagecreatefromstring(file_get_contents($logo));

            $QR_width = imagesx($QR);//二维码图片宽度

            $QR_height = imagesy($QR);//二维码图片高度

            $logo_width = imagesx($logo);//logo图片宽度

            $logo_height = imagesy($logo);//logo图片高度

            $logo_qr_width = $QR_width / 5;

            $scale = $logo_width/$logo_qr_width;

            $logo_qr_height = $logo_height/$scale;

            $from_width = ($QR_width - $logo_qr_width) / 2;

            //重新组合图片并调整大小
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);

            $QR_img=imagepng($QR,$path.$filename);


        $QR_img=HOST.substr($path.$filename,1);

        return $QR_img;

      }
}
 ?>
