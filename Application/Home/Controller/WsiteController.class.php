<?php

namespace Home\Controller;

use Think\Controller;
use Api\Controller\ToolController;

class WsiteController extends Controller {


   /**
    * 常见问题ok
    */
   public function common_question()
   {
      $questions=M('problems')->order('view desc')->where(array('is_show'=>1))->select();
      $this->assign('questions',$questions);
   	  $this->display();
   }
   /**
    * 会员页面
    */
   public function MemberPage(){
      $this->display();
   }
   /**
    * 交易比率todo
    */
   public function exchange_ratio($id)
   {
      $where['qp_uid']=$id;
      $where['qp_qfsatus']='SUCCESS';
      // 总的统计 
      $data['total_count']=M('quickpayorder')->where($where)->fetchSql()->count();  
      if($price=M('quickpayorder')->where($where)->sum('qp_price')){
          $data['total_money']=$price;
      }else{
          $data['total_money']=0;
      }
      //按日期统计
      $params=$param=$where;
      // $param['qp_create_time']=array('gt',mktime(0,0,0,date('m'),date('d'),date('Y')));
      // $param['qp_create_time']=array('lt',mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1);
      $param['qp_create_time']=array('between',mktime(0,0,0,date('m'),date('d'),date('Y')).','.mktime(0,0,0,date('m'),date('d')+1,date('Y')));
      $data['day_count']=M('quickpayorder')->where($param)->count();
      if($price=M('quickpayorder')->where($param)->sum('qp_price')){
          $data['day_total_money']=$price;
      }else{
          $data['day_total_money']=0;
      }
      // 当月统计
      // $params['qp_create_time']=array('gt',mktime(0,0,0,date('m'),1,date('Y')));
      // $params['qp_create_time']=array('lt',mktime(23,59,59,date('m'),date('t'),date('Y')));
      $params['qp_create_time']=array('between',mktime(0,0,0,date('m'),1,date('Y')).','.mktime(23,59,59,date('m'),date('t'),date('Y')));
      $data['month_count']=M('quickpayorder')->where($params)->count();
      if($price=M('quickpayorder')->where($params)->sum('qp_price')){ 
          $data['month_total_money']=$price; 
      }else{ 
          $data['month_total_money']=0;
      }
      // print_r($where);
      // print_r($param);
      // print_r($params);
      // print_r($data);
      // 
      $data['total_money']=number_format($data['total_money'],2);
      $data['day_total_money']=number_format($data['day_total_money'],2);
      $data['month_total_money']=number_format($data['month_total_money'],2);
      $this->assign('data',$data); 
   		$this->display(); 
   } 


   /**
    * 系统公告详情
    */
   public function notice_detail()
   {
   		$id = I('get.id');
   		empty($id) ? $this->error("请重新操作") : null;

   		//查询公告
   		$notice_obj = M('notice as n');
   		$notice_obj->join(array('LEFT JOIN tringroominfo as t ON n.public_admin = t.id'));
   		$notice_obj->field('n.id, n.title,n.content, t.t_name, n.create_time');
   		$notice_obj->where('n.id = ' . $id);
   		$notice_result = $notice_obj->find();

 		$this->assign('notice_result', $notice_result);
   		$this->display();
   }


   /**
    * 系统公告列表
    */
   public function notice_list()
   {
   		//查询公告
		$notice_obj = M('notice as n');
		$notice_obj->join(array('LEFT JOIN tringroominfo as t ON n.public_admin = t.id'));
		$notice_obj->field('n.id, n.title, left(n.content, 30) as content, t.t_name, n.create_time');
		$notice_obj->limit(0, 20);
		$notice_obj->order('id desc');
		$notice_obj->where(array( 'n.is_del' => 0 ));
		$notice_result = $notice_obj->select();

   		$this->assign('notice_result', $notice_result);
   		$this->display();
   }


