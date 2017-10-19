<?php
namespace Mrpiadmin\Controller;
use Think\Controller;
use Think\Page;

//
class OrderfyController extends ConController { //迅富钱包比例配置

    public function index($status=''){

      $page_size=15;

      $page=I('get.page');

      $login_type=session('login_type');

      $where='';

      if($login_type=='2'){

        //getCurrent User Id
        $current_id = M("usermanage")->where(array("u_account" => session('tringid')))->getField("id");

        //getCurrent User userlevel

        $arr=M("userlevel")->field('user_id,parent_id')->select();

        //getCurrent User SonUser Array
        $dump=$this->getSoon($arr,$current_id);

        $where_in=array('order_user_id'=>array('in',$dump));

      }

      $where=$status=='' ? $where : $where.'order_state='.$status;

      if(IS_POST){

        $u_order=I('post.u_order');

        $u_start=I('post.start');

        $u_end=I('post.end');

      }else{

        $u_order=I('get.u_order');

        $u_start=date('Y-m-d H:i:s',I('get.u_start'));

        $u_end=date('Y-m-d H:i:s',I('get.u_end'));

      }

      if($u_order!='')
        $where=$where!='' ? $where." and user_order_id =".$u_order : $where." user_order_id =".$u_order ;

      if($u_start!='')
        $where=$where!='' ? $where." and o.update_at > '".$u_start."'" : $where." o.update_at > '".$u_start."'" ;

      if($u_end!='')
        $where=$where!='' ? $where." and o.update_at < '".$u_end."'" : $where." o.update_at < '".$u_end."'" ;

      if($login_type=='1'){

        $count=M('userorder o')
            ->where($where)
            ->join('usermanage u on o.order_user_id=u.id','left')
            ->count();

      }elseif($login_type=='2'){

        if(empty($where)){
          $count=M('userorder o')
              ->where($where_in)
              ->join('usermanage u on o.order_user_id=u.id','left')
              ->count();
        }else{
          $count=M('userorder o')
              ->where($where)
              ->where($where_in)
              ->join('usermanage u on o.order_user_id=u.id','left')
              ->count();
        }
      }

      //echo M()->_sql();

      $page=new \Think\Page($count,$page_size);// 实例化分页类 传入总记录数和每页显示的记录数(25)//new Page($count,$page_size);

      $parameters=array('status'=>$status);

      if($u_order!='')
          $parameters['u_order']=$u_order;

      if($u_start!='')
          $parameters['u_start']=strtotime($u_start);

      if($u_end!='')
         $parameters['u_end']=strtotime($u_end);

      $page->parameter = $parameters;

      if($login_type=='1'){

        $orders=M('userorder o')
              ->field('o.*,u.u_nick,u.u_mobile')
              ->where($where)
              ->join('usermanage u on o.order_user_id=u.id','left')
              ->limit($page->firstRow.','.$page->listRows)
              ->order('user_order_id desc')
              ->select();
      }elseif($login_type=='2'){

        if(empty($where)){
          $orders=M('userorder o')
                ->field('o.*,u.u_nick,u.u_mobile')
                ->where($where_in)
                ->join('usermanage u on o.order_user_id=u.id','left')
                ->limit($page->firstRow.','.$page->listRows)
                ->order('user_order_id desc')
                ->select();

        }else{
          $orders=M('userorder o')
                ->field('o.*,u.u_nick,u.u_mobile')
                ->where($where_in)
                ->where($where)
                ->join('usermanage u on o.order_user_id=u.id','left')
                ->limit($page->firstRow.','.$page->listRows)
                ->order('user_order_id desc')
                ->select();
        }
      }

      $show=$page->show();

      $this->assign('orders',$orders);

      $this->assign('show',$show);

      $this->assign('u_order',$u_order);

      $this->assign('u_start',$u_start);

      $this->assign('u_end',$u_end);

      $this->assign('status',$status);

      $this->display('index');

    }

    public function detail(){

      $get=I('get.');

      $data_path=[];

      $user_info=M('userorder oy')
                ->join('userlevel ul on ul.user_id=oy.order_user_id','left')
                ->join('usermanage um on um.id=oy.order_user_id','left')
                ->where(['user_order_id'=>$get['id']])
                ->find();

      $commission=M('orderfy_commission oc')
            ->join('usermanage u on oc.commission_user_id=u.id','left')
            ->join('userorder oy on oy.user_order_id=oc.commission_order','left')
            ->where(['commission_order'=>$user_info['user_order_id']])
            ->select();


      $this->assign('user_info',$user_info);

      $this->assign('commissions',$commission);

      $this->display('detail');
    }
    public function changeState(){ // 更改订单状态 并添加分成信息

    }



