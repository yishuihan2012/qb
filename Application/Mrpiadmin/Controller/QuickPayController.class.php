<?php

namespace Mrpiadmin\Controller;

use Think\Controller;

class QuickPayController extends ConController {

    /**
     * 银行卡列表
     * @return [type] [description]
     */
    public function cardList(){
        $where='';
        $post=I('post.');
        if($card_number=$post['card_number']){
            $where['card_number']=array("like","%".$card_number."%");          
            $this->assign('card_number',$card_number);
        }
        if($card_name=$post['card_name']){
            $where['card_name']=array("like","%".$card_name."%"); 
            $this->assign('card_name',$card_name);
        }
        if($card_tel=$post['card_tel']){
            $where['card_tel']=array("like","%".$card_tel."%");
            $this->assign('card_tel',$card_tel);
        }
        if($card_bank=$post['card_bank']){
            $where['card_bank']=array("like","%".$card_bank."%");
            $this->assign('card_bank',$card_bank);
        }
        $table = M('quickpaycard');
        $count = $table -> count();
        $page = new \Think\Page($count,10);
        $show = $page->show();
        $data = $table ->join('usermanage u on u.id=quickpaycard.user_id','left')
                       -> limit($page->firstRow.','.$page->listRows)  
                       ->where($where)
                       -> order('card_id desc') 
                       -> select();
        $this->assign('data',$data);
        $this->assign('page',$show);
        $this->display();
    }
    /**
     * 删除卡号
     */
    public function deleteCard($id){
        
        $re = M('quickpaycard') -> where(array('card_id' => $id)) -> delete();
        if($re){
            echo 1;
        }else{
            echo 0;
        }
    }
    /**
     * 修改卡号
     * @return [type] [description]
     */
    public function updateCard(){
        if($_POST){
            $post = $_POST; 
            $id = $post['id'];
            unset($post['id']);
            $re = M('quickpaycard')->save($post);
            if($re){
                $this->success('修改成功',U('QuickPay/cardList'));
            }else{
                $this->error('您未做修改');
            }
        }else{
            $id = $_GET['id'];
            $data = M('quickpaycard') -> where(array('card_id' => $id)) -> find();
            $card_list=M('bankmanager')->field('id,bank_name,bank_logo')->select();
            $this->assign('card_list',$card_list);
            $this->assign('data',$data);
            $this->display();
        }
    }
    /**
     * 支付订单列表
     * @return [type] [description]
     */
    public function orderList(){
        // 搜索
        $where='';
        $post=I('post.');
        if($qp_id=$post['qp_id']){
            $where['qp_id']=array("like","%".$qp_id."%");          
            $this->assign('qp_id',$qp_id);
        }
        if($u_nick=$post['u_nick']){
            $where['u_nick']=array("like","%".$u_nick."%"); 
            $this->assign('u_nick',$u_nick);
        }
        if($u_starts=$post['u_start']){
            $u_start=strtotime($u_starts);
            $where['qp_create_time']=array('GT',$u_start);
            $this->assign('u_start',$u_starts);
        }

        if($u_ends=$post['u_end']){
            $u_end=strtotime($u_ends);
            $where['qp_create_time']=array('LT',$u_end);
            $this->assign('u_end',$u_ends);
        }
        if(session('login_type')==2){
            $id=session('id');
            $where['_string']='FIND_IN_SET('.$id.', paths)';
        }
        $table = M('quickpayorder');
        $count = $table -> count();
        $page = new \Think\Page($count,10);
        $show = $page->show(); 
        $data = $table ->join('usermanage u on u.id= quickpayorder.qp_uid','left')
                       ->join('userlevel l on l.user_id=u.id')
                       ->join('usercertification c on c.usercertification_user_id=quickpayorder.qp_uid','left')
                       -> limit($page->firstRow.','.$page->listRows)  
                       ->where($where)
                       -> order('qp_create_time desc') 
                       -> select();
        $this->assign('data',$data);
        $this->assign('page',$show);
        $this->display();
    }
    /**
     * 订单详情
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function showOrder($id){
        $data= M('quickpayorder')->where(array('qp_id'=>$id))->find();
        $this->assign('data',$data);
        $this->display();
    }
    /**
     * 删除订单
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function deleteOrder($id){
         if(!$id){
            echo 0;die;
         }
         $re= M('quickpayorder')->where(array('qp_id'=>$id))->delete();
         if($re){
            echo 1;
        }else{
            echo 0;
        }
    }
    /**
     * 分润列表
     * @return [type] [description]
     */
    public function subProfit(){
        $where['log_option']=3;
        $table = M('userwalletlog');
        $count = $table -> count();
        $page = new \Think\Page($count,10);
        $show = $page->show();
        $data = $table ->join('userwallet w on w.user_wallet_id=userwalletlog.log_wallet_id','left')
                        ->join('quickpayorder o on o.qp_id=userwalletlog.other_info','left')
                       -> limit($page->firstRow.','.$page->listRows)  
                       ->where($where)
                       -> order('qp_create_time desc') 
                       -> select();
        foreach ($data as $k => $v) {
            $data[$k]['user']=M('usermanage')->where(array('id'=>$v['wallet_user_id']))->find();
            $data[$k]['feeuser']=M('usermanage')->where(array('id'=>$v['qp_uid']))->find();
        }
        $this->assign('data',$data);
        $this->assign('page',$show);
        $this->display();
    }
    /**
     * 刷卡渠道管理
     * @return [type] [description]
     */
    public function channelManage(){
        $channel=M('channelmanage m')->join('membertype t on t.member_id=m.channel_level')->join('channeltype ct on ct.ct_id=m.channel_type')->where('m.channel_show=1')->select();
        // var_dump($channel);
        $this->assign('data',$channel);
        $this->display();
    }
    /**
     * 添加渠道
     */
    public function addChannel(){
        if($post=I('post.')){
            $post['channel_create_time']=time();
            $search=M('channelmanage')->where(array('channel_type'=>$post['channel_type'],'channel_level'=>$post['channel_level']))->find();
            if($search){
                 $res=M('channelmanage')->where(array('channel_type'=>$post['channel_type'],'channel_level'=>$post['channel_level']))->save($post);
                 echo 1;die;
            }else{
                 $res=M('channelmanage')->add($post);
                 echo 2;die;
            }
        }else{
            $membertype=M('membertype')->select();
            $channeltype=M('channeltype')->where(array('ct_status'=>1))->select();
            $this->assign('channeltype',$channeltype);
            $this->assign('membertype',$membertype);
            $this->display();
        }
    }
    /**
     * 更新渠道信息
     * @param  [type] $type_id  [description]
     * @param  [type] $level_id [description]
     * @return [type]           [description]
     */
    public function channel_update($id){
       
        if($post=I('post.')){
            // var_dump($post);die;
            $post['channel_create_time']=time();
            $data=M('channelmanage')->where(array('channel_id'=>$id))->save($post);
        }else{
            $data=M('channelmanage')->where(array('channel_id'=>$id))->find();
            $membertype=M('membertype')->select();
            $channeltype=M('channeltype')->where(array('ct_status'=>1))->select();
            $this->assign('channeltype',$channeltype);
            $this->assign('membertype',$membertype);
            $this->assign('data',$data);
            // var_dump($data);die;
            $this->display();
        }
    }
    /**
     * [channel_delete description]
     * @param  [type] $type_id  [description]
     * @param  [type] $level_id [description]
     * @return [type]           [description]
     */
    public function channel_delete($id){

         $res=M('channelmanage')->where(array('channel_id'=>$id))->delete();
         echo $res;die;
    }
    public function SourceManage(){
        $data=M('channeltype')->select();
        $this->assign('data',$data);
        $this->display();
    }
    public function addSource(){
        if($post=I('post.')){
            $post['ct_create_time']=time();
            if($file=$_FILES){
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 3145728;
                $upload->rootPath = './Uploads/Uploads/';
                $upload->savePath = '';
                $upload->saveName = array('uniqid','');
                $upload->exts     = array('jpg', 'gif', 'png', 'jpeg');
                $upload->autoSub  = true;
                $upload->subName  ='channeltype';
                // 上传文件 
                $info   =   $upload->uploadOne($file['img']);
                if(!$info) {// 上传错误提示错误信息
                    $this->error($upload->getError());
                }else{// 上传成功 获取上传文件信息
                    $img_path=HOST.$info['savepath'].$info['savename'];
                    $post['ct_img']=$img_path;
                }
            }
            $res=M('channeltype')->add($post);
            if($res){
                $this->success('新增成功');
            }else{
                $this->error('新增失败');
            }
        }else{
            $this->display();
        }
    }
    public function update_channeltype($id){
        if(!$id){
            $this->error('获取参数失败');
        }else{
            $data=M('channeltype')->where(array('ct_id'=>$id))->find();
            $this->assign('data',$data);
            $this->display();
        }
    }
    public function upChannelType($id){
        $post=I('post.');
        $post['ct_create_time']=time();
        $file=$_FILES;
        if(isset($file)){
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 3145728;
            $upload->rootPath = './Uploads/Uploads/';
            $upload->savePath = '';
            $upload->saveName = array('uniqid','');
            $upload->exts     = array('jpg', 'gif', 'png', 'jpeg');
            $upload->autoSub  = true;
            $upload->subName  = 'channeltype';
            // 上传文件 
            $info   =   $upload->uploadOne($file['img']);
            if(!$info) {// 上传错误提示错误信息
                // $this->error($upload->getError());
            }else{// 上传成功 获取上传文件信息
                $img_path=HOST."/Uploads/Uploads/".$info['savepath'].$info['savename'];
                $post['ct_img']=$img_path;
            }
             $res=M('channeltype')->where(array('ct_id'=>$id))->save($post);
            if($res){
                $this->success('修改成功');
            }else{
                $this->error('未做修改');
            }
        }
    }
    public function upChannelStatus($id){
        $status=I('post.data');
        if(!isset($status) || !$id){
            echo 0;die;
        }else{
            if($status==0){
                $status=1;
            }else if($status==1){
                $status=0;
            }
            $res=M('channeltype')->where(array('ct_id'=>$id))->save(array('ct_status'=>$status));
            echo $res;die;
        }
    }
}