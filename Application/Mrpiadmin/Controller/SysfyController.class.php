<?php
namespace Mrpiadmin\Controller;
use Think\Controller;
class SysfyController extends ConController { //迅富钱包比例配置
    public function index(){
      if(IS_POST){ //编辑操作
        $post=I('post.');
        $data=array(
          'proxy' =>session('id'),//当前登录ID
          'info'  =>serialize($post) //保存配置信息
        );

        $DB=M('sysxfmanage');
        $configs=$DB->where('sysxf_id=1')->find();
        if($configs){
          $DB->where('sysxf_id=1')->save($data);
        }else{
          $DB->add($data);
        }
        echo 1;
      }else{
        $DB=M('sysxfmanage');
        if(!session('?id')){//id信息不存在的话 退出重新登录
            session("tringid",null);
            $this->redirect('index/login');

        }
        $sysconf=$DB->find();//根据ID找到对应的 配置信息
        //

        $role_list=M('userrolemanager')->where(['userrolemanager_type'=>2])->select(); //分润相关等级

        $this->assign('role_list',$role_list);

        if($sysconf){

            $sysconf['info']=unserialize($sysconf['info']);

            $commissions=$sysconf['info']['commissions'];

            $data_commissions=[]; //分润配置信息

            foreach ($commissions['pay_method'] as $key => $value) {
              # code...
              $data_commissions[]=['pay_method'=>$value,
                              'commission_level'=>$commissions['commission_level'][$key],
                              'mobile_receipt_total'=>$commissions['mobile_receipt_total'][$key],
                              'mobile_receipt_info'=>$commissions['mobile_receipt_info'][$key],
                            ];
            }

            $this->assign('sysconf',$sysconf);

            $this->assign('data_commissions',$data_commissions);

        }

        $this->display();
      }
    }


    /**
     * 管理员修改密码
     */
    public function modifyLoginPasswd($identify=0)
    {
    	$this->assign('t_user', session('tringid'));
        $this->assign('identify', $identify);
    	$this->display();
    }


    /**
     * 管理员修改密码操作
     */
    public function modifyLoginPasswd_act()
    {
        $identify=I('post.identify');
    	$original_passwd = I('post.original_passwd');
    	$new_passwd = I('post.new_passwd');
    	$c_new_passwd = I('post.c_new_passwd');

    	$t_user = session('tringid');

    	if (empty($original_passwd) || empty($new_passwd) || empty($c_new_passwd))
    	{
    		echo -2; exit();
    	}

    	if ($new_passwd != $c_new_passwd)
    	{
    		echo -2; exit();
    	}
    	//对比输入的旧密码是否一致
    	$original_passwd = md5( md5($original_passwd).C('rand_str'));
        if($identify==1){
            $tringroominfo_result = M('usermanage')->field('u_pass')->where(array( 'u_account' => $t_user ))->find();
            if (!empty($tringroominfo_result))
            {
                if ($original_passwd != $tringroominfo_result['u_pass'])
                {
                    echo -1; exit();
                }
            }

        }elseif($identify==0){
            $tringroominfo_result = M('tringroominfo')->field('t_pass')->where(array( 't_user' => $t_user ))->find();
            if (!empty($tringroominfo_result))
            {
                // echo $tringroominfo_result['t_pass'].'--'.$original_passwd;die;
                if ($original_passwd != $tringroominfo_result['t_pass'])
                {
                    echo -1; exit();
                }
            }

        }
    	//修改密码
    	$new_passwd = md5( md5($new_passwd) .C('rand_str'));

    	
        if($identify==1){
           $insertArray = array( 'u_pass' => $new_passwd);
           $res= M('usermanage')->data($insertArray)->where(array( 'u_account' => $t_user ))->save();;
        }elseif($identify==0){
           $insertArray = array( 't_pass' => $new_passwd);
           $res= M('tringroominfo')->data($insertArray)->where(array( 't_user' => $t_user ))->save();
        }
    	echo $res;exit();
    }


    /**
     * 设置客服
     */
    public function setCustomService()
    {
    	$type = I('post.type');

    	$where = !empty($type) ? 'type = ' . $type : '';

    	$contact_result = M('contact_info')->field('id, contact_no, type')->where($where)->select();

    	$type = !empty($type) ? $type : 1;

    	$this->assign('contact_type', $type);
    	$this->assign('contact_result', $contact_result);
    	$this->display();
    }


    /**
     * 增加客服
     */
    public function addCustomService()
    {
    	$this->display();
    }


    /**
     * 增加客服
     */
    public function addCustomService_act()
    {
    	$contact_no = I('post.contact_no');
    	$type = I('post.type');

    	if (empty($contact_no) || empty($type))
    	{
    		echo 0; exit();
    	}

    	$insertArray = array(
    		'contact_no' => $contact_no,
    		'type' => $type
    	);
    	M('contact_info')->data($insertArray)->add();

    	echo 1;
    }


    /**
     * 删除客服
     */
    public function delCustomService()
    {
    	$id = I('get.id');

    	if (empty($id))
    	{
    		echo 0; exit();
    	}

    	$insertArray = array('id' => $id);
    	M('contact_info')->where('id = ' . $id)->delete();

    	$this->redirect('Sysfy/setCustomService');
    }



    /**
     * 修改客服,查询单条信息
     */
    public function modifyCustomService()
    {
    	$id = I('get.id');

    	if (empty($id))
    	{
    		echo 0; exit();
    	}

    	$insertArray = array('id' => $id);
    	$contact_result = M('contact_info')->where('id = ' . $id)->find();

    	$this->assign('contact_result', $contact_result);

    	$this->display('Sysfy/modifyCustomService');
    }


    /**
     * 修改客服操作
     */
    public function modifyCustomService_act()
    {
    	$id = I('post.id');
    	$contact_no = I('post.contact_no');
    	$type = I('post.type');

    	if (empty($id) || empty($contact_no) || empty($type))
    	{
    		echo 0; exit();
    	}

    	$insertArray = array(
    		'contact_no' => $contact_no,
    		'type' => $type
    	);

    	M('contact_info')->data($insertArray)->where('id = ' . $id)->save();

    	echo 1;
    }
    /**
     * 关于我们
     * @return [type] [description]
     */
    public function aboutUs(){
         if($post=I('post.')){
            $about_us=$post['about_us'];
            $res=M('about_us')->where(array('about_id'=>1))->save(array('about_content'=>$about_us,'about_time'=>time()));
            if($res){
                $this->success('保存成功'); 
            }else{
                $this->error('未做更改');
            }
         }else{
            $about_us=M('about_us')->where(array('is_show'=>1))->find();
            $about_us=$about_us['about_content'];
            $this->assign('about_us',$about_us);
            $this->display();
         }
    }
}
