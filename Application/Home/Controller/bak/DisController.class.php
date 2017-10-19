<?php
namespace Home\Controller;
use Think\Controller;
class DisController extends ConController {
    public function index(){
        $id = $this->islogin();
            //首先判断用户存不存在
            if($id){
                $where['u_level'] = array('neq',0);
//                $where['u_level'] = array('LT',3);
                $where['id'] =$id;
                $sql = M("usermanage")->where($where)->field("u_nick,u_image,u_level,u_mobile,u_commission")->find();
                //再判断这个用户的类型是不是  小于2不等于0的数据
                if($sql)
                {   //如果是就输出信息
                    $MP_com=M("financemanage")->where("f_types=5 and f_uid=".$id)->sum("f_price");
                    if(empty($MP_com)){
                        $MP_com=0;
                    }
                    $this->assign("commission",$MP_com);
                    if($sql["u_level"]==1){
                        $sql["u_level"]="总代理";
                    }
                    else if($sql["u_level"]==2){
                        $sql["u_level"]="股东";
                    }
                    else if($sql["u_level"]==3){
                        $sql["u_level"]="爱心会员";
                    }
                    else{
                        $sql["u_level"]="普通用户";
                    }
                    $sql["u_nick"]=empty($sql["u_nick"])?$sql["u_mobile"]:$sql["u_nick"];
                    $sql["u_image"]=empty($sql["u_image"])?"/public/web/images/logo.jpg":$sql["u_image"];

                    $list = $sql;
                    
                }else{
                    //如果不是 就跳转
                    $this->redirect("My/MyFx");
                }
                $order_where["u_thec|u_theb|u_thea"]=$id;
                $order=M("usermanage")->where($order_where)->field("id")->select();
                //统计分销订单数量
                $order_num=0;
                foreach($order as $key=>$val){
                    $order_num+=M("ordermanage")->where("o_uid=".$val["id"])->count();
                }
                //统计我的客户
                $p_count=count($order);
                $this->assign('p_count',$p_count);
                $this->assign('ordercount',$order_num);
                $this->assign('list',$list);
                $this->display();
            }else{
                $this->redirect("Index/login");
            }

    } 
    public function DisOrder(){
        $id=$this->islogin(); 
        if($id){
            $where["u_thea|u_theb|u_thec"]=$id;
            $MP_data=M("usermanage")->where($where)->field("id")->select();
            $sid="";
            if($MP_data){
                foreach ($MP_data as $key => $val) {
                    $sid.=$val["id"].",";
                }
                $sid=substr($sid,0,-1);
                $s_where["o_uid"]=array("in",$sid);
                $order = M('ordermanage'); // 实例化User对象
                $count      = $order->where($s_where)->count();// 查询满足要求的总记录数
                $Page       = new \Think\Page($count,5);// 实例化分页类 传入总记录数和每页显示的记录数(25)
                $Page->setConfig('theme',' %UP_PAGE% %DOWN_PAGE% ');//
                $show       = $Page->show();// 分页显示输出
                // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
                $list = $order->order('id desc')->where($s_where)->limit($Page->firstRow.','.$Page->listRows)->select();
                foreach($list as $key=>$val){
                    $val["o_ginfo"]=unserialize($val["o_ginfo"]);
                    foreach($val["o_ginfo"] as $k=>$v){
                        $val["o_ginfo"][$k]=M("goodsmanage")->where("id=".$v["gid"])->field("id,g_thumb,g_title,g_vipprice")->find();
                        $val["o_ginfo"][$k]["gnum"]=$v["gnum"];
                    }
                    $lists[$key]=$val;
                }
                $this->assign('list',$lists);// 赋值数据集
                $this->assign('page',$show);// 赋值分页输出
            }
            $this->display(); // 输出模板
        }else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function DisYong(){
        $id=$this->islogin();
        if($id){
            $MP_sql=M("financemanage")->where("f_types=5 and f_uid=".$id)->order("id desc")->select();
            $MP_com=M("financemanage")->where("f_types=5 and f_uid=".$id)->sum("f_price");
            if($MP_sql){
                $this->assign("f_list",$MP_sql);
            }
            if(empty($MP_com)){
                $MP_com=0;
            }
            $this->assign("commission",$MP_com);
            $this->display();
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function DisTd(){
        $id=$this->islogin();
        if($id){
            $where["u_thea|u_theb|u_thec"]=$id;
            $MP_data=M("usermanage")->where($where)->field("id")->select();
            foreach($MP_data as $k=>$v) {
                $data = M("usermanage")->where("id=" . $v["id"])->field("id,u_thec,u_theb,u_thea,u_image,u_nick,u_mobile,u_commission")->find();
                if($data){
                    $data["count1"] = M("usermanage")->where("u_thec=" . $data["id"])->count();
                    $data["count2"] = M("usermanage")->where("u_theb=" . $data["id"])->count();
                    $data["count3"] = M("usermanage")->where("u_thea=" . $data["id"])->count();
                    $data["u_nick"] = empty($data["u_nick"]) ? $data["u_mobile"] : $data["u_nick"];
                    $data["u_image"] = empty($data["u_image"]) ? "/public/web/images/logo.jpg" : $data["u_image"];
                    $list[]=$data;
                }
            }
            $this->assign("list",$list);
            $this->display();
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function DisKh(){
        $id=$this->islogin();
        if($id){
                $cc["count1"]=M("usermanage")->where("u_thec=".$id)->count();
                $cc["count2"]=M("usermanage")->where("u_theb=".$id)->count();
                $cc["count3"]=M("usermanage")->where("u_thea=".$id)->count();
                $this->assign('cc',$cc);
            $this->display();
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
}