   /**
    * 结算费率 ok
    */
   public function settlement()
   {
     $data=M('sysxfmanage')->find();
     $data=unserialize($data['info']);
     //查询渠道费率
     $channel=M('channeltype')->select();
     if($channel){
        foreach ($channel as $k => $v) {
           $rate=M('channelmanage m')->where(array('m.channel_type'=>$v['ct_id']))->join('channeltype ct on ct.ct_id=m.channel_type')->join('membertype t on t.member_id=m.channel_level')->select();
           if($rate){
              $arr[$v['ct_name']]=$rate;
           }
        }
     }
     // var_dump($arr);die;
      $this->assign('arr',$arr);
      $this->assign('data',$data);
      $this->display();
   }



   /**
    * 分享 ok
    */
   public function share()
   {
   		$this->display();
   }


   /**
    * 分享后注册todo
    */
   public function join_us(){

       $get=I('get.');

       if($get['id']){ //如果传的是id
         $mobile=M('usermanage')->where(['id'=>$get['id']])->getField('u_account');
         $get['invite']=$mobile;
       }

       $this->assign('get',$get);

       $this->display();

   }


   /**
    * 问题详情
    */
   public function question_detail()
   {
     $get = I('get.');

     M('problems')->where([ 'problem_id' => $get['id'] ])->setInc('view', 1);
     $problems_detail=M('problems')->where(['problem_id'=>$get['id']])->find();

     $this->assign('problems_detail',$problems_detail);
   	 $this->display();
   }


   /**
    * 提现记录  OK
    */
   public function cash_list()
   {
      $id = I('get.id');

      // $table = M('userwalletlog');

      // $count = M('userwalletlog a') -> where(array('a.log_option' => 2,'b.wallet_user_id' => $id)) ->join ('userwallet b on a.log_wallet_id = b.user_wallet_id','left') -> count();
      $count=M('usercash c')->join('usermanage m on m.id=c.cash_user_id')->where(array('c.cash_user_id'=>$id))->count();
      //$count = $table -> where(array('log_option' => 2)) -> count();

      $page = new \Think\Page($count,10);

      $show = $page->show();

      //$data = $table -> where(array('log_option' => 2)) -> limit($page->firstRow.','.$page->listRows) -> select();
      // $data =  M('userwalletlog a') -> where(array('a.log_option' => 2,'b.wallet_user_id' => $id)) ->join ('userwallet b on a.log_wallet_id = b.user_wallet_id','left') -> select();
       $data=M('usercash c')->join('usermanage m on m.id=c.cash_user_id')->where(array('c.cash_user_id'=>$id))->order('c.create_at desc')->select();
       // var_dump($data);die;
      // for($i = 0;$i<count($data);$i++){
      //   $data[$i]['end_bank'] = substr($data[$i]['account'],-4);
      // }

      $this->assign('page',$show);
      $this->assign('data',$data);
      $this->display();
   }


   /**
    * 贷款通道ok
    */
   public function credit_way()
   {
      //$table = M('credit_way');

      //$count = $table -> count();

      //$page = new \Think\Page($count,10);

      //$show = $page->show();

      //limit($page->firstRow.','.$page->listRows)
      $data = M('credit_way') -> select();

      for($i = 0;$i<count($data);$i++){
        if(strpos($data[$i]['quota'],',') === false){
            $data[$i]['quota'] = $data[$i]['quota'];
        }else{
            $quota = explode(',',$data[$i]['quota']);
            $data[$i]['sma_quota'] = $quota[0];
            $data[$i]['max_quota'] = $quota[1];
            $data[$i]['quota'] = '';
        }
      }

      //$this->assign('page',$show);
      $this->assign('data',$data);
   		$this->display();
   }


