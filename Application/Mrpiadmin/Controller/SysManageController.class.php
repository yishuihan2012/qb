<?php
namespace Mrpiadmin\Controller;
use Think\Controller;
class SysManageController extends ConController {
    public function index(){
        $MP_data=I("post.");
        if($MP_data){
            //数据处理分组
            if($MP_data["types_r"]==1)$MP_data["scale_r"]=(string)($MP_data["scale_r"]/100);
            $MP_data["s_rose"]=array(
                "types"=>$MP_data["types_r"],
                "scale"=>$MP_data["scale_r"]
            );
            if($MP_data["types_t"]==1)$MP_data["scale_t"]=(string)($MP_data["scale_t"]/100);
            $MP_data["s_trose"]=array(
                "types"=>$MP_data["types_t"],
                "scale"=>$MP_data["scale_t"]
            );
            if($MP_data["types_a"]==1)$MP_data["scale_a"]=(string)($MP_data["scale_a"]/100);
            $MP_data["s_scalea"]=array(
                "types"=>$MP_data["types_a"],
                "scale"=>$MP_data["scale_a"]
            );
            if($MP_data["types_b"]==1)$MP_data["scale_b"]=(string)($MP_data["scale_b"]/100);
            $MP_data["s_scaleb"]=array(
                "types"=>$MP_data["types_b"],
                "scale"=>$MP_data["scale_b"]
            );
            if($MP_data["types_c"]==1)$MP_data["scale_c"]=(string)($MP_data["scale_c"]/100);
            $MP_data["s_scalec"]=array(
                "types"=>$MP_data["types_c"],
                "scale"=>$MP_data["scale_c"]
            );
            //数据进行序列化
            $MP_data["s_rose"]=serialize($MP_data["s_rose"]);
            $MP_data["s_trose"]=serialize($MP_data["s_trose"]);
            $MP_data["s_scalea"]=serialize($MP_data["s_scalea"]);
            $MP_data["s_scaleb"]=serialize($MP_data["s_scaleb"]);
            $MP_data["s_scalec"]=serialize($MP_data["s_scalec"]);
            //更新数据库信息
            $MP_sql=M("sysmanage")->where("id=1")->data($MP_data)->save();
            if($MP_sql){
                echo 1;
            }
            else{
                echo 0;
            }
        }
        else {
            $MP_sql=M("sysmanage")->where("id=1")->find();
            //分组处理=>总代分销比例
            $MP_sql["s_rose"]=unserialize($MP_sql["s_rose"]);
            if($MP_sql["s_rose"]["types"]==1){
                $MP_sql["s_rose"]["scale"]=$MP_sql["s_rose"]["scale"]*100;
            }
            //分组处理=>三级分销比例
            $MP_sql["s_scalea"]=unserialize($MP_sql["s_scalea"]);
            if($MP_sql["s_scalea"]["types"]==1){
                $MP_sql["s_scalea"]["scale"]=$MP_sql["s_scalea"]["scale"]*100;
            }
            //分组处理=>二级分销比例
            $MP_sql["s_scaleb"]=unserialize($MP_sql["s_scaleb"]);
            if($MP_sql["s_scaleb"]["types"]==1){
                $MP_sql["s_scaleb"]["scale"]=$MP_sql["s_scaleb"]["scale"]*100;
            }
            //分组处理=>一级分销比例
            $MP_sql["s_scalec"]=unserialize($MP_sql["s_scalec"]);
            if($MP_sql["s_scalec"]["types"]==1){
                $MP_sql["s_scalec"]["scale"]=$MP_sql["s_scalec"]["scale"]*100;
            }
            //分组处理=>分代分销比例
            $MP_sql["s_trose"]=unserialize($MP_sql["s_trose"]);
            if($MP_sql["s_trose"]["types"]==1){
                $MP_sql["s_trose"]["scale"]=$MP_sql["s_trose"]["scale"]*100;
            }
            $this->assign("s",$MP_sql);
            $this->display();
        }
    }
    public function setEwm(){
        if(IS_POST){
            $data=I('post.');
            if(M('ewmbgmanage')->where("id=1")->save($data)){
                echo 1;
            }else{
                echo 0;
            }
        }else{
            $info=M('ewmbgmanage')->find();
            $this->info=$info;
        }

        $this->display();
    }
}