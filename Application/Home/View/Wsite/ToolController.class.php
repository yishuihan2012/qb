<?php
namespace Api\Controller;
use Think\Controller;
use Think\Upload;
use Dysmsapi\Request\V20170525;

class ToolController extends Controller{ //工具类接口

  public function upload(){

      $post=I('post.');

      if(!is_login($post['token'],$post['user']))
             exit(json_encode(['code'=>112,'msg'=>'登录信息失效！请重新登录','data'=>'']));

      $upload = new \Think\Upload();// 实例化上传类
      $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
      //设置上传文件大小
      $upload->maxSize = 3292200;
      //设置上传文件类型
      $upload->allowExts = explode(',', 'jpg,gif,png,jpeg');
      //设置附件上传目录
      $upload->savePath = './';
      //设置需要生成缩略图，仅对图像文件有效
      $upload->thumb = true;
      //设置需要生成缩略图的文件后缀
      $upload->thumbPrefix = 'm_,s_';  //生产2张缩略图
      //设置缩略图最大宽度
      $upload->thumbMaxWidth = '400,100';
      //设置缩略图最大高度
      $upload->thumbMaxHeight = '400,100';
      //设置上传文件规则
      $upload->saveRule = 'uniqid';
      //删除原图
      $upload->thumbRemoveOrigin = true;
      // 上传文件
      $info   =   $upload->upload();

    //echo json_encode(['code'=>1234,'msg'=>'测试信息','data'=>$info]);

      if(!$info)// 上传错误提示错误信息
          exit(json_encode(['code'=>109,'msg'=>'文件上传失败！','data'=>'']));

      $path=ltrim($info['headImg']['savepath'],'.');

      echo json_encode(['code'=>200,'msg'=>'文件上传成功！','data'=>['url'=>HOST."Uploads".$path.$info['headImg']['savename']]]);

  }
  //获取手机验证码
  public function get_verification_code(){

    $post=I('post.');
    if(!preg_match("/^1[34578]{1}\d{9}$/",$post['phone']) || !$post['phone'])
        exit(json_encode(['code'=>106,'msg'=>'请输入正确的手机号码！','data'=>'']));

    $code=rand_zifu(1,4);

    $mseeage=json_decode($this->sendSms($post['phone'],$code),true);
    // $mseeage['Code']='OK';
    if($mseeage['Code']!='OK')
        exit(json_encode(['code'=>133,'msg'=>'验证码发送失败！请稍后再试！','data'=>'']));

    //发送短信...
    if($mseeage['Code']=='OK'){
          $return=M('userverification')->add(['phone'=>$post['phone'],'code'=>$code,'update_at'=>time()]); //添加验证码
          if(!$return)
              exit(json_encode(['code'=>134,'msg'=>'验证码发送失败！请稍后再试！','data'=>'']));

          echo json_encode(['code'=>200,'msg'=>'验证码已发送！请查收','data'=>'']);

    }
  }

  public function sendSms($phone,$code) {

      vendor('smsSdk.aliyun-php-sdk-core.Config');
      vendor('smsSdk.Dysmsapi.Request.V20170525.SendSmsRequest');
      vendor('smsSdk.Dysmsapi.Request.V20170525.QuerySendDetailsRequest');

      //此处需要替换成自己的AK信息
      $accessKeyId = C('accessKeyId');
      $accessKeySecret = C('accessKeySecret');
      //短信API产品名
      $product = "Dysmsapi";
      //短信API产品域名
      $domain = "dysmsapi.aliyuncs.com";
      //暂时不支持多Region
      $region = "cn-hangzhou";

      //初始化访问的acsCleint
      $profile = \DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

      \DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);

      $acsClient= new \DefaultAcsClient($profile);

      $request = new \Dysmsapi\Request\V20170525\SendSmsRequest;
      //必填-短信接收号码
      $request->setPhoneNumbers($phone);
      //必填-短信签名
      $title=C('SYSTEM_TITLE');
      $request->setSignName($title);
      //必填-短信模板Code
      $request->setTemplateCode("SMS_92415001");
      //选填-假如模板中存在变量需要替换则为必填(JSON格式)
      $request->setTemplateParam("{\"code\":\"".$code."\"}");

      //发起访问请求
      $acsResponse = $acsClient->getAcsResponse($request);
      return json_encode($acsResponse);
  }

  //验证验证码信息是否正确
 public function check_verification(){

      $post=I('post.');

      $verification=verification_code($post['phone'],$post['code']);
      // if($post['code']!='123456')
      if(!$verification)
          exit(json_encode(['code'=>104,'msg'=>'验证码信息错误！','data'=>'']));

      echo json_encode(['code'=>200,'msg'=>'验证码信息正确','data'=>'']);
  }
