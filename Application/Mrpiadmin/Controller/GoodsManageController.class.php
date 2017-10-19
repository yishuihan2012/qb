<?php
namespace Mrpiadmin\Controller;
use Think\Controller;
class GoodsManageController extends ConController {
    public function index(){
        //$this->display();
    }
    public function GoodsAdd(){
        $MP_data=I("post.");
        if($MP_data){
            if(empty($MP_data["g_title"])){
                $this->error("请填写商品名称");
            }
            $MP_data["g_image"]=implode("|",$MP_data["g_image"]);
            //处理三级分销比例
            $MP_data["g_scalea"]=array(
                "types"=>$MP_data["types_a"],
                "scale"=>$MP_data["types_a"]==1 ? (string)($MP_data["scale_a"]/100) : (string)$MP_data["scale_a"]
            );
            $MP_data["g_scalea"]=serialize($MP_data["g_scalea"]);
            //处理二级分销比例
            $MP_data["g_scaleb"]=array(
                "types"=>$MP_data["types_b"],
                "scale"=>$MP_data["types_b"]==1 ? (string)($MP_data["scale_b"]/100) : (string)$MP_data["scale_b"]
            );
            $MP_data["g_scaleb"]=serialize($MP_data["g_scaleb"]);
            //处理一级分销比例
            $MP_data["g_scalec"]=array(
                "types"=>$MP_data["types_c"],
                "scale"=>$MP_data["types_c"]==1 ? (string)($MP_data["scale_c"]/100) : (string)$MP_data["scale_c"]
            );
            $MP_data["g_scalec"]=serialize($MP_data["g_scalec"]);
            //处理商品属性
            for($i=0;$i<count($MP_data["attr_name"]);$i++){
                $MP_data["g_attr"][]=array(
                    "attr_name"=>$MP_data["attr_name"][$i],
                    "attr_value"=>explode("|",$MP_data["attr_value"][$i])
                );
            }
            $MP_data["g_attr"]=serialize($MP_data["g_attr"]);
            for($i=0;$i<count($MP_data["info_name"]);$i++){
                $MP_data["g_info"][]=array(
                    "info_name"=>$MP_data["info_name"][$i],
                    "info_value"=>explode("|",$MP_data["info_value"][$i])
                );
            }
            $MP_data["g_info"]=serialize($MP_data["g_info"]);
            $MP_data["g_description"]=htmlspecialchars_decode($MP_data["g_description"]);
            $MP_data["g_state"]=1;
            $MP_sql=M("goodsmanage")->data($MP_data)->add();
            if($MP_sql){
                echo 1;
            }
            else{
                echo 0;
            }
        }
        else{
            $MP_sql=M("gtypemanage")->select();
            $this->assign("type_list",$MP_sql);
            $this->display();
        }
    }
    public function GoodsList(){
        $map['o_status']=array('in','1,2,3,5,7');
        $order=M('ordermanage')->where($map)->field('o_ginfo')->select();
        $orderGinfo=array();
        foreach($order as $k=>$v){
            $orderGinfo[]=unserialize($v['o_ginfo']);
        }
        $User = M('goodsmanage'); // 实例化User对象
        $count      = $User->where("is_del=0")->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,12);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $User->where("is_del=0")->field("id,g_title,g_thumb,g_type,g_scalea,g_scaleb,g_scalec,g_price,g_vipprice,g_stock,g_salesnum,g_state,is_del")->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        //处理列表显示数据

