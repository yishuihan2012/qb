<?php
namespace Home\Controller;
use Think\Controller;
class MyController extends ConController {
    public function index(){
        $id=$this->islogin();
        if($id){
            $MP_sql=M("usermanage")->where("id=".$id)->find();
            if($MP_sql){
                //统计个人购买订单数
                $MP_sql["u_order"]=M("ordermanage")->where("o_uid=".$id)->count();
                //分权限统计销售额，销售量
                if($MP_sql["u_isrose"]==1 && $MP_sql["u_level"]==1){
                    $MP_where["u_rose"]=$id;
                }
                else{
                    $MP_where["u_thea|u_theb|u_thec"]=$id;
                }
                $MP_list=M("usermanage")->where($MP_where)->field("id")->select();
                $sales=0;
                $salesnum=0;
                foreach($MP_list as $key=>$val){
                    $sales+=M("ordermanage")->where("o_status>0 and o_status<4 and o_uid=".$val["id"])->sum("o_price");
                    $salesnum+=M("ordermanage")->where("o_uid=".$val["id"])->count();
                }
                $MP_sql["u_sales"]=$sales;
                $MP_sql["u_salesnum"]=$salesnum;
                //处理数据
                $MP_sql["u_nick"]=empty($MP_sql["u_nick"]) ? $MP_sql["u_mobile"]:$MP_sql["u_nick"];
                if($MP_sql["u_level"]==1){
                    $MP_sql["u_level"]="总代理";
                }
                else if($MP_sql["u_level"]==2){
                    $MP_sql["u_level"]="股东";
                }
                else if($MP_sql["u_level"]==3){
                    $MP_sql["u_level"]="爱心会员";
                }
                else{
                    $MP_sql["u_level"]="普通用户";
                }
                $this->u_level=$MP_sql["u_level"];
                $this->assign("u",$MP_sql);
            }
            $this->display();
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function MyEwm(){
        $u_code=I('get.u_code');
        $MP_sql=M("usermanage")->where("u_code='{$u_code}'")->field("u_nick,u_mobile,u_level,u_ewm,u_image,u_code")->find();
        $bginfo=M('ewmbgmanage')->where("id=1")->find();
        $imgbg='.'.$bginfo['bg'];
        $imgbg=imagecreatefromstring(file_get_contents($imgbg));
        $QR=$MP_sql['u_ewm'];
        $QR=imagecreatefromstring(file_get_contents($QR));
        $QR_width=imagesx($QR);
        $QR_height=imagesy($QR);
        $QR_img_width=$bginfo['e_s_w'];
        $QR_img_height=$bginfo['e_s_h'];
        imagecopyresampled($imgbg,$QR,$bginfo['e_x'],$bginfo['e_y'],$bginfo['b_x'],$bginfo['b_y'],$QR_img_width,$QR_img_height,$bginfo['e_w'],$bginfo['e_h']);
        $path=explode("/",$MP_sql["u_ewm"]);
        $QR_img=imagepng($imgbg,"./".$path[3]."/".$path[4]."/BG".$path[5]);
        $this->qr_img="/".$path[3]."/".$path[4]."/BG".$path[5];
       $this->display();


    }
    public function MyInfo(){
        $id=$this->islogin();
        if($id){
            $MP_sql=M("usermanage")->where("id=".$id)->field("u_nick,u_mobile,u_account,u_image")->find();
            if($MP_sql){
                $MP_sql["u_nick"]=empty($MP_sql["u_nick"]) ? $MP_sql["u_mobile"]:$MP_sql["u_nick"];
                $this->assign("u",$MP_sql);
            }
            $this->display();
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function MyEdit(){
        $id=$this->islogin();
        if($id){
            $MP_data=I("post.");
            $MP_sql=M("usermanage")->where("id=".$id)->data($MP_data)->save();
            if($MP_sql){
                echo 1;
            }
            else{
                echo 0;
            }
        }
    }
    public function MyPass(){
        $id=$this->islogin();
        if($id){
            $MP_data=I("post.");
            if($MP_data) {
                $MP_data["u_pass"] = md5pass($MP_data["u_pass"]);
                $MP_check = M("usermanage")->where("id={$id} and u_pass='{$MP_data["u_pass"]}'")->count();
                if ($MP_check) {
                    $n_pass = md5pass($MP_data["n_pass"]);
                    session("login", null);
                    session("u_nick", null);
                    $MP_sql = M("usermanage")->where("id=" . $id)->setField("u_pass", $n_pass);
                    echo 1;
                } else {
                    echo 2;
                }
            }
            else{
                $this->display();
            }
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function MyAddress(){
        $id=$this->islogin();
        if($id){
            if(IS_GET){
                $this->hid=I('get.hid');
                $this->gid1=I('get.MP_gids');
                if(I('get.hid')==2){
                    $MP_gids=I('get.MP_gids');
                    $MP_gids= explode("_",	$MP_gids);
                    $MP_gids=array_filter($MP_gids);
                    $this->assign('gids',$MP_gids);
                }else{
                    $this->MP_gids=I('get.MP_gids');
                    //20160919
                    $this->ginfo=iconv('GB2312', 'UTF-8', $_GET["ginfo"]);
                    $this->num=I('get.num');
                    // 20160919
                }
            }

            $MP_sql=M("addressmanage")->where("a_uid=".$id)->select();
            if($MP_sql){
                $this->assign("a_list",$MP_sql);
            }
            $this->display();
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function MyAddressAdd(){
        $id=$this->islogin();
        if($id){
            $MP_data=I("post.");
            if($MP_data){
                $MP_data["a_provice"]=M("region")->where("region_id=".$MP_data["a_provice"])->getField("region_name");
                $MP_data["a_city"]=M("region")->where("region_id=".$MP_data["a_city"])->getField("region_name");
                $MP_data["a_county"]=M("region")->where("region_id=".$MP_data["a_county"])->getField("region_name");
                $MP_data["a_uid"]=$id;
                $default= $MP_data["a_default"];
                if(empty($default)){
                    $MP_data["a_default"]=0;
                }else{
                    $MP_data["a_default"]=1;
                    $map['a_uid']=$id;
                    M('addressmanage')->where($map)->save(array('a_default'=>0));
                }
                if($MP_data['a_mobile']==""){
                    $info['status']=4;
                    $this->ajaxReturn($info);
                }
                $MP_sql=M("addressmanage")->data($MP_data)->add();

                if($MP_sql){
                    if(isset($MP_data['hid'])){
                        $this->hid=$MP_data['hid'];
                        $info['aid']=$MP_sql;
                        $info['status']=3;
                        $this->ajaxReturn($info);
                    }else{
                        $info['status']=1;
                        $this->ajaxReturn($info);
                    }
                }
                else{
                    $info['status']=0;
                    $this->ajaxReturn($info);

                }
            }
            else {
                if(IS_GET){
                    $this->hid=I('get.hid');
                    if(I('get.hid')==2){
                        $MP_gids=I('get.gids');
                        $MP_gids= explode("_",$MP_gids);
                        $MP_gids=array_filter($MP_gids);
                        $this->assign('gids',$MP_gids);
                    }else{
                        $this->MP_gids=I('get.gids');
                        //20160919
                        $this->num=I('get.num');
                        $this->ginfo=I('get.ginfo');
                        //20160919
                    }
                }
                $MP_provice = M("region")->where("parent_id=1")->field("region_id,region_name")->select();
                $this->assign("provice", $MP_provice);
                $this->display();
            }
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function MyAddressDel(){
        $id=$this->islogin();
        if($id){
            $aid=I("post.id");
            if($aid){
                $MP_sql=M("addressmanage")->where("id=".$aid)->delete();
                if($MP_sql){
                    echo 1;
                }
                else{
                    echo 0;
                }
            }
            else{
                echo 0;
            }
        }
    }
    public function MyAddressEdit(){
        $id=$this->islogin();
        if($id){
            $MP_data=I("post.");
            if($MP_data){
                foreach ($MP_data as $key=>$val){
                    if(!empty($val)){
                        $MP_save[$key]=$val;
                    }
                }
                if(isset($MP_save["a_provice"])) {
                    $MP_save["a_provice"] = M("region")->where("region_id=" . $MP_data["a_provice"])->getField("region_name");
                }
                if(isset($MP_save["a_city"])) {
                    $MP_save["a_city"] = M("region")->where("region_id=" . $MP_data["a_city"])->getField("region_name");
                }
                if(isset($MP_save["a_county"])) {
                    $MP_save["a_county"] = M("region")->where("region_id=" . $MP_data["a_county"])->getField("region_name");
                }
                $default= $MP_save["a_default"];
                if(empty($default)){
                    $MP_data["a_default"]=0;
                }else{
                    $MP_data["a_default"]=1;
                    $map['a_uid']=$id;
                    $map['id']=array('not in',$MP_data["id"]);
                    M('addressmanage')->where($map)->save(array('a_default'=>0));
                }
                 if($MP_data['a_mobile']==""){
                   echo 4;
                }
                $MP_sql=M("addressmanage")->where("id=".$MP_data["id"])->data($MP_save)->save();
                if($MP_sql){
                    echo 1;
                }
                else{
                    echo 0;
                }
            }
            else {
                $aid = I("get.id");
                $MP_address = M("addressmanage")->where("id=" . $aid)->find();
                $this->assign("a", $MP_address);
                $MP_provice = M("region")->where("parent_id=1")->field("region_id,region_name")->select();
                $this->assign("provice", $MP_provice);
                $this->display();
            }
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function MyTg(){
        $id=$this->islogin();
        if($id){
            $MP_sql=M("usermanage")->where("id=".$id)->field("u_level,u_rose,u_isrose")->find();
            if($MP_sql){
                //分权限统计销售额，销售量
                if($MP_sql["u_isrose"]==1 && $MP_sql["u_level"]==1){
                    $MP_where["u_rose"]=$id;
                }
                else{
                    $MP_where["u_thea|u_theb|u_thec"]=$id;
                }
                $MP_list=M("usermanage")->where($MP_where)->field("id")->select();
                $totalsales=0;
                $onsales=0;
                $unsales=0;
                $onorder=0;
                $unorder=0;
                foreach($MP_list as $key=>$val){
                    $totalsales+=M("ordermanage")->where("o_status>0 and o_uid=".$val["id"])->sum("o_price");
                    $onsales+=M("ordermanage")->where("o_status=3 and o_uid=".$val["id"])->sum("o_price");
                    $unsales+=M("ordermanage")->where("o_status<3 and o_status>0 and o_uid=".$val["id"])->sum("o_price");
                    $onorder+=M("ordermanage")->where("o_status>0 and o_uid=".$val["id"])->count();
                    $unorder+=M("ordermanage")->where("o_status=0 and o_uid=".$val["id"])->count();
                }
                //处理数据
                $MP_info=array(
                    "totalsales"=>$totalsales,
                    "onsales"=>$onsales,
                    "unsales"=>$unsales,
                    "onorder"=>$onorder,
                    "unorder"=>$unorder
                );
                $this->assign("x",$MP_info);
            }
            $this->display();
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function MyCf(){
        $id=$this->islogin();
        if($id){
            $MP_money=M("usermanage")->where("id=".$id)->getField("u_commission");//yongjin
            $this->assign("u_money",$MP_money);
            $MP_finance=M("financemanage")->where("f_uid={$id} and f_status=1")->sum("f_price");
            if(!isset($MP_finance)){
                $MP_finance=0;
            }
            $list_finance=M("financemanage")->where("f_uid={$id} and f_status=1 and f_types<>5" )->order("f_datetime desc")->select();
            $list_finance_0=M("financemanage")->where("f_uid={$id} and f_status=0  and f_types<>5" )->order("f_datetime desc")->select();
            $list_finance_1=M("financemanage")->where("f_uid={$id} and f_status=-1  and f_types<>5" )->order("f_datetime desc")->select();

            $this->assign("list",$list_finance);
            $this->assign("list_0",$list_finance_0);
            $this->assign("list_1",$list_finance_1);
            $this->assign("u_price",$MP_finance);
            $this->display();
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function MyMoney(){
        $id=$this->islogin();
        if($id){
            $MP_money=M("usermanage")->where("id=".$id)->getField("u_money");
            $this->assign("u_money",$MP_money);
            $MP_finance=M("financemanage")->where("f_uid={$id} and (f_types=1 or f_types=2)")->order("id desc")->limit(10)->select();
            $this->assign("list",$MP_finance);
            $this->display();
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function MyFx(){
        $id=$this->islogin();
        if($id){
            $num=M('fordermanage')->where('f_uid='.$id)->count();
            $this->num=$num;
            $this->display();
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function MyXse(){
        $id=$this->islogin();
        if($id){
            $MP_sql=M("usermanage")->where("id=".$id)->field("u_level,u_rose,u_isrose")->find();
            if($MP_sql){
                //分权限统计销售额，销售量
                if($MP_sql["u_isrose"]==1 && $MP_sql["u_level"]==1){
                    $MP_where["u_rose"]=$id;
                }
                else{
                    $MP_where["u_thea|u_theb|u_thec"]=$id;
                }
                $MP_list=M("usermanage")->where($MP_where)->field("id")->select();
                $totalsales=0;
                $onsales=0;
                $unsales=0;
                $order=0;
                $sales=0;
                $tdsales=0;
                $nopay=0;
                $st= strtotime(date('Y-m-d'));
                $et=strtotime(date('Y-m-d',strtotime('+1 day')));
                foreach($MP_list as $key=>$val){
                    //未付款
                    $nopay+=M("ordermanage")->where("o_status=0 and o_uid=".$val["id"])->sum("o_price");
                    //已付款未完成
                    $unsales+=M("ordermanage")->where(" o_status<3 and o_status>0 and o_uid=".$val["id"])->sum("o_price");
                    //已完成
                    $onsales+=M("ordermanage")->where("o_status=3 and o_uid=".$val["id"])->sum("o_price");
                    //总收入
                    $totalsales+=M("ordermanage")->where("o_status>0 and o_status<4  and o_uid=".$val["id"])->sum("o_price");
                    //今天到账
                    $tdsales+=M("ordermanage")->where("o_paytime<{$et} and o_paytime>{$st} and o_status=1 and o_uid=".$val["id"])->sum("o_price");

                    //今日订单数
                    $order+=M("ordermanage")->where("o_paytime<{$et} and o_paytime>{$st} and o_uid=".$val["id"])->count();
                    //今日收入
                    $sales+=M("ordermanage")->where("o_paytime<{$et} and o_paytime>{$st} and o_status=1 and o_uid=".$val["id"])->sum("o_price");
                }
                //处理数据
                $MP_info=array(
                    "totalsales"=>$totalsales,
                    "onsales"=>$onsales,
                    "unsales"=>$unsales,
                    "order"=>$order,
                    "sales"=>$sales,
                    "tdsales"=>$tdsales,
                    'nopay'=>$nopay
                );
                $this->assign("x",$MP_info);
            }
            $this->display();
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function MyXsl(){
        $id=$this->islogin();
        if($id){
            $MP_sql=M("usermanage")->where("id=".$id)->field("u_level,u_rose,u_isrose")->find();
            if($MP_sql){
                //分权限统计销售额，销售量
                if($MP_sql["u_isrose"]==1 && $MP_sql["u_level"]==1){
                    $MP_where["u_rose"]=$id;
                }
                else{
                    $MP_where["u_thea|u_theb|u_thec"]=$id;
                }
                $MP_list=M("usermanage")->where($MP_where)->field("id")->select();
                $totalorder=0;
                $onorder=0;
                $unorder=0;
                $order=0;
                $datetime=date("Y-m-d",time());
                foreach($MP_list as $key=>$val){
                    $totalorder+=M("ordermanage")->where("o_uid=".$val["id"])->count();
                    $onorder+=M("ordermanage")->where("o_datetime='{$datetime}' and o_status=3 and o_uid=".$val["id"])->count();
                    $unorder+=M("ordermanage")->where("o_datetime='{$datetime}' and o_status<3 and o_uid=".$val["id"])->count();
                    $order+=M("ordermanage")->where("o_datetime='{$datetime}' and o_uid=".$val["id"])->count();
                }
                //处理数据
                $MP_info=array(
                    "totalorder"=>$totalorder,
                    "onorder"=>$onorder,
                    "unorder"=>$unorder,
                    "order"=>$order
                );
                $this->assign("x",$MP_info);
            }
            $this->display();
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function ToMoney(){
        $id=$this->islogin();
        if($id){
            $MP_data=I("post.");
            if($MP_data["f_types"]==1) {
                $MP_money = M("usermanage")->where("id=" . $id)->getField("u_money");
                if($MP_money>=$MP_data["f_price"]) {
                    $MP_arr = array(
                        "f_price" => $MP_data["f_price"],
                        "f_uid" => $id,
                        "f_datetime" => time(),
                    );
                    if ($MP_data["types"] == 1) {
                        $MP_arr["f_text"] = "余额微信提现";
                        $MP_arr["f_types"] = 1;
                    } else if ($MP_data["types"] == 2) {
                        $MP_arr["f_text"] = "余额银行卡提现";
                        $MP_arr["f_types"] = 2;
                        $MP_arr["f_bname"]=$MP_data["f_bname"];
                        $MP_arr["f_bnumber"]=$MP_data["f_bnumber"];
                        $MP_arr["f_bkname"]=$MP_data["f_bkname"];
                        $MP_arr["f_bkadd"]=$MP_data["f_bkadd"];
                    }


                    M("financemanage")->data($MP_arr)->add();
                    M("usermanage")->where("id=".$id)->setDec("u_money",$MP_data["f_price"]);

                    echo 1;
                }
                else{
                    echo 0;
                }
            }
            else if($MP_data["f_types"]==3){
                $c_code=$MP_data['c_code'];
                if($c_code==session('L_code')){
                    $MP_money = M("usermanage")->where("id=" . $id)->getField("u_commission");
                    if($MP_money>=$MP_data["f_price"]) {
                        $MP_arr = array(
                            "f_price" => $MP_data["f_price"],
                            "f_uid" => $id,
                            "f_datetime" => time(),
                        );
                        if ($MP_data["types"] == 1) {
                            $MP_arr["f_text"] = "佣金微信提现";
                            $MP_arr["f_types"] = 3;
                        } else if ($MP_data["types"] == 2) {
                            $MP_arr["f_text"] = "佣金银行卡提现";
                            $MP_arr["f_types"] = 4;
                            $MP_arr["f_bname"]=$MP_data["f_bname"];
                            $MP_arr["f_bnumber"]=$MP_data["f_bnumber"];
                            $MP_arr["f_bkname"]=$MP_data["f_bkname"];
                            $MP_arr["f_bkadd"]=$MP_data["f_bkadd"];
                        }
                        M("financemanage")->data($MP_arr)->add();
                        M("usermanage")->where("id=".$id)->setDec("u_commission",$MP_data["f_price"]);
                        echo 1;
                    }
                    else{
                        echo 0;
                    }
                }else{
                    echo 3;
                }

            }
        }
        else{
            $this->redirect("Index/login","请登录");
        }
    }
    public function MyBank(){
        $MP_data=I("get.");
        $MP_data['f_datetime']=time();
        $c_code=$MP_data['c_code'];
        if($c_code==session('L_code')){
            $this->assign("f",$MP_data);
            $this->display();
        }else{
            echo"<script>alert('验证码错误')</script>";
            redirect('My/MyCf');
        }

    }

    public function WxSq(){
        $id=$this->islogin();
        $i_data=I("get.");
        $M_AppID = C('WX_APPID');
        $M_AppSecret = C('WX_APPSECRET');
        if($i_data["code"]){
            $code=$i_data["code"];
            $state=$i_data["state"];
            $M_check=session("M_check");
            if($state==$M_check && $code!="") {
                $get_token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$M_AppID}&secret={$M_AppSecret}&code={$code}&grant_type=authorization_code";
                $wx_info = curl_get($get_token_url);
                $access_token = $wx_info['access_token'];
                $openid = $wx_info['openid'];
                $get_user_info_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openid}&lang=zh_CN";
                $wx_user_info = curl_get($get_user_info_url);
                $data['u_wxopenid']=$wx_user_info["openid"];
            $re=M('usermanage')->where('id='.$id)->setField($data);
            session("M_check",null);
                if($re){
                    $this->success('微信授权成功',U('My/Index'));
                }else{
                    $this->error('微信授权成功',U('My/Index'));
                }

            }
        }
        else {
            $http="http://www.xyclsw.com";
            $url=urlencode($http.U("Home/My/WxSq"));
            $M_check = rand_zifu(3, 32);
            session("M_check",$M_check);
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$M_AppID}&redirect_uri={$url}&response_type=code&scope=snsapi_userinfo&state={$M_check}#wechat_redirect";
           header("Location:" . $url);
        }

    }



}