//验证是否可以注册
  public function check_users(){ //检测手机格式 以及是否被注册
      $post=I('post.');
      if(!preg_match("/^1[34578]{1}\d{9}$/",$post['phone']))
          exit(json_encode(['code'=>106,'msg'=>'手机格式不正确！请重新输入！','data'=>'']));

      $users=M('usermanage')->where(['u_account'=>$post['phone']])->find();

      if($users)
          exit(json_encode(['code'=>107,'msg'=>'该手机已注册！请登陆！','data'=>'']));

      echo json_encode(['code'=>200,'msg'=>'可以注册！','data'=>'']);
  }

  public function menu(){ //获取菜单信息

    $post=I('post.');

    $menus=M('appmenus')->where(['menu_type'=>$post['menu_type'],'menu_state'=>1])->order('menu_order asc')->select();

    if(!$menus)
        exit(json_encode(['code'=>142,'msg'=>'菜单信息查询失败！','data'=>'']));

    echo json_encode(['code'=>200,'msg'=>'菜单信息获取成功！','data'=>$menus]);

  }

  public function getAD(){ //获取广告信息

      $ad=M('admanage')->where(['a_position'=>1,'is_del'=>0])->order('id asc')->select();

      if(!$ad)
            exit(json_encode(['code'=>143,'msg'=>'广告信息查询失败','data'=>'']));

      foreach($ad as $key=>&$val){
          $val['a_image']=HOST.$val['a_image'];
      }

      echo json_encode(['code'=>200,'msg'=>'菜单信息获取成功！','data'=>$ad]);

  }

  public function getNotice(){ //获取公告信息

    $notices=M('notice')->limit('5')->order('create_time desc')->select();

    foreach($notices as $key=>$val){
      $return[]=['title'=>$val['title'],'url'=>HOST.U('Home/Wsite/notice_detail',['id'=>$val['id']])];
    }

    echo json_encode(['code'=>200,'msg'=>'公告信息获取成功！','data'=>$return]);

  }

  public function check_update(){

    $post=I('post.');
    if($post['type']){
      $versions=M('appversion')->where(['version_state'=>1,'version_type'=>$post['type']])->find();
    }else{
      $versions=M('appversion')->where(['version_state'=>1,'version_type'=>'android'])->find();
    }

    if($versions['version_code']>$post['version_code']){
         exit(json_encode(['code'=>100,'msg'=>'发现新的版本！','data'=>['link'=>$versions['vrsion_link'],'info'=>$versions['version_desc']]]));
      }else if($versions['version_code']<$post['version_code']){
         exit(json_encode(['code'=>300,'msg'=>'正在审核中','data'=>[]]));
      }else if($versions['version_code']==$post['version_code']){
          echo json_encode(['code'=>200,'msg'=>'已经是最新版本！','data'=>'']);
  }
      }

      

  public function get_qrcode_by(){ //获取二维码

    $post=I('post.');

    $users=M('usermanage')->join('usercertification uc on uc.usercertification_user_id=usermanage.id','left')->where(['usermanage.id'=>$post['user']])->find();

    if(!$users)
      exit(json_encode(['code'=>142,'msg'=>'未找到相关用户！','data'=>'']));

    if(!$users['usercertification_name'])
      $users['usercertification_name']="---";

    echo json_encode(['code'=>200,'msg'=>'信息获取成功！','data'=>['qrcode'=>$users['u_ewm'],'name'=>$users['usercertification_name']]]);

  }
  /**
   * 获取会员列表
   * @return [type] [description]
   */
  public function getMemberList(){
    if(!$uid=I('post.user')){
        exit(json_encode(['code'=>100,'msg'=>'请输入用户id!','data'=>'']));
    }
    $list=M('membertype')->select();
    if($list){
          $user=M('usermanage')->where(array('id'=>$uid))->find();
          if($user){
              $now_price=0;
              $u_member_id=$user['u_member_id'];
              foreach ($list as $k => $v) {
                 if($v['member_id']>$u_member_id){
                     $list[$k]['isUp']=1;
                 }else{
                     $list[$k]['isUp']=0;
                     if($v['member_id']==$user['u_member_id']){
                          $now_price=$v['member_up_money'];
                     }
                 }

              }
              foreach ($list as $k => $v) {
                 if($v['member_up_money']-$now_price<0){
                     $list[$k]['up_price']=0;
                 }else{
                     $list[$k]['up_price']=$v['member_up_money']-$now_price;
                 }
              }
          }
        echo json_encode(['code'=>200,'msg'=>'信息获取成功！','data'=>['list'=>$list]]);
    }else{
        echo json_encode(['code'=>100,'msg'=>'信息获取失败！','data'=>[]]);
    }
  }
  /**
   *  验证手机号是否存在
   * @return [type] [description]
   */
  public function checkMobile(){
      $mobile=I('post.mobile');
      if(!$mobile){
          echo json_encode(['code'=>100,'msg'=>'获取手机号失败','data'=>[]]);die;
      }
      $res=M('usermanage')->where(array('u_mobile'=>$mobile))->find();
      if($res){
           echo json_encode(['code'=>200,'msg'=>'信息获取成功！','data'=>['result'=>1]]);
      }else{
           echo json_encode(['code'=>101,'msg'=>'信息获取失败！','data'=>['result'=>0]]);
      }
  } 

}
 ?>