   /**
    * 贷款大全ok
    */
   public function credit_list()
   {
      //最热门
      $data_hot = M('credit_way a')->join('credit_of_tags b on a.id = b.cradit_id','left')->where(array('b.tag_id' => 1))->select();
      //放款快
      $data_fas = M('credit_way a')->join('credit_of_tags b on a.id = b.cradit_id','left')->where(array('b.tag_id' => 2))->select();
      //利率低
      $data_low = M('credit_way a')->join('credit_of_tags b on a.id = b.cradit_id','left')->where(array('b.tag_id' => 3))->select();
      //额度大
      $data_big = M('credit_way a')->join('credit_of_tags b on a.id = b.cradit_id','left')->where(array('b.tag_id' => 4))->select();

      $hot = $this->limit($data_hot);
      $fas = $this->limit($data_fas);
      $low = $this->limit($data_low);
      $big = $this->limit($data_big);

      $this->assign('hot',$hot);
      $this->assign('fas',$fas);
      $this->assign('low',$low);
      $this->assign('big',$big);
   		$this->display();
   }


   //额度转换方法
   public function limit($data){

      for($i = 0;$i<count($data);$i++){
        if(strpos($data[$i]['quota'],',') === false){
            $data[$i]['quota'] = $data[$i]['quota'];
        }else{
            $quota = explode(',',$data[$i]['quota']);
            $data[$i]['sma_quota'] = $quota[0];
            $data[$i]['max_quota'] = $quota[1];
            $data[$i]['quota'] = '';
        }
      }
      return $data;
   }




   /**
    * 贷款攻略  OK
    */
   public function credit_strategy()
   {
      $table = M('articlemanager');

      $count = $table -> where(array('article_category' => 1)) -> count();

      $page = new \Think\Page($count,10);

      $show = $page->show();

      $data = $table -> where(array('article_category' => 1)) -> order('create_at desc') -> limit($page->firstRow.','.$page->listRows) -> select();

      $count=$table -> where(array('article_category' => 1)) -> count();

      $pagenum=ceil($count/10);

      $this->assign('count',$count);

      $this->assign('pagenum',$pagenum);

      $this->assign('data',$data);

      $this->assign('page',$show);

      $this->display();
   }
   public function ajaxCreditStraegy(){
      $page=I('post.page');
      $num=10;
      $data= M('articlemanager')-> where(array('article_category' => 1)) -> order('create_at desc') -> limit(($page-1)*$num,$page*$num) -> select();
      $this->ajaxReturn($data);
   }


   /**
    * 客户服务del
    */
   public function custom_service()
   {
      $qq=M('contact_info')->where(['type'=>1])->select(); //1 qq
      $tel=M('contact_info')->where(['type'=>2])->select(); //1 qq

      $this->assign('qq',$qq);
      $this->assign('tel',$tel);
   		$this->display();
   }


   /**
    * 累计分润(个人中心)todo
    */
   public function get_profit()
   {
   		$this->display();
   }

   /**
    * 累计分佣
    */
   public function get_commission()
   {
       $get=I('get.');
       if(!is_login($get['token'],$get['id']))
          exit('登录失效！请重新登录');

       $commissions=M('orderfy_commission')->where(['commission_user_id'=>$get['id']])->select();

       //分佣可提现总金额
       $total_amount=M('userwallet')->where(['wallet_user_id'=>$get['id'],'wallet_type'=>1])->getField('wallet_amount');

       //累计分佣
       $total_commissions=M('orderfy_commission')->where(['commission_user_id'=>$get['id']])->sum('commission_amount');


       $this->assign('commissions',$commissions);
       $this->assign('total_commissions',$total_commissions);
       $this->assign('total_amount',$total_amount);


       $this->display();
   }


   /**
    * 累计分润todo
    */
   public function get_profit_2()
   {
   		$this->display();
   }


   /**
    * 累计收款todo
    */
   public function gathering()
   {
   		$this->display();
   }


   /**
    * 帮助ok
    */
   public function help()
   {
   		$this->display();
   }


   /**
    * 贷款通道ok
    */
   public function internal_card_handling()
   {
      $where['credit'] = array('neq',"");

      $data = M('bankmanager') -> where($where) -> select();

      foreach ($data as $key => &$value) {
        # code...
        $value['credit_info']=unserialize($value['credit_info']);
      }

      $this->assign('data',$data);

   		$this->display();
   }