        foreach($list as $key=>$val){
            $val["g_scalea"]=unserialize($val["g_scalea"]);
            if($val["g_scalea"]["types"]==1){
                $val["g_scalea"]["scale"]=($val["g_scalea"]["scale"]*100)."%";
            }
            $val["g_scaleb"]=unserialize($val["g_scaleb"]);
            if($val["g_scaleb"]["types"]==1){
                $val["g_scaleb"]["scale"]=($val["g_scaleb"]["scale"]*100)."%";
            }
            $val["g_scalec"]=unserialize($val["g_scalec"]);
            if($val["g_scalec"]["types"]==1){
                $val["g_scalec"]["scale"]=($val["g_scalec"]["scale"]*100)."%";
            }
            foreach($orderGinfo as $m){
                foreach($m as $n){
                    if($n['gid']==$val['id']){
                        $val['truesale']+=$n['gnum'];
                    }
                }
            }


            $val["g_thumb"]=empty($val["g_thumb"]) ? "/Public/img/profile.jpg":$val["g_thumb"];
            $val["g_type"]=M("gtypemanage")->where("id=".$val["g_type"])->getField("g_name");
            $goods_list[$key]=$val;


        }
        $this->assign('goods_list',$goods_list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->display(); // 输出模板
    }
    public function GoodsEdit(){
        $MP_data=I("post.");
        if($MP_data){
            if(empty($MP_data["g_title"])){
                $this->error("请填写商品名称");
            }
            if(!empty($MP_data["g_image"])) {
                $MP_data["g_image"] = implode("|", $MP_data["g_image"]);
            }
            //处理三级分销比例
            $MP_data["g_scalea"]=array(
                "types"=>$MP_data["types_a"],
                "scale"=>$MP_data["types_a"]==1 ? (string)($MP_data["scale_a"]/100) : (string)$MP_data["scale_a"]
            );
            $MP_data["g_scalea"]=serialize($MP_data["g_scalea"]);
            //处理二级分销比例
            $MP_data["g_scaleb"]=array(
                "types"=>$MP_data["types_b"],
                "scale"=>$MP_data["types_b"]==1 ? (string)($MP_data["scale_b"]/100) : (string)$MP_data["scale_b"]
            );
            $MP_data["g_scaleb"]=serialize($MP_data["g_scaleb"]);
            //处理一级分销比例
            $MP_data["g_scalec"]=array(
                "types"=>$MP_data["types_c"],
                "scale"=>$MP_data["types_c"]==1 ? (string)($MP_data["scale_c"]/100) : (string)$MP_data["scale_c"]
            );
            $MP_data["g_scalec"]=serialize($MP_data["g_scalec"]);
            //处理商品属性
            for($i=0;$i<count($MP_data["attr_name"]);$i++){
                $MP_data["g_attr"][]=array(
                    "attr_name"=>$MP_data["attr_name"][$i],
                    "attr_value"=>explode("|",$MP_data["attr_value"][$i])
                );
            }
            $MP_data["g_attr"]=serialize($MP_data["g_attr"]);
            for($i=0;$i<count($MP_data["info_name"]);$i++){
                $MP_data["g_info"][]=array(
                    "info_name"=>$MP_data["info_name"][$i],
                    "info_value"=>explode("|",$MP_data["info_value"][$i])
                );
            }
            $MP_data["g_info"]=serialize($MP_data["g_info"]);
            $MP_data["g_description"]=htmlspecialchars_decode($MP_data["g_description"]);
            $MP_data["g_state"]=0;
            $MP_sql=M("goodsmanage")->where("id=".$MP_data["id"])->data($MP_data)->save();
            if($MP_sql){
                echo 1;
            }
            else{
                echo 0;
            }
        }
        else{
            $id=I("get.id");
            $MP_sql=M("goodsmanage")->where("id=".$id)->find();
            $MP_sql["g_image"]=explode("|",$MP_sql["g_image"]);
            $MP_sql["g_attr"]=unserialize($MP_sql["g_attr"]);
            foreach($MP_sql["g_attr"] as $key=>$val){
                $val["attr_value"]=implode("|",$val["attr_value"]);
                $MP_sql["g_attr"][$key]=$val;
            }
            $MP_sql["g_info"]=unserialize($MP_sql["g_info"]);
            foreach($MP_sql["g_info"] as $key=>$val){
                $val["info_value"]=implode("|",$val["info_value"]);
                $MP_sql["g_info"][$key]=$val;
            }
            $MP_sql["g_scalea"]=unserialize($MP_sql["g_scalea"]);
            if($MP_sql["g_scalea"]["types"]==1){
                $MP_sql["g_scalea"]["scale"]=$MP_sql["g_scalea"]["scale"]*100;
            }
            $MP_sql["g_scaleb"]=unserialize($MP_sql["g_scaleb"]);
            if($MP_sql["g_scaleb"]["types"]==1){
                $MP_sql["g_scaleb"]["scale"]=$MP_sql["g_scaleb"]["scale"]*100;
            }
            $MP_sql["g_scalec"]=unserialize($MP_sql["g_scalec"]);
            if($MP_sql["g_scalec"]["types"]==1){
                $MP_sql["g_scalec"]["scale"]=$MP_sql["g_scalec"]["scale"]*100;
            }
            $this->assign("g",$MP_sql);
            $MP_type=M("gtypemanage")->select();
            $this->assign("type_list",$MP_type);
            $this->display();
        }
    }
    public function GoodsDel(){
        $MP_data=I("post.");
        if($MP_data){
            $id=$MP_data["id"];
            $MP_sql=M("goodsmanage")->where("id=".$id)->setField("is_del",1);
            if($MP_sql){
                echo 1;;
            }
            else{
                echo 0;
            }
        }
        else{
            $this->error("非法操作");
        }
    }
    public function GoodsUnDel(){
        $MP_data=I("post.");
        if($MP_data){
            $id=$MP_data["id"];
            $MP_sql=M("goodsmanage")->where("id=".$id)->setField("is_del",0);
            if($MP_sql){
                echo 1;;
            }
            else{
                echo 0;
            }
        }
        else{
            $this->error("非法操作");
        }
    }
    public function GoodsState(){
        $MP_data=I("post.");
        if($MP_data){
            $id=$MP_data["id"];
            $MP_sql=M("goodsmanage")->where("id=".$id)->setField("g_state",1);
            if($MP_sql){
                echo 1;;
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