    //确认订单
    public function sure($id=0,$type=1){ //默认type1 ajaxreturn 为2的时候 return

      if(!$id){
        $result['status']='0';
        $result['msg']='参数错误';
        $this->ajaxReturn($result);
      }

      //get this order user id
      $orderinfo=M('userorder')->where(array('user_order_id'=>$id))->find();

      if($orderinfo['order_state']=='2')
      {
        $result['status']='1';
        $result['msg']='该订单已被确认';
        $this->ajaxReturn($result);
      }
      //获取当前用户分成层级
      $user=$this->getlevel($orderinfo['order_user_id']);

      //get config money type
      $money_info=M('sysxfmanage')->where(array('proxy'=>session('id')))->find();
      $money=unserialize($money_info['info']);

      //get first member parent to thired
      //Set foreach value
      $money_type_value1=0;

      $count_level=count($user);

      if($money['type']=='1'){
        //get count price for first to thrid
        $money_content=$money['direct_total']+$money['indirect_total']+$money['indirect_3rd_total'];
        if($money_content > 100){
          $result['status']='2';
          $result['msg']='三级分成比例大于1';
          $this->ajaxReturn($result);
        }

      }elseif($money['type']=='2'){
        //get count price for first to thrid
        $money_content=$money['direct_total']+$money['indirect_total']+$money['indirect_3rd_total'];
        if($money_content > $money['member_price']){
          $result['status']='2';
          $result['msg']='三级分成总数大于会员价格';
          $this->ajaxReturn($result);
        }

      }

      $tranDb =M();
      $tranDb->startTrans();

      $value['order_state']="2";
      //更新订单状态 为 已支付
      $updateOrderStatus = $tranDb->table('userorder')->where(array('user_order_id'=>$id))->setField($value);

      foreach ($user as $k => $v) {

        $money_1st=$money['type']=='1' ? ($money['member_price'] * $money['direct_total'])/100 : $money['direct_total'];
        $money_2nd=$money['type']=='1' ? ($money['member_price'] * $money['indirect_total'])/100 : $money['indirect_total'];
        $money_3rd=$money['type']=='1' ? ($money['member_price'] * $money['indirect_3rd_total'])/100 : $money['indirect_3rd_total'];

        if($count_level==3){ //三级
          //
          if($money_type_value1==0)
            //get currentMoney
            $currentMoney=$money_1st;
          elseif($money_type_value1==1)
            //get currentMoney
            $currentMoney=$money_2nd;
          elseif($money_type_value1==2)
            //get currentMoney
            $currentMoney=$money_3rd;
        }else if($count_level==2){ //二级
          //
          if($money_type_value1==0){
            $currentMoney=$money_1st+$money_3rd;
          }elseif($money_type_value1==1){
             $currentMoney=$money_2nd;
          }

        }else{ //一级
          if($money_type_value1==0){
            $currentMoney=$money_1st+$money_2nd+$money_3rd;
          }
        }

        //get  currentUser
        $currentUser=$user[$money_type_value1];

        //get currentUser userwallet
        $user_wallet=M('userwallet')->where(array('wallet_user_id'=>$currentUser))->find();
        /*
            set currentUser wallet money_count
            if currentUser have wallet get currentUser
        */
        if($user_wallet){
          $current_user_balance['wallet_amount']=$user_wallet['wallet_amount']+$currentMoney;
          $current_user_balance['update_at']    =date('Y-m-d H:i:s'.time());
          $updateUserWallet=$tranDb->table('userwallet')->where(array('user_wallet_id'=>$user_wallet['user_wallet_id']))->setField($current_user_balance);
          $current_userWallet_id=$user_wallet['user_wallet_id'];
          if(!$updateUserWallet){
            $result['status']='3';
            $result['msg']="更改用户".$currentUser."钱包余额失败";
            break;
          }
        }else{
          $data=array(
            'wallet_user_id'=>$currentUser,
            'wallet_amount' =>$currentMoney,
            'wallet_type'   =>'1',
            'update_at'     =>date('Y-m-d H:i:s',time())
          );
          $updateUserWallet=$tranDb->table('userwallet')->add($data);
          $current_userWallet_id=$updateUserWallet;
          if(!$updateUserWallet){
            $result['status']='3';
            $result['msg']="新增用户".$currentUser."钱包余额失败";
            break;
          }
        }

        //insert userwalletlog
        $userWallerLog=array(
          'log_wallet_id' =>$current_userWallet_id,//钱包id
          'log_amount'    =>$currentMoney,
          'account'       =>'线下付款',
          'account_name'  =>'管理员确认',
          'log_option'    =>'1',
          'create_at'     =>date('Y-m-d H:i:s',time())
        );
        $insertWallerLog=$tranDb->table('userwalletlog')->add($userWallerLog);
        if(!$insertWallerLog){
          $result['status']='3';
          $result['msg']="新增用户".$currentUser."钱包日志失败";
          break;
        }

        //insert orderfy_commission
        $orderfy_commission=array(
          'commission_order'  =>$id,
          'commission_user_id'=>$currentUser,
          'commission_amount' =>$currentMoney,
          'create_at'     =>date('Y-m-d H:i:s',time())
        );
        $insertOrderfy_comm=$tranDb->table('orderfy_commission')->add($orderfy_commission);
        if(!$insertOrderfy_comm){
          $result['status']='3';
          $result['msg']="新增用户".$currentUser."分成信息失败";
          break;
        }

        $money_type_value1++;
      }

      $u_roleinfo['u_role']='2';

      $u_role=$tranDb->table('usermanage')->where(array('id'=>$orderinfo['order_user_id']))->setField($u_roleinfo);

      if($updateOrderStatus &&  $updateUserWallet && $insertWallerLog && $insertOrderfy_comm && $u_role)
      {
        $tranDb->commit();
        $result['status']='4';
        $result['msg']="分成成功";
        if($type==1){
            $this->ajaxReturn($result);
        }else{
            return $result;
        }
      }else{
        $tranDb->rollback();
        if($type==1){
              $this->ajaxReturn($result);
        }else{
              return $result;
        }
      }

    }


    public function getlevel($uid)
    {

        $parent_user=M('userlevel')->where(array('user_id'=>$uid))->find();

        $parent_str=$parent_user['path_3rd'];

        return explode(",",$parent_str);

    }
}