   /**
    * 养卡秘籍  OK
    */
   public function keep_card()
   {
      $table = M('articlemanager');

      $count = $table -> where(array('article_category' => 2)) -> count();

      $page = new \Think\Page($count,10);

      $show = $page->show();

      $data = $table -> where(array('article_category' => 2)) -> order('create_at desc') -> limit($page->firstRow.','.$page->listRows) -> select();

      $count=$table -> where(array('article_category' => 2)) -> count();

      $pagenum=ceil($count/10);

      $this->assign('count',$count);

      $this->assign('pagenum',$pagenum);

      $this->assign('data',$data);

      $this->assign('page',$show);

   		$this->display();
   }
   public function ajaxKeepCard(){
      $page=I('post.page');
      $num=10;
      $data= M('articlemanager')-> where(array('article_category' => 2)) -> order('create_at desc') -> limit(($page-1)*$num,$page*$num) -> select();
      $this->ajaxReturn($data);
   }

   /**
    * 我的代理 ok
    */
   public function get_my_agent()
   {
      $get=I('get.');
      $this->assign('id',$get['id']);
   		$this->display();
   }


   /**
    * 最新口子  OK
    */
   public function new_card()
   {
      $table = M('articlemanager');

      $count = $table -> where(array('article_category' => 3)) -> count();

      $page = new \Think\Page($count,10);

      $show = $page->show();

      $data = $table -> where(array('article_category' => 3)) -> order('create_at desc') -> limit($page->firstRow.','.$page->listRows) -> select();

      $firstdata = $table -> where(array('article_category' => 3)) -> order('create_at desc') -> limit(0,10) -> select();

      $count=$table -> where(array('article_category' => 3)) -> count();
      $pagenum=ceil($count/10);
      $this->assign('count',$count);
      $this->assign('pagenum',$pagenum);
      $this->assign('firstdata',$firstdata);
      
      $this->assign('data',$data);

      $this->assign('page',$show);

      $this->display();
   }
   public function ajaxGetPageCard(){
      $page=I('post.page');
      $num=10;
      $data= M('articlemanager')-> where(array('article_category' => 3)) -> order('create_at desc') -> limit(($page-1)*$num,$page*$num) -> select();
      $this->ajaxReturn($data);
   }

   /**
    * 攻略详情 ok =>盈利模式
    */
   public function strategy_detail()
   {
      $id = $_GET['id'];
      $data = M('articlemanager') -> where(array('article_id' => $id)) -> find();
      // $data=M('problems')->where(array('problem_id'=>40))->find();
      // print_r($data);die;
      //每到此方法一次 浏览数量加一次
      $str['article_view'] = $data['article_view']+1;


      $re = M('articlemanager') -> where(array('article_id' => $id)) -> save($str);

      $this->assign('data',$data);
   		$this->display();
   }


   /**
    * 意见反馈del
    */
   public function suggestions()
   {
   		$this->display();
   }


   /**
    * 交易账单 todo
    */
   public function trade_bill()
   {
     $where=[];
     $get=I('get.');

     $id=$get['id'];

     $where['qp_uid']=$id;

     // if($get['type'])
     //     $where['log_option']=$get['type'];

     // $count = M('userwalletlog a')->join('userwallet b on a.log_wallet_id = b.user_wallet_id','left')->where($where)->count();
     $count = M('quickpayorder')->where($where)->count();
     $page = new \Think\Page($count,10);

     $show = $page->show();

     // $data =  M('userwalletlog a')->join ('userwallet b on a.log_wallet_id = b.user_wallet_id','left')->where($where)->select();
     $data = M('quickpayorder')->where($where)->order('qp_create_time desc')->select();
     foreach ($data as $k => $v) {
        $data[$k]['qp_paycard']=substr($v['qp_paycard'],-4);
        // $data[$k]['qp_price']=substr($v['qp_price'], 0, strlen($v['qp_price']) - 2); 
     }
     // for($i = 0;$i<count($data);$i++){
     //   if($data[$i]['log_option'] == 2){
     //     $data[$i]['end_bank'] = substr($data[$i]['account'],-4);
     //     $data[$i]['operation'] = '提现';
     //   }elseif($data[$i]['log_option'] == 1){
     //     $data[$i]['operation'] = "分佣金";
     //   }elseif($data[$i]['log_option'] == 3){
     //     $data[$i]['operation'] = "分润";
     //   }else{
     //     $data[$i]['operation'] = "充值";
     //   }
     // }


     $this->assign('page',$show);
     $this->assign('data',$data);
     $this->display();
   }

