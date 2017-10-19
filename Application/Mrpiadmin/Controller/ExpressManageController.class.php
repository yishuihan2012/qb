<?php
namespace Mrpiadmin\Controller;
use Think\Controller;
class ExpressManageController extends ConController {
    public function index(){
        //$this->display('login');
    }
    public function ExpressAdd(){
        $MP_data=I("post.");
        if($MP_data){
            $MP_sql=M("expressmanage")->data($MP_data)->add();
            if($MP_sql){
                echo 1;
            }
            else{
                echo 0;
            }
        }
        else{
            $this->display();
        }
    }
    public function ExpressList(){
        $MP_sql=M("expressmanage")->select();
        if($MP_sql){
            $this->assign("express_list",$MP_sql);
        }
        $this->display();
    }
    public function ExpressEdit(){
        $MP_data=I("post.");
        if($MP_data){
            $id=$MP_data["id"];
            $MP_sql=M("expressmanage")->where("id=".$id)->data($MP_data)->save();
            if($MP_sql){
                echo 1;
            }
            else{
                echo 0;
            }
        }
        else{
            $id=I("get.id");
            $MP_sql=M("expressmanage")->where("id=".$id)->find();
            $this->assign("e_info",$MP_sql);
            $this->display();
        }
    }
    public function ExpressDel(){
        $MP_data=I("post.");
        if($MP_data){
            $id=$MP_data["id"];
            $MP_sql=M("expressmanage")->where("id=".$id)->delete();
            if($MP_sql){
                echo 1;
            }
            else{
                echo 0;
            }
        }
        else{
            $this->error("非法操作");
        }
    }
}