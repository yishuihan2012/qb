<?php
namespace Mrpiadmin\Controller;
use Think\Controller;
class AdManageController extends ConController {
    public function index(){
        //$this->display('login');
    }
    public function AdAdd(){
        $MP_data=I("post.");
        if($MP_data){
            $MP_sql=M("admanage")->data($MP_data)->add();
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
    public function AdList(){
        $MP_sql=M("admanage")->where("is_del=0")->select();
        if($MP_sql){
            $this->assign("ad_list",$MP_sql);
        }
        $this->display();
    }
    public function AdEdit(){
        $MP_data=I("post.");
        if($MP_data){
            $id=$MP_data["id"];
            $MP_data["is_del"]=0;
            $MP_sql=M("admanage")->where("id=".$id)->data($MP_data)->save();
            if($MP_sql){
                echo 1;
            }
            else{
                echo 0;
            }
        }
        else{
            $id=I("get.id");
            $MP_sql=M("admanage")->where("id=".$id)->find();
            $this->assign("ad_info",$MP_sql);
            $this->display();
        }
    }
    public function AdDel(){
        $MP_data=I("post.");
        if($MP_data){
            $id=$MP_data["id"];
            $MP_sql=M("admanage")->where("id=".$id)->setField("is_del",1);
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