   /**
    * 交易账单 todo
    */
   public function trade_bill_2()
   {
     $where=[];

      $get=I('get.');

      $id=$get['id'];

      $where['b.wallet_user_id']=$id;

      if($get['type'])
          $where['log_option']=$get['type'];

      $count = M('userwalletlog a')->join('userwallet b on a.log_wallet_id = b.user_wallet_id','left')->where($where)->count();

      $page = new \Think\Page($count,10);

      $show = $page->show();

      $data =  M('userwalletlog a')->join ('userwallet b on a.log_wallet_id = b.user_wallet_id','left')->where($where)->select();

      for($i = 0;$i<count($data);$i++){
        if($data[$i]['log_option'] == 2){
          $data[$i]['end_bank'] = substr($data[$i]['account'],-4);
          $data[$i]['operation'] = '提现';
        }elseif($data[$i]['log_option'] == 1){
          $data[$i]['operation'] = "分佣金";
        }elseif($data[$i]['log_option'] == 3){
          $data[$i]['operation'] = "分润";
        }else{
          $data[$i]['operation'] = "充值";
        }
      }

      $this->assign('page',$show);
      $this->assign('data',$data);
      $this->display();
   }


   /**
    * 用卡攻略 OK
    */
   public function using_card_strategy()
   {
      $table = M('articlemanager');

      $count = $table -> where(array('article_category' => 4)) -> count();

      $page = new \Think\Page($count,10);

      $show = $page->show();

      $data = $table -> where(array('article_category' => 4)) -> order('create_at desc') -> limit($page->firstRow.','.$page->listRows) -> select();

      $count=$table -> where(array('article_category' => 4)) -> count();

      $pagenum=ceil($count/10);

      $this->assign('count',$count);

      $this->assign('pagenum',$pagenum);

      $this->assign('data',$data);

      $this->assign('page',$show);

      $this->display();
   }
   public function ajaxUsingCardStrategy(){
      $page=I('post.page');
      $num=10;
      $data= M('articlemanager')-> where(array('article_category' => 4)) -> order('create_at desc') -> limit(($page-1)*$num,$page*$num) -> select();
      $this->ajaxReturn($data);
   }


   /**
    * 内部办卡 todo
    */
   public function card_handling_home()
   {
   		$this->display();
   }


   /**
    * 信用卡激活 OK
    */

   public function credit_card_activate()
   {
      $where['bank_activation'] = array('neq',"");
      $data = M('bankmanager') -> where($where) -> select();
      $this->assign('data',$data);
   		$this->display();
   }


   /**
    * 内部提额  OK
    */
   public function promote_limit()
   {
      $where['bank_ascension'] = array('neq',"");
      $article = M('articlemanager') -> where(array('article_category' => 5)) -> order('article_id desc') -> limit(10) -> select();
      $data = M('bankmanager') -> where($where) -> select();
      $this->assign('data',$data);
      $this->assign('article',$article);
   		$this->display();
   }


   /**
    * 内部办卡活动中心
    */
   public function active_center()
   {
      $data_long = M('activity') -> where(array('activity_type' => 1,'activity_recommend' => 0,'activity_status' => 1)) -> select();
      $data_high = M('activity') -> where(array('activity_type' => 1,'activity_recommend' => 1,'activity_status' => 1)) -> select();

      $this->assign('long',$data_long);
      $this->assign('high',$data_high);
   		$this->display();
   }


