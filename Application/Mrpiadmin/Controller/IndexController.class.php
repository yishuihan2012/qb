<?php

namespace Mrpiadmin\Controller;

use Think\Controller;

class IndexController extends ConController {

    public function index(){

        $this->display('login');

    }

    public function login()

    {

        $tringid = $this->islogin();

        if ($tringid) {

            if(session("login_type")=='1'){

                $r = M("tringroominfo")->where(array("t_user" => $tringid))->getField("t_name");

                $r = $r ? '' : '管理员';

            }

            if(session("login_type")=='2'){

                $r = '平台员工';

            }

            $this->assign("t_name", $r);

            $this->assign("t_user", $_COOKIE['tringid']);

            $this->display('index');

        } else {

            $this->error("请登录",U('index/index'));

        }

    }

    public function checklogin()

    {

        $data = I("post.");

        $data['t_pass']=md5(md5($data['t_pass']).C('rand_str'));

        $t_user = $data['t_user'];

        $t_pass = $data['t_pass'];


        if ($t_user && $t_pass) {

            $r = M('tringroominfo')->where($data)->find();

            if ($r) {

                session(array("name"=>"tringid","expire"=>3600*12));

                session("tringid",$t_user);

                session("id",$r['id']);

                session("login_type",'1');

                echo 1;

            } else {

                $where=array(
                    'u_account' =>$t_user,
                    'u_pass'    =>$data['t_pass'] 
                );

                $c=M('usermanage')->where($where)->find();

               if($c){
                    if($c['status']==0){
                        echo -1;
                    }else{
                        session(array("name"=>"tringid","expire"=>3600*12));

                        session("tringid",$t_user);

                        session("id",$c['id']);

                        session("login_type",'2');

                        echo 1;
                    }
                }else{

                    session("tringid",null);

                    echo 0;

                }

            }

        } else {

            session("tringid",null);

            echo 0;

        }

    }

    public function logout()

    {

        session("tringid",null);

        $this->success("退出成功", U('index/index'));

    }

    public function indexpage()

    {
        //
       /* $login_type=session('login_type');

        if($login_type=='1'){

            $now_os=M('tringroominfo')->where(['id'=>session('id')])->getField('u_os');
            
        }else if($login_type=='2'){

            $now_os=M('usermanage')->where(['id'=>session('id')])->getField('u_os');
            
        }else{
            //
        }
        
        $this->assign('login_type',$login_type);*/

       $login_type=session('login_type');
        if($login_type==2){
            $id=session('id');
            $where['_string']='FIND_IN_SET('.$id.', paths)';
        }
        $count['count_member']=M('usermanage')->join('userlevel on userlevel.user_id=usermanage.id')->where($where)->count();
        $t = time();
        $count['time']=$t;
        $start_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));  //当天开始时间
        $end_time = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t)); //当天结束时间

        $count['count_member_today']=M('usermanage')->join('userlevel on userlevel.user_id=usermanage.id')->where($where)->where('u_times between "'.date('Y-m-d H:i:s',$start_time).' " and "'.date('Y-m-d H:i:s',$end_time).'"')->count();

        $count['count_order']=M('userorder')->join('usermanage on userorder.order_user_id=usermanage.id')->join('userlevel on userlevel.user_id=usermanage.id')->where($where)->count();

        $count['count_success_order']=M('userorder')->join('usermanage on userorder.order_user_id=usermanage.id')->join('userlevel on userlevel.user_id=usermanage.id')->where($where)->where(array('order_state'=>2))->count();

        // print_r($count);die;
        $this->assign('count',$count);
        $this->display('indexpage');

    }

}

