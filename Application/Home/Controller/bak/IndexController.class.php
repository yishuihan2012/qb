<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends ConController {
    public function index(){
        $MP_ad=M("admanage")->where("is_del=0")->select();
        if($MP_ad){
            foreach($MP_ad as $key=>$val){
                if($val["a_position"]==0){
                    $banner[]=$val;
                }
                else if($val["a_position"]==1){
                    $ad[]=$val;
                }
            }
            $this->assign("banner",$banner);
            $this->assign("ad",$ad);
        }
        $MP_menu=M("menumanage")->where("is_del=0 and is_menu=1")->select();
        $this->Kj_menu=M("menumanage")->where("is_del=0 and is_menu=0")->select();
        $this->assign("menu",$MP_menu);
        $MP_goods=M("goodsmanage")->where("is_del=0")->field("id,g_price,g_vipprice,g_title,g_salesnum,g_thumb,g_reco,g_type,g_stock")->order("g_stock desc")->select();
        if($MP_goods){
            $this->assign("goods",$MP_goods);
        }

        $this->display();
    }
    public function login(){
        $MP_data=I("post.");
        if($MP_data){
            $data['u_pass']=md5pass($MP_data['u_pass']);
            $data["u_state"]=1;
            $data["is_del"]=0;
            $data['u_mobile']=$MP_data['u_mobile'];
            $data['u_mobile']=$MP_data['u_mobile'];
            
            $MP_check=M("usermanage")->where($data)->find();
            if($MP_check) {
				$path=explode("/",$MP_check['u_ewm']);
                $ewm='./'.$path[3]."/".$path[4].'/'.$path[5];
                $ewmpath='./'.$path[3]."/".$path[4];
				
                if(!file_exists($ewmpath)){
                    mkdir($ewmpath);
                }
                if(!file_exists($ewm)){
                    vendor("phpqrcode.phpqrcode");
                    //$data = $i_data["text"];//生成内容
                    $data = "http://www.xyclsw.com".U("Home/Index/Reg",array("code"=>$MP_check["u_code"]));//生成内容
                    $lv = "L";//容错级别L,M,Q,H
                    $size = 10;//大小1~10
                    $filename = $path[5];//图片名称
                    \QRcode::png($data, $ewmpath.'/'.$filename, $lv, $size); 
                }
				
				
				
                session("login",$MP_check["id"]);
                $u_nick=empty($MP_check["u_nick"]) ? $MP_check["u_mobile"]:$MP_check["u_nick"];
                if($MP_data['remember']==1){
                    cookie('u_mobile',$MP_data['u_mobile'],time()+86400);
                    cookie('u_pass',$MP_data['u_pass'],time()+86400);
                    cookie('remember',$MP_data['remember'],time()+86400);
                }else{
                    cookie('u_mobile',$MP_data['u_mobile'],time()-86400);
                    cookie('u_pass',$MP_data['u_pass'],time()-86400);
                    cookie('remember',$MP_data['remember'],time()-86400);
                }
                session("u_nick",$u_nick);
                session('L_code',null);
                //redirect('Index/index');
                echo 1;
            }
            else{
                echo 0;
            }
        }
        else{
            $u_mobile=cookie('u_mobile');
            $u_pass=cookie('u_pass');
            $remember=cookie('remember');
            if($u_mobile&&$u_pass&&$remember){
                $this->assign('u_mobile',$u_mobile);
                $this->assign('u_pass',$u_pass);
                $this->assign('remember',$remember);
            }
            session('L_code',null);
            $this->display();
        }
    }
    public function loginout(){
        session("login",null);
        session("u_nick",null);
        $this->success("退出成功",U("Index/login"));
    }
    public function Reg(){
        $MP_data=I("post.");
        if($MP_data){
            $MP_check=M("usermanage")->where("u_mobile=".$MP_data["u_mobile"])->count();
            if($MP_check){
                echo 0;
            }
            else{
               $MP_code=session("code");
                if(!empty($MP_code)){
                    $MP_check_code=M("usermanage")->where("u_code='{$MP_code}'")->find();
                    if($MP_check_code && $MP_check_code["u_level"]!=0){
                        if($MP_check_code["u_isrose"]==1){
                            $MP_data["u_rose"]=$MP_check_code["id"];
                        }
                        else{
                            $MP_data["u_rose"]=$MP_check_code["u_rose"];
                        }
                        $MP_data["u_thec"]=$MP_check_code["id"];
                        $MP_data["u_theb"]=$MP_check_code["u_thec"];
                        $MP_data["u_thea"]=$MP_check_code["u_theb"];
//                        $MP_data["u_level"]=3;
                    }
                }
                if($MP_data['vcode']==""){
                    echo 2;
                    die;
                }
                if($MP_data['vcode']!=session('L_code')){
                    echo 3;
                    die;
                }
                $MP_data["u_pass"]=md5pass($MP_data["u_pass"]);
                $MP_data["u_account"]=$MP_data["u_mobile"];
                $MP_data["u_code"]=rand_zifu(3,8);
                $MP_data["u_state"]=1;
                $MP_data["u_times"]=NOW_TIME;
                $MP_sql=M("usermanage")->data($MP_data)->add();

                //二维码图片生成
                vendor("phpqrcode.phpqrcode");
                //$data = $i_data["text"];//生成内容
                $data = "http://www.xyclsw.com".U("Home/Index/Reg",array("code"=>$MP_data["u_code"]));//生成内容
                $lv = "L";//容错级别L,M,Q,H
                $size = 10;//大小1~10
                $path = "./Uploads/".date("Y-m-d",time())."/";//图片保存地址
                if(!file_exists($path)){
                    mkdir($path);
                }
                $filename = "yt" . time() . $size .$MP_sql. ".png";//图片名称
                \QRcode::png($data, $path.$filename, $lv, $size);
                $QR_img="http://www.xyclsw.com".substr($path.$filename,1);
                $MP_set=M("usermanage")->where("id=".$MP_sql)->setField("u_ewm",$QR_img);
                session("login",$MP_sql);
                session("u_nick",$MP_data["u_mobile"]);
                session('L_code',null);
                session("code".null);
                echo 1;
            }
        }
        else{
            session('L_code',null);
            $code=I("get.code");
            session("code",$code);
            $this->display();
        }
    }
    public function MenuCon(){
        $id=I("get.id");
        if($id){
            $MP_sql=M("menumanage")->where("id=".$id)->find();
            $this->assign("m",$MP_sql);
            $this->display();
        }
        else{
            $this->error("非法操作");
        }
    }
    public function region(){
        $MP_data=I("post.");
        if($MP_data){
            if(!empty($MP_data["a_provice"])){
                $MP_sql=M("region")->where("parent_id=".$MP_data["a_provice"])->field("region_id,region_name")->select();
            }
            else if(!empty($MP_data["a_city"])){
                $MP_sql=M("region")->where("parent_id=".$MP_data["a_city"])->field("region_id,region_name")->select();
            }
            exit(json_encode($MP_sql));
        }
    }
    public function ProductList(){
        $this->display();
    }
    public function ProductCon(){
        $gid=I("get.id");
        $MP_sql=M("goodsmanage")->where("id=".$gid)->find();
        $MP_sql["g_attr"]=unserialize($MP_sql["g_attr"]);
        $attrnum=0;
        foreach($MP_sql["g_attr"] as $key=>$val){
            if(!empty($val["attr_name"])){
                $attrnum++;
            }
        }
        if($attrnum==0){
            $MP_sql["g_attr"]="";
        }
        $MP_sql["g_info"]=unserialize($MP_sql["g_info"]);
        $infonum=0;
        foreach($MP_sql["g_info"] as $key=>$val){
            if(!empty($val["info_name"])){
                $infonum++;
            }
        }
        if($infonum==0){
            $MP_sql["g_info"]="";
        }
        $MP_sql["g_image"]=explode("|",$MP_sql["g_image"]);
        $this->assign("g",$MP_sql);
        $MP_goods=M("goodsmanage")->where("is_del=0")->order("g_salesnum desc")->field("id,g_thumb,g_title,g_price,g_vipprice")->limit(6)->select();
        $this->assign("goods_list",$MP_goods);
        $this->display();
    }
    public function ShopCart(){
        $id=$this->islogin();
        if($id){
            $MP_sql=M("cartmanage")
                ->alias("c")
                ->join("goodsmanage g on c.c_gid=g.id")
                ->field("c.*,g.g_title,g.g_thumb,g.g_vipprice")
                ->where("c.c_uid=".$id)
                ->select();
            if($MP_sql){
                $this->assign("cart_list",$MP_sql);
            }
            $this->display();
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function SubmitOrder(){
        $id=$this->islogin();
        $aid=I('request.a_id');
        if(!empty($aid)){
            $map['id']=$aid;
        }else{
            $map['a_uid']=$id;
            $map['a_default']=1;
        }
        //20160917
        $MP_address = M("addressmanage")->where($map)->order("id desc")->find();
        $this->assign("a", $MP_address);
        //20160917
        if($id){
            $MP_gid=I("get.gid");
            if($MP_gid){
                $MP_price = 0;
                $MP_goods[0] = M("goodsmanage")->where("id=" . $MP_gid)->field("id,g_title,g_vipprice,g_price,g_vipprice,g_thumb,g_stock")->find();
                $MP_goods[0]["num"]=I('get.num');
                if($MP_goods[0]["num"]>$MP_goods[0]["g_stock"]){
                    $this->error("该商品已卖完，请重新选择");
                    exit();
                }
                $MP_price = $MP_price + $MP_goods[0]["g_vipprice"] * $MP_goods[0]["num"];
                $MP_goods[0]['ginfo']="";
                //20160919
                if(is_array(I('get.ginfo'))){
                    foreach (I('get.ginfo') as $val){
                        foreach ($val as $v){
                            $MP_goods[0]['ginfo'].=$v.",";
                        }
                    }
                    $MP_goods[0]['ginfo']=rtrim($MP_goods[0]['ginfo'],',');
                }else{
                    $MP_goods[0]['ginfo']=I('get.ginfo');
                }
                //20160919
                $this->assign("list", $MP_goods);
                $this->assign("totalprice", $MP_price);
                //20160919
//                $this->ginfo=$MP_goods[0]['ginfo'];
                $this->ginfo=iconv('UTF-8', 'GB2312', $MP_goods[0]['ginfo']);
                $this->num=I('get.num');
                $MP_gids=$MP_gid;
                $this->hid=1;
                $this->check=1;
                //20160919
            }
            else {
                $gid = I("post.c_id");
                $MP_price = 0;
                $MP_gids="";
                foreach ($gid as $key => $val) {
                    $cart_info=M("cartmanage")->where("id=".$val)->find();
                    $MP_goods[$key] = M("goodsmanage")->where("id=" . $cart_info["c_gid"])->field("id,g_title,g_vipprice,g_price,g_vipprice,g_thumb,g_stock")->find();
                    if($cart_info["c_gnum"]>$MP_goods[$key]["g_stock"]){
                        $this->error("部分商品已卖完，请重新选择");
                        exit();
                    }
                    $MP_gids.=$val."_";
                    $MP_goods[$key]["num"] = $cart_info["c_gnum"];
                    $MP_goods[$key]["ginfo"] = $cart_info["c_ginfo"];
                    $MP_price = $MP_price + $MP_goods[$key]["g_vipprice"] * $MP_goods[$key]["num"];
                }
                $this->assign("list", $MP_goods);
                $this->assign("totalprice", $MP_price);

                $this->hid=2;
                $this->check=2;
            }
            $this->MP_gids=$MP_gids;
            $this->display();
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function CartAdd(){
        $id=$this->islogin();
        if($id){
            $MP_data=I("post.");
            $c_ginfo=rtrim($MP_data["ginfo"],',');
            $MP_check=M("cartmanage")->where("c_gid={$MP_data["id"]} and c_uid={$id} and c_ginfo='{$c_ginfo}'")->find();
            if($MP_check){
                $MP_set["c_gnum"]=$MP_check["c_gnum"]+$MP_data["num"];
                $MP_sql=M("cartmanage")->where("id=".$MP_check["id"])->setField($MP_set);
                if($MP_sql){
                    echo 1;
                }
            }
            else {
                $MP_cart = array(
                    "c_gid" => $MP_data["id"],
                    "c_gnum" => $MP_data["num"],
                    "c_uid" => $id,
                    "c_ginfo" => $c_ginfo
                );
                $MP_sql=M("cartmanage")->data($MP_cart)->add();
                if($MP_sql){
                    echo 1;
                }
            }
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function CartDel(){
        $id=$this->islogin();
        if($id){
            $cid=I("post.id");
            $MP_sql=M("cartmanage")->where("id=".$cid)->delete();
            if($MP_sql){
                echo 1;
            }
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function BackPass(){
        session('L_code',null);
        $this->display();
    }
    /***
     * 检测手机号
     * LSZ 20160914
     */
    public function checkPhone(){
        $map['u_moblie']=I('post.phoneNum');
        $count=M('usermanage')->where($map)->count();
        if($count>0){
            $this->ajaxReturn(1);
        }else{
            $this->ajaxReturn(0);
        }
    }
    /**
     * 修改密码
     *  LSZ 20160914
     */
    public function changePwd(){
        $u_mobile=I('post.u_mobile');
        $map['u_mobile']=$u_mobile;
        $newpwd=md5pass(I('post.newpwd'));
        $data['u_pass']=$newpwd;
        if(M('usermanage')->where($map)->save($data)){
            session('L_code',null);
            echo 1;
        }
        else{
            session('L_code',null);
            echo 0;
        }
    }
    /**
     * 修改购物车数量
     * 20160914 LSZ
     */
    public function editNum(){
        $map['id']=I('post.id');
        $data['c_gnum']=I('post.c_gnum');
        M('cartmanage')->where($map)->setField($data);

    }
    //结束
    public function OrderPrint(){
        $oid=I("get.id");
        if($oid){
            $MP_sql=M("ordermanage")
                ->alias("o")
                ->join("addressmanage a on o.o_aid=a.id")
                ->where("o.id=".$oid)
                ->field("a.*,o.*")
                ->find();

            $MP_sql['o_ginfo']=unserialize($MP_sql['o_ginfo']);
            $this->assign("e",$MP_sql);
            $this->display();
        }
        else{
            $this->error("非法操作");
        }
    }
}