   /**
    * 内部贷款活动中心
    */
   public function active_center_2()
   {
      $data_long = M('activity') -> where(array('activity_type' => 2,'activity_recommend' => 0,'activity_status' => 1)) -> select();
      $data_high = M('activity') -> where(array('activity_type' => 2,'activity_recommend' => 1,'activity_status' => 1)) -> select();

      $this->assign('long',$data_long);
      $this->assign('high',$data_high);
   		$this->display();
   }


   /**
    * 申卡进度  OK
    */
   public function open_card_schedule()
   {
      $where['bank_progress'] = array('neq',"");
      $data = M('bankmanager') -> where($where) -> select();
      $this->assign('data',$data);
   		$this->display();
   }


   /**
    * 会员返佣 ok
    */
   public function rate_back()
   {
   		$this->display();
   }


   /**
    * 注册协议  暂未做后台配置
    */
   public function register_agreement()
   {
   		$this->display();
   }


   /**
    * 帮助好友开通账户
    */
   public function help_open(){

     if(IS_POST){
       $post=I('post.');
       if(!verification_code($post['phone'],$post['checkCode']))
            $this->error('验证码信息错误！请重新填写');

        //通过验证确认开通
        if(is_registered($post['phone'])) //判断是否可以继续注册
            $this->error('该账号已经注册！请登陆');

        $u_code=rand_zifu(2,8); //生成邀请码

        $user=M('usermanage');

        $user->startTrans();

        //密码为手机号 后 6 位

        $login_passwd='123456';

        $register=$user
              ->add(['u_account'=>$post['phone'],'u_mobile'=>$post['phone'],'u_pass'=>md5pass($login_passwd),'u_nick'=>$post['phone'],'u_code'=>$u_code,'u_times'=>date('Y-m-d H:i:s',time()),'update_at'=>date('Y-m-d H:i:s',time())]);

        //生成二维码
        $qrcodes=A('Api/Login');

        $qrcode=$qrcodes->getQrcode(HOST.U('Home/Wsite/join_us',['id'=>$register]));

        //生成商户号码
        $no=date("ymd").str_pad($register,12,0,STR_PAD_LEFT);

        $user->where(['id'=>$register])->setField(['user_no'=>$no,'u_ewm'=>$qrcode]); //更新商户号

        //获取当前用户信息
        $users=M('userlevel')->where(['user_id'=>$post['id']])->find();

        //取出当前用户的上三级信息
        $path_3rd=array_reverse(explode(',',$users['paths'].",".$users['id']));

        $new_3rd=array_chunk($path_3rd,3);

        $new_3rd_str=implode(',',current($new_3rd));

        $paths=$users['paths'].",".$users['id'];
        //确定用户层级关系
        $levels=M('userlevel')->add(['user_id'=>$register,'parent_id'=>$users['id'],'paths'=>trim($paths,','),'path_3rd'=>trim($new_3rd_str,',')]);

        if($levels && $register){
            M()->commit();
        }else{
            M()->rollback();
        }

        $this->success('账号已经开通！请登陆！');
     }else{

       $get=I('get.');

       $this->assign('id',$get['id']);

       $this->display();

     }

   }


   /**
    * 分享二维码 ok
    */
   public function share_code3($id)
   {
   		//获取该登录用户的实名信息
   		// $id = I('get.id');

      if(!$id)
          exit($this->display());

   		$usermanage_result = M('usermanage')->field('u_ewm')->where(['id'=>$id])->find();
      //认证信息
   		$usercertification_result = M('usercertification')
                                ->field('left(usercertification_name, 1) as usercertification_name')
                                ->where(['usercertification_user_id'=>$id])->find();
      // var_dump($usermanage_result);die;
   		$this->assign('usermanage_result', $usermanage_result);
   		$this->assign('usercertification_result', $usercertification_result);
   		$this->display();
   }


