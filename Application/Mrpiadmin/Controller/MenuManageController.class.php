<?php
namespace Mrpiadmin\Controller;
use Think\Controller;
class MenuManageController extends ConController {
    public function index(){
        //$this->display('login');
    }
    public function MenuAdd(){
        $MP_data=I("post.");
        if($MP_data){
            $MP_data["m_content"]=htmlspecialchars_decode($MP_data["m_content"]);
            //$MP_data["is_menu"]=1;
	
            $MP_sql=M("menumanage")->data($MP_data)->add();
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
    public function MenuList(){
        $MP_sql=M("menumanage")->where("is_del=0 and is_menu=1")->select();
        if($MP_sql){
            $this->assign("menu_list",$MP_sql);
        }
        $this->display();
    }
    public function MenuEdit(){
        $MP_data=I("post.");
        if($MP_data){
            $id=$MP_data["id"];
            $MP_data["is_del"]=0;
            //$MP_data["is_menu"]=1;

            $MP_data["m_content"]=htmlspecialchars_decode($MP_data["m_content"]);
            $MP_sql=M("menumanage")->where("id=".$id)->data($MP_data)->save();
            if($MP_sql){
                echo 1;
            }
            else{
                echo 0;
            }
        }
        else{
            $id=I("get.id");
            $MP_sql=M("menumanage")->where("id=".$id)->find();
            $this->assign("menu_info",$MP_sql);
            $this->display();
        }
    }
    public function MenuKjEdit(){
		die;
        $MP_data=I("post.");
        if($MP_data){
            $id=$MP_data["id"];
            $MP_data["is_del"]=0;
            $MP_data["is_menu"]=0;
            $MP_data["m_content"]=htmlspecialchars_decode($MP_data["m_content"]);
            $MP_sql=M("menumanage")->where("id=".$id)->data($MP_data)->save();
            if($MP_sql){
                echo 1;
            }
            else{
                echo 0;
            }
        }
        else{
            $id=I("get.id");
            $MP_sql=M("menumanage")->where("id=".$id)->find();
            $this->assign("menu_info",$MP_sql);
            $this->display();
        }
    }
    public function MenuCon(){
        $id=I("get.id");
        $MP_sql=M("menumanage")->where("id=".$id)->find();
        $this->assign("menu_info",$MP_sql);
        $this->display();
    }
    public function MenuDel(){
        $MP_data=I("post.");
        if($MP_data){
            $id=$MP_data["id"];
            $MP_sql=M("menumanage")->where("id=".$id)->setField("is_del",1);
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