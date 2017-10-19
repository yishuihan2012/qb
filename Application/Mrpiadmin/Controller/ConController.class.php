<?php

namespace Mrpiadmin\Controller;

use Think\Controller;

class ConController extends Controller {

	public function _initialize(){

		 $MP_sql=M("menumanage")->where("is_del=0 and is_menu=0")->select();

        if($MP_sql){

            $this->assign("not_menu",$MP_sql);

        }

	}

    public function index(){

        //

    }

    public function islogin()

    {

        $tringid = session("tringid");

        $type    = session("login_type");

        if ($tringid) {

            $r = M('tringroominfo')->where(array("t_user" => $tringid))->count();

            if ($r == 1) {

                return $tringid;

            } else {

                $c=M('usermanage')->where(array("u_account" => $tringid))->count();

                if($c==1){

                    return $tringid;

                }else{

                    return false;

                }

            }

        } else {

            return false;

        }

    }


    //迭代器寻找子孙
    public function getSoon($arr,$parent=0)
    {
        $task = array($parent);//创建任务表
        $subs = array();//存子孙栏目的数组
        while(!empty($task))//如果任务表不为空 就表示要做任务
        {
            $flag = false;//默认没找到子树
            foreach($arr as $k=>$v){
                 if($v['parent_id'] == $parent){
                        $subs[]=$v['user_id'];
                        array_push($task,$v['user_id']);//借助栈 把新的地区的id压入栈
                        $parent = $v['user_id'];
                        unset($arr[$k]);//把找到的单元unset掉
                        $flag = true;
                 }
            }
            if(!$flag){//表示没找到子树
                array_pop($task);
                $parent = end($task);
                
            }

        }
        return $subs;

    }

}