   /**
    * 分享二维码 ok
    */
   public function share_link()
   {
   		$this->display();
   }


   /**
    * 分享素材 ok
    */
   public function share_material(){

   		$material_obj = M('material');

   		$material_obj->field('id, title, content, create_time, download_num');

   		$material_obj->where('is_del = 0');

   		$material_obj->order('id desc');

   		$material_obj->limit(0, 20);

   		$material_result = $material_obj->select();

   		$material_img_obj = M('material_img');

   		foreach ($material_result as $key => $val){

   			$material_result[$key]['img'] = $material_img_obj->where(array( 'material_id' => $val['id']))->select();

   		}

   		$this->assign('material_result', $material_result);

   		$this->display();
   }
   public function get_download(){
      $android=M('appversion')->where('version_state =1 and version_type= "android"')->find();
      $ios=M('appversion')->where('version_state =1 and version_type= "ios"')->find();
      $this->assign('android',$android);
      $this->assign('ios',$ios);
      $this->display('m_fuying');
   }
   public function get_download_pc(){
      $this->display('fuying');
   }
   public function services(){
      $this->display('services');
   }
   public function pay_success(){ //支付成功页面
     $this->display('pay_success');
   }
   public function qrcode(){ //支付二维码

     $post=I('post.');
     $this->assign('code',$post['code']);
     $this->display('qrcode');
   }
   /**
    * 我的盈利
    * @return [type] [1分佣 3分润]
    */
   public function my_profit($id,$type=1){
      if($id){
        $user=M('usermanage')->where(array('id'=>$id))->find();
        $url=HOST.'/index.php?s=/Api/Member/getMyWalletLog/token/'.$user['user_token'].'/user/'.$user['id'].'/type/'.$type;
        // var_dump($url);die;
        $res=file_get_contents($url);
        // var_dump($res);
        $res=json_decode($res,true);
        if($res['code']==200){
          $this->assign('data',$res['data']['list']);
        }
        $this->assign('id',$id);
        $this->assign('type',$type);
        $this->display();
      }else{
        exit('获取参数失败!');
      }
   }
   /**
    * 我的团队
    * @return [type] [description]
    */
   public function my_team($id){
        if($id){
            $user=M('usermanage')->where(array('id'=>$id))->find();
            $url=HOST.'/index.php?s=/Api/Member/getMyTeamCount/token/'.$user['user_token'].'/user/'.$user['id'];
            // var_dump($url);die;
            $res=file_get_contents($url);
            // var_dump($res);
            $res=json_decode($res,true);
            // print_r($res);die;
            if($res['code']==200){
              $this->assign('data',$res['data']);
              
            }
            $this->assign('id',$id);
            $this->display();
        }else{
          exit('获取参数失败!');
        }
   }
   /**
    * 团队详情
    * @return [type] [description]
    */
   public function team_group($id,$member_id,$type=1){
      if($id && $member_id ){
            $user=M('usermanage')->where(array('id'=>$id))->find();
            $url=HOST.'/index.php?s=/Api/Member/getMyTeam/token/'.$user['user_token'].'/user/'.$user['id'].'/member_id/'.$member_id.'/type/'.$type;
            // var_dump($url);die;
            $res=file_get_contents($url);
            // var_dump($res);
            $res=json_decode($res,true);
            // print_r($res);die;
            if($res['code']==200){
              $this->assign('data',$res['data']);
              
            }
            $this->assign('id',$id);
            $this->assign('member_id',$member_id);
            $this->assign('type',$type);
            $this->display();
        }else{
          exit('获取参数失败!');
        }
   }
   /**
    * 关于我们
    * @return [type] [description]
    */
   public function about_us($version="v1.0.0"){
      $this->assign('version',$version);
      $about_us=M('about_us')->where(array('is_show'=>1))->find();
      $about_us=$about_us['about_content'];
      $this->assign('about_us',$about_us);
      $this->display();
   }
}
