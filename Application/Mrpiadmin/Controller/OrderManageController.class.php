<?php
namespace Mrpiadmin\Controller;
use Think\Controller;
class OrderManageController extends ConController {
    public function index(){
        //$this->display('login');
    }
    public function OrderList(){
        $MP_data=I("get.");
        if(!empty($MP_data)){
            //处理状态条件
            if($MP_data["o_status"]!=""){
                $ListWhere["o_status"]=$MP_data["o_status"];
                $map['o_status']=$MP_data["o_status"];
            }
            //处理时间条件]

            if(empty($MP_data["starttime"]) && $MP_data["endtime"]){

                $times=NOW_TIME;
                $endtime=str_format_time($MP_data["endtime"],2);
                $ListWhere["o_datetime"]=array("BETWEEN","{$times},{$endtime}");
                $map['endtime']=$MP_data["endtime"];
            }
            else if(empty($MP_data["endtime"]) && $MP_data["starttime"]){
                $times=NOW_TIME;
                $starttime=str_format_time($MP_data["starttime"],1);
                $ListWhere["o_datetime"]=array("BETWEEN","{$starttime},{$times}");
                $map['starttime']=$MP_data["starttime"];
            }
            else if($MP_data["starttime"] && $MP_data["endtime"]){
                $endtime=str_format_time($MP_data["endtime"],2);
                $starttime=str_format_time($MP_data["starttime"],1);
                $ListWhere["o_datetime"]=array("BETWEEN","{$starttime},{$endtime}");
                $map['starttime']=$MP_data["starttime"];
                $map['endtime']=$MP_data["endtime"];
            }
            //处理关键字条件
            if($MP_data["search_key"]!=""){
                $ListWhere["o_sn"]=array("like","%".$MP_data["search_key"]."%");
                $map['search_key']=$MP_data["search_key"];
            }
        }
        else{
            $ListWhere="";
        }
        $User = M('ordermanage'); // 实例化User对象
        $count      = $User->where($ListWhere)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        if(!empty($MP_data)){
            $Page->parameter=$map;
            $this->map=$map;
        }
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $User->where($ListWhere)->order('id desc,o_status desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        //处理列表显示数据
        $this->price=M('ordermanage')->where($ListWhere)->sum('o_price');

        if($MP_data["o_status"]==''){
            //未付款
            $ListWhere["o_status"]=0;
            $this->notpayprice=M('ordermanage')->where($ListWhere)->sum('o_price');
            $this->notpaycount=M('ordermanage')->where($ListWhere)->count();
            //已付款
            $ListWhere["o_status"]=array('gt','0');
            $this->payprice=M('ordermanage')->where($ListWhere)->sum('o_price');
            $this->paycount=M('ordermanage')->where($ListWhere)->count();

        }

        //订单量
        $this->count=$count;
        foreach($list as $key=>$val){
            $val["o_ginfo"]=unserialize($val["o_ginfo"]);
            foreach($val["o_ginfo"] as $k=>$v){
                $v["gname"]=M("goodsmanage")->where("id=".$v["gid"])->getField("g_title");
                $val["o_ginfo"][$k]=$v;
            }
            $val["address"]=M("addressmanage")->where("id=".$val["o_aid"])->find();
            if($val["o_eid"]==0){
                $val["o_eid_name"]="无需物流快递";
            }
            else {
                $val["o_eid_name"] = M("expressmanage")->where("id=" . $val["o_eid"])->getField("e_name");
            }
            $val["o_uid"]=M("usermanage")->where("id=".$val["o_uid"])->getField("u_mobile");
            $order_list[$key]=$val;
            $thcount=M('refund')->where('r_oid='.$val['id'])->count();
            $order_list[$key]['thcount']=$thcount;

        }
        //总和

        $this->enumber=M('expressmanage')->field('id,e_name')->select();
        $this->assign('order_list',$order_list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->display(); // 输出模板
    }
    public function exportE(){
        $MP_data=I("get.");
        if(!empty($MP_data)){
            //处理状态条件
            if($MP_data["o_status"]!=""){
                $ListWhere["o_status"]=$MP_data["o_status"];

            }
            //处理时间条件]
            if(empty($MP_data["starttime"]) && $MP_data["endtime"]){
                $times=NOW_TIME;
                $endtime=str_format_time($MP_data["endtime"],2);
                $ListWhere["o_datetime"]=array("BETWEEN","{$times},{$endtime}");
            }
            else if(empty($MP_data["endtime"]) && $MP_data["starttime"]){
                $times=NOW_TIME;
                $starttime=str_format_time($MP_data["starttime"],1);
                $ListWhere["o_datetime"]=array("BETWEEN","{$starttime},{$times}");
            }
            else if($MP_data["starttime"] && $MP_data["endtime"]){
                $endtime=str_format_time($MP_data["endtime"],2);
                $starttime=str_format_time($MP_data["starttime"],1);
                $ListWhere["o_datetime"]=array("BETWEEN","{$starttime},{$endtime}");
            }
            //处理关键字条件
            if($MP_data["search_key"]!=""){
                $ListWhere["o_sn"]=array("like","%".$MP_data["search_key"]."%");
                $map['search_key']=$MP_data["search_key"];
            }
        }
        else{
            $ListWhere="";
        }

        $order = M('ordermanage'); // 实例化User对象
        $list=$order->where($ListWhere)->select();
        foreach($list as $key=>$val){
            $val["o_ginfo"]=unserialize($val["o_ginfo"]);
            foreach($val["o_ginfo"] as $k=>$v){
                $v["gname"]=M("goodsmanage")->where("id=".$v["gid"])->getField("g_title");
                $v["gprice"]=M("goodsmanage")->where("id=".$v["gid"])->getField("g_vipprice");
                $info=$v['gname']." （数量：".$v['gnum'].",单价：".$v["gprice"].",".$v['ginfo']."）"."；\r\n";
                $val["o_ginfo"]= $info;
            }

            $val["address"]=M("addressmanage")->where("id=".$val["o_aid"])->find();
            if($val["o_eid"]==0){
                $val["o_eid_name"]="无需物流快递";
            }
            else {
                $val["o_eid_name"] = M("expressmanage")->where("id=" . $val["o_eid"])->getField("e_name");
            }
            $val["o_uid"]=M("usermanage")->where("id=".$val["o_uid"])->getField("u_mobile");
            $order_list[$key]=$val;
        }
        if(count($order_list)==0){
            echo "无数据 <a href='".U('OrderManage/OrderList',$ListWhere)."'>返回</a>";
            die;
        }
        Vendor('excel.PHPExcel');
        $phpExcel=new \PHPExcel();
        Vendor('excel.PHPExcel.IOFactor');
        Vendor('excel.PHPExcel.style');
        $phpExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
        $phpExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $phpExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $phpExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $phpExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $phpExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $phpExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $phpExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $phpExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $phpExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $phpExcel->getActiveSheet()->getStyle('A1:J1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $letter=array('A','B','C','D','E','F','G','H','I','J');
        $tableheader=array('订单号','商品','地址','	收货人','订单金额','订单时间','付款时间','快递信息','用户','状态');
        for($i = 0;$i < count($tableheader);$i++) {
            $phpExcel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
        }
        $count = count($order_list)+1;

        for ($i = 2; $i <= $count; $i++) {
            $phpExcel->getActiveSheet()->getColumnDimension("A".$i)->setAutoSize(true);
            $phpExcel->getActiveSheet()->getColumnDimension("B".$i)->setAutoSize(true);
            $phpExcel->getActiveSheet()->getColumnDimension("C".$i)->setAutoSize(true);
            $phpExcel->getActiveSheet()->getColumnDimension("D".$i)->setAutoSize(true);
            $phpExcel->getActiveSheet()->getColumnDimension("E".$i)->setAutoSize(true);
            $phpExcel->getActiveSheet()->getColumnDimension("F".$i)->setAutoSize(true);
            $phpExcel->getActiveSheet()->getColumnDimension("G".$i)->setAutoSize(true);
            $phpExcel->getActiveSheet()->getColumnDimension("H".$i)->setAutoSize(true);
            $phpExcel->getActiveSheet()->getColumnDimension("I".$i)->setAutoSize(true);
            $phpExcel->getActiveSheet()->getColumnDimension("J".$i)->setAutoSize(true);
            $phpExcel->getActiveSheet()->setCellValue('A' . $i, $order_list[$i-2]['o_sn']);
            $phpExcel->getActiveSheet()->setCellValue('B' . $i, $order_list[$i-2]['o_ginfo']);
            $phpExcel->getActiveSheet()->setCellValue('C' . $i, $order_list[$i-2]['address']['a_provice'].$order_list[$i-2]['address']['a_city'].$order_list[$i-2]['address']['a_county'].$order_list[$i-2]['address']['a_address']);
            $phpExcel->getActiveSheet()->setCellValue('D' . $i, $order_list[$i-2]['address']['a_consignee']."：".$order_list[$i-2]['address']['a_mobile']);
            $phpExcel->getActiveSheet()->setCellValue('E' . $i, $order_list[$i-2]['o_price']);
            $phpExcel->getActiveSheet()->setCellValue('F' . $i, date("Y-m-d H:i:s",$order_list[$i-2]['o_datetime']));
            $phpExcel->getActiveSheet()->setCellValue('G' . $i, date("Y-m-d H:i:s",$order_list[$i-2]['o_paytime']));
            $phpExcel->getActiveSheet()->setCellValue('H' . $i, $order_list[$i-2]['o_eid_name']."：".$order_list[$i-2]['o_enumber']);
            $phpExcel->getActiveSheet()->setCellValue('I' . $i, $order_list[$i-2]["o_uid"]);
            $phpExcel->getActiveSheet()->getStyle('I'.$i)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
            $phpExcel->getActiveSheet()->setCellValue('J' . $i, get_status($order_list[$i-2]["o_status"]));
        }

        header("Content-Transfer-Encoding:binary");
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition: attachment; filename="订单' . time() . '.xls"');
        $phpExcel->createSheet();
        $objWriter = \PHPExcel_IOFactory::createWriter($phpExcel, 'Excel5');
        $objWriter->save('php://output');
    }


    public function OrderList10(){
        $MP_data=I("post.");
         $times=NOW_TIME-5*24*60*60;
        $ListWhere['o_status']=2;
        $ListWhere['o_paytime']=array('lt',$times);
//
        $User = M('ordermanage'); // 实例化User对象
        $count      = $User->where($ListWhere)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $User->where($ListWhere)->order('o_paytime asc')->limit($Page->firstRow.','.$Page->listRows)->select();
        //处理列表显示数据
        $this->price=M('ordermanage')->where($ListWhere)->sum('o_price');

        if($MP_data["o_status"]==''){
            //未付款
            $ListWhere["o_status"]=0;
            $this->notpayprice=M('ordermanage')->where($ListWhere)->sum('o_price');
            $this->notpaycount=M('ordermanage')->where($ListWhere)->count();
            //已付款
            $ListWhere["o_status"]=array('gt','0');
            $this->payprice=M('ordermanage')->where($ListWhere)->sum('o_price');
            $this->paycount=M('ordermanage')->where($ListWhere)->count();

        }

        //订单量
        $this->count=$count;
        foreach($list as $key=>$val){
            $val["o_ginfo"]=unserialize($val["o_ginfo"]);
            foreach($val["o_ginfo"] as $k=>$v){
                $v["gname"]=M("goodsmanage")->where("id=".$v["gid"])->getField("g_title");
                $val["o_ginfo"][$k]=$v;
            }
            $val["address"]=M("addressmanage")->where("id=".$val["o_aid"])->find();
            if($val["o_eid"]==0){
                $val["o_eid_name"]="无需物流快递";
            }
            else {
                $val["o_eid_name"] = M("expressmanage")->where("id=" . $val["o_eid"])->getField("e_name");
            }
            $val["o_uid"]=M("usermanage")->where("id=".$val["o_uid"])->getField("u_mobile");
            $order_list[$key]=$val;
        }
        //总和

        $this->enumber=M('expressmanage')->field('id,e_name')->select();
        $this->assign('order_list',$order_list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->display(); // 输出模板
    }
    public function OrderDel(){
        $MP_data=I("post.");
        if($MP_data){
            $id=$MP_data["id"];
            $MP_sql=M("ordermanage")->where("id=".$id)->delete();
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
    public function OrderState(){
        $MP_data=I("post.");
        if($MP_data){
            if($MP_data["state"]==1){
                $MP_sql=M("ordermanage")->where("id=".$MP_data["id"])->setField(array("o_status"=>$MP_data["state"],"o_paytime"=>NOW_TIME));

            }else if($MP_data["state"]==6){//退款执行操作

            }else{
                $MP_sql=M("ordermanage")->where("id=".$MP_data["id"])->setField("o_status",$MP_data["state"]);
            }
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
    public function addEnumber(){
        $MP_data=I("post.");
        if($MP_data){
            if(M('ordermanage')->where('id='.$MP_data['id'])->setField($MP_data)){
                echo 1;
            }else{
                echo 0;
            }

        }else{
            $this->error("非法操作");
        }
    }
    public function OrderStates(){
        $MP_data=I("post.");
        if(count( $MP_data['id'])>0){
            foreach($MP_data['id'] as $k=>$v){
                $oid=$v;
                $MP_sql=M("ordermanage")->where("id=".$oid)->setField("o_status",3);
            //$MP_sql=true;
                if($MP_sql){
                    $MP_order=M("ordermanage")->where("id=".$oid)->field("o_uid,o_ginfo,o_sn,o_price")->find();
                    $total_fee=$MP_order["o_price"];
                    //奖励部分
                    $data=M("usermanage")->where("id=".$MP_order["o_uid"])->field("u_thea,u_theb,u_thec,u_rose,u_isrose")->find();
                    //如果该用户不是总代，并且是推广用户，则进行奖励
                    if($data["u_isrose"]==0 && $data["u_thec"]!=0) {
                        $sys_scale=M("sysmanage")->where("id=1")->find();
                        if ($data["u_thea"] != 0) {
                            $check_dis=M("usermanage")->where("id=".$data["u_thea"])->getField("u_level");
                            if($check_dis>0) {
                                $money_1 = 0;
                                //获取产品三级分销比例，进行判断赋值
                                if ($money_1 == 0) {
                                    $goodslist = unserialize($MP_order["o_ginfo"]);
                                    foreach ($goodslist as $key => $val) {
                                        $goodsinfo = M("goodsmanage")->where("id=" . $val["gid"])->field("g_scalea,g_vipprice")->find();
                                        $goods_a = unserialize($goodsinfo["g_scalea"]);
                                        $goods_m = $goodsinfo["g_vipprice"];
                                        if ($goods_a["types"] == 1 && $goods_a["scale"] != 0 && $goods_a["scale"] != "") {
                                            $money_1 += ($goods_m * $goods_a["scale"] * $val["gnum"]);
                                        } else if ($goods_a["types"] == 2 && $goods_a["scale"] != 0 && $goods_a["scale"] != "") {
                                            $money_1 += ($goods_a["scale"] * $val["gnum"]);
                                        }
                                    }
                                }
                                //获取用户分销比例，进行判断赋值
                                if ($money_1 == 0) {
                                    $scale = M("usermanage")->where("id=" . $data["u_thea"])->getField("u_scale");
                                    if ($scale != "") {
                                        $money_1 = $total_fee * $scale;
                                    }
                                }
                                //获取系统三级分销比例，进行判断赋值
                                if ($money_1 == 0) {
                                    $sys_a = unserialize($sys_scale["s_scalec"]);
                                    if ($sys_a["types"] == 1 && $sys_a["scale"] != 0 && $sys_a["scale"] != "") {
                                        $money_1 = $total_fee * $sys_a["scale"];
                                    } else if ($sys_a["types"] == 2 && $sys_a["scale"] != 0 && $sys_a["scale"] != "") {
                                        $money_1 = $sys_a["scale"];
                                    } else {
                                        $money_1 = $total_fee * 0.1;
                                    }
                                }
                                //如果有分销金额产生，则执行分销
                                if ($money_1 != 0) {
                                    M("usermanage")->where("id=" . $data["u_thea"])->setInc("u_commission", $money_1);
                                    $finance = array(
                                        "f_types" => 5,
                                        "f_text" => "订单" . $MP_order["o_sn"] . "奖励",
                                        "f_price" => $money_1,
                                        "f_uid" => $data["u_thea"],
                                        "f_datetime" => time()
                                    );
                                    M("financemanage")->data($finance)->add();
                                }
                                //进行用户分代资格判断，叠加返佣
                                $trose = M("usermanage")->where("id=" . $data["u_thea"])->getField("u_level");
                                if ($trose == 2) {
                                    $sys_t = unserialize($sys_scale["s_trose"]);
                                    if ($sys_t["types"] == 1 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                        $tmoney = $total_fee * $sys_t["scale"];
                                    } else if ($sys_t["types"] == 2 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                        $tmoney = $sys_t["scale"];
                                    } else {
                                        $tmoney = $total_fee * 0.03;
                                    }
                                    if ($tmoney != 0 && $tmoney != "") {
                                        M("usermanage")->where("id=" . $data["u_thea"])->setInc("u_commission", $tmoney);
                                        $finance = array(
                                            "f_types" => 5,
                                            "f_text" => "分代：订单" . $MP_order["o_sn"] . "奖励",
                                            "f_price" => $tmoney,
                                            "f_uid" => $data["u_thea"],
                                            "f_datetime" => time()
                                        );
                                        M("financemanage")->data($finance)->add();
                                    }
                                }
                            }
                        }
                        if ($data["u_theb"] != 0) {
                            $check_dis=M("usermanage")->where("id=".$data["u_theb"])->getField("u_level");
                            if($check_dis>0) {
                                $money_2 = 0;
                                //获取产品二级分销比例，进行判断赋值
                                if ($money_2 == 0) {
                                    $goodslist = unserialize($MP_order["o_ginfo"]);
                                    foreach ($goodslist as $key => $val) {
                                        $goodsinfo = M("goodsmanage")->where("id=" . $val["gid"])->field("g_scaleb,g_vipprice")->find();
                                        $goods_b = unserialize($goodsinfo["g_scaleb"]);
                                        $goods_m = $goodsinfo["g_vipprice"];
                                        if ($goods_b["types"] == 1 && $goods_b["scale"] != 0 && $goods_b["scale"] != "") {
                                            $money_2 += ($goods_m * $goods_b["scale"] * $val["gnum"]);
                                        } else if ($goods_b["types"] == 2 && $goods_b["scale"] != 0 && $goods_b["scale"] != "") {
                                            $money_2 += ($goods_b["scale"] * $val["gnum"]);
                                        }
                                    }
                                }
                                //获取用户分销比例，进行判断赋值
                                if ($money_2 == 0) {
                                    $scale = M("usermanage")->where("id=" . $data["u_theb"])->getField("u_scale");
                                    if ($scale != "") {
                                        $money_2 = $total_fee * $scale;
                                    }
                                }
                                //获取系统二级分销比例，进行判断赋值
                                if ($money_2 == 0) {
                                    $sys_b = unserialize($sys_scale["s_scaleb"]);
                                    if ($sys_b["types"] == 1 && $sys_b["scale"] != 0 && $sys_b["scale"] != "") {
                                        $money_2 = $total_fee * $sys_b["scale"];
                                    } else if ($sys_b["types"] == 2 && $sys_b["scale"] != 0 && $sys_b["scale"] != "") {
                                        $money_2 = $sys_b["scale"];
                                    } else {
                                        $money_2 = $total_fee * 0.2;
                                    }
                                }
                                //如果有分销金额产生，则执行分销
                                if ($money_2 != 0) {
                                    M("usermanage")->where("id=" . $data["u_theb"])->setInc("u_commission", $money_2);
                                    $finance = array(
                                        "f_types" => 5,
                                        "f_text" => "订单" . $MP_order["o_sn"] . "奖励",
                                        "f_price" => $money_2,
                                        "f_uid" => $data["u_theb"],
                                        "f_datetime" => time()
                                    );
                                    M("financemanage")->data($finance)->add();
                                }
                                //进行用户分代资格判断，叠加返佣
                                $trose = M("usermanage")->where("id=" . $data["u_theb"])->getField("u_level");
                                if ($trose == 2) {
                                    $sys_t = unserialize($sys_scale["s_trose"]);
                                    if ($sys_t["types"] == 1 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                        $tmoney = $total_fee * $sys_t["scale"];
                                    } else if ($sys_t["types"] == 2 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                        $tmoney = $sys_t["scale"];
                                    } else {
                                        $tmoney = $total_fee * 0.03;
                                    }
                                    if ($tmoney != 0 && $tmoney != "") {
                                        M("usermanage")->where("id=" . $data["u_theb"])->setInc("u_commission", $tmoney);
                                        $finance = array(
                                            "f_types" => 5,
                                            "f_text" => "分代：订单" . $MP_order["o_sn"] . "奖励",
                                            "f_price" => $tmoney,
                                            "f_uid" => $data["u_theb"],
                                            "f_datetime" =>time()
                                        );
                                        M("financemanage")->data($finance)->add();
                                    }
                                }
                            }
                        }
                        if ($data["u_thec"] != 0) {
                            $check_dis=M("usermanage")->where("id=".$data["u_thec"])->getField("u_level");
                            if($check_dis>0) {
                                $money_3 = 0;
                                //获取产品一级分销比例，进行判断赋值
                                if ($money_3 == 0) {
                                    $goodslist = unserialize($MP_order["o_ginfo"]);
                                    foreach ($goodslist as $key => $val) {
                                        $goodsinfo = M("goodsmanage")->where("id=" . $val["gid"])->field("g_scalec,g_vipprice")->find();
                                        $goods_c = unserialize($goodsinfo["g_scalec"]);
                                        $goods_m = $goodsinfo["g_vipprice"];
                                        if ($goods_c["types"] == 1 && $goods_c["scale"] != 0 && $goods_c["scale"] != "") {
                                            $money_3 += ($goods_m * $goods_c["scale"] * $val["gnum"]);
                                        } else if ($goods_c["types"] == 2 && $goods_c["scale"] != 0 && $goods_c["scale"] != "") {
                                            $money_3 += ($goods_c["scale"] * $val["gnum"]);
                                        }
                                    }
                                }
                                //获取用户分销比例，进行判断赋值
                                if ($money_3 == 0) {
                                    $scale = M("usermanage")->where("id=" . $data["u_thec"])->getField("u_scale");
                                    if ($scale != "") {
                                        $money_3 = $total_fee * $scale;
                                    }
                                }
                                //获取系统一级分销比例，进行判断赋值
                                if ($money_3 == 0) {
                                    $sys_c = unserialize($sys_scale["s_scalec"]);
                                    if ($sys_c["types"] == 1 && $sys_c["scale"] != 0 && $sys_c["scale"] != "") {
                                        $money_3 = $total_fee * $sys_c["scale"];
                                    } else if ($sys_c["types"] == 2 && $sys_c["scale"] != 0 && $sys_c["scale"] != "") {
                                        $money_3 = $sys_c["scale"];
                                    } else {
                                        $money_3 = $total_fee * 0.3;
                                    }
                                }
                                //如果有分销金额产生，则执行分销
                                if ($money_3 != 0) {
                                    M("usermanage")->where("id=" . $data["u_thec"])->setInc("u_commission", $money_3);
                                    $finance = array(
                                        "f_types" => 5,
                                        "f_text" => "订单" . $MP_order["o_sn"] . "奖励",
                                        "f_price" => $money_3,
                                        "f_uid" => $data["u_thec"],
                                        "f_datetime" => time()
                                    );
                                    M("financemanage")->data($finance)->add();
                                }
                                //进行用户分代资格判断，叠加返佣
                                $trose = M("usermanage")->where("id=" . $data["u_thec"])->getField("u_level");
                                if ($trose == 2) {
                                    $sys_t = unserialize($sys_scale["s_trose"]);
                                    if ($sys_t["types"] == 1 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                        $tmoney = $total_fee * $sys_t["scale"];
                                    } else if ($sys_t["types"] == 2 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                        $tmoney = $sys_t["scale"];
                                    } else {
                                        $tmoney = $total_fee * 0.03;
                                    }
                                    if ($tmoney != 0 && $tmoney != "") {
                                        M("usermanage")->where("id=" . $data["u_thec"])->setInc("u_commission", $tmoney);
                                        $finance = array(
                                            "f_types" => 5,
                                            "f_text" => "分代：订单" . $MP_order["o_sn"] . "奖励",
                                            "f_price" => $tmoney,
                                            "f_uid" => $data["u_thec"],
                                            "f_datetime" =>  time()
                                        );
                                        M("financemanage")->data($finance)->add();
                                    }
                                }
                            }
                        }
                    }

                }
                $arr[]=1;

            }
            if(count($arr)<count( $MP_data['id'])){
                echo 1;
            }
            if(count($arr)==count( $MP_data['id'])){
                echo 2;
            }
        }else{
            echo 0;
        }
    }
    public function OrderSure(){
        $oid=I("post.id");
        $MP_sql=M("ordermanage")->where("id=".$oid)->setField("o_status",3);
//            $MP_sql=true;
        if($MP_sql){
            $MP_order=M("ordermanage")->where("id=".$oid)->field("o_uid,o_ginfo,o_sn,o_price")->find();
            $total_fee=$MP_order["o_price"];
            //奖励部分
            $data=M("usermanage")->where("id=".$MP_order["o_uid"])->field("u_thea,u_theb,u_thec,u_rose,u_isrose")->find();
            //如果该用户不是总代，并且是推广用户，则进行奖励
            if($data["u_isrose"]==0 && $data["u_thec"]!=0) {
                $sys_scale=M("sysmanage")->where("id=1")->find();
                if ($data["u_thea"] != 0) {//3ji
                    $check_dis=M("usermanage")->where("id=".$data["u_thea"])->getField("u_level");
                    if($check_dis>0) {
                        $money_1 = 0;
                        //获取产品三级分销比例，进行判断赋值
                        if ($money_1 == 0) {
                            $goodslist = unserialize($MP_order["o_ginfo"]);
                            foreach ($goodslist as $key => $val) {
                                $goodsinfo = M("goodsmanage")->where("id=" . $val["gid"])->field("g_scalea,g_vipprice")->find();
                                $goods_a = unserialize($goodsinfo["g_scalea"]);
                                $goods_m = $goodsinfo["g_vipprice"];
                                if ($goods_a["types"] == 1 && $goods_a["scale"] != 0 && $goods_a["scale"] != "") {
                                    $money_1 += ($goods_m * $goods_a["scale"] * $val["gnum"]);
                                } else if ($goods_a["types"] == 2 && $goods_a["scale"] != 0 && $goods_a["scale"] != "") {
                                    $money_1 += ($goods_a["scale"] * $val["gnum"]);
                                }
                            }
                        }
                        //获取用户分销比例，进行判断赋值
                        if ($money_1 == 0) {
                            $scale = M("usermanage")->where("id=" . $data["u_thea"])->getField("u_scale");
                            if ($scale != "") {
                                $money_1 = $total_fee * $scale;
                            }
                        }
                        //获取系统三级分销比例，进行判断赋值
                        if ($money_1 == 0) {
                            $sys_a = unserialize($sys_scale["s_scalec"]);
                            if ($sys_a["types"] == 1 && $sys_a["scale"] != 0 && $sys_a["scale"] != "") {
                                $money_1 = $total_fee * $sys_a["scale"];
                            } else if ($sys_a["types"] == 2 && $sys_a["scale"] != 0 && $sys_a["scale"] != "") {
                                $money_1 = $sys_a["scale"];
                            } else {
                                $money_1 = $total_fee * 0.1;
                            }
                        }
                        //如果有分销金额产生，则执行分销
                        if ($money_1 != 0) {
                            M("usermanage")->where("id=" . $data["u_thea"])->setInc("u_commission", $money_1);
                            $finance = array(
                                "f_types" => 5,
                                "f_text" => "订单" . $MP_order["o_sn"] . "奖励",
                                "f_price" => $money_1,
                                "f_uid" => $data["u_thea"],
                                "f_datetime" => time()
                            );
                            M("financemanage")->data($finance)->add();
                        }
                        //进行用户分代资格判断，叠加返佣
                        $trose = M("usermanage")->where("id=" . $data["u_thea"])->getField("u_level");
                        if ($trose == 2) {
                            $sys_t = unserialize($sys_scale["s_trose"]);
                            if ($sys_t["types"] == 1 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                $tmoney = $total_fee * $sys_t["scale"];
                            } else if ($sys_t["types"] == 2 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                $tmoney = $sys_t["scale"];
                            } else {
                                $tmoney = $total_fee * 0.03;
                            }
                            if ($tmoney != 0 && $tmoney != "") {
                                M("usermanage")->where("id=" . $data["u_thea"])->setInc("u_commission", $tmoney);
                                $finance = array(
                                    "f_types" => 5,
                                    "f_text" => "分代：订单" . $MP_order["o_sn"] . "奖励",
                                    "f_price" => $tmoney,
                                    "f_uid" => $data["u_thea"],
                                    "f_datetime" => time()
                                );
                                M("financemanage")->data($finance)->add();
                            }
                        }
                    }
                }
                if ($data["u_theb"] != 0) {
                    $check_dis=M("usermanage")->where("id=".$data["u_theb"])->getField("u_level");
                    if($check_dis>0) {
                        $money_2 = 0;
                        //获取产品二级分销比例，进行判断赋值
                        if ($money_2 == 0) {
                            $goodslist = unserialize($MP_order["o_ginfo"]);
                            foreach ($goodslist as $key => $val) {
                                $goodsinfo = M("goodsmanage")->where("id=" . $val["gid"])->field("g_scaleb,g_vipprice")->find();
                                $goods_b = unserialize($goodsinfo["g_scaleb"]);
                                $goods_m = $goodsinfo["g_vipprice"];
                                if ($goods_b["types"] == 1 && $goods_b["scale"] != 0 && $goods_b["scale"] != "") {
                                    $money_2 += ($goods_m * $goods_b["scale"] * $val["gnum"]);
                                } else if ($goods_b["types"] == 2 && $goods_b["scale"] != 0 && $goods_b["scale"] != "") {
                                    $money_2 += ($goods_b["scale"] * $val["gnum"]);
                                }
                            }
                        }
                        //获取用户分销比例，进行判断赋值
                        if ($money_2 == 0) {
                            $scale = M("usermanage")->where("id=" . $data["u_theb"])->getField("u_scale");
                            if ($scale != "") {
                                $money_2 = $total_fee * $scale;
                            }
                        }
                        //获取系统二级分销比例，进行判断赋值
                        if ($money_2 == 0) {
                            $sys_b = unserialize($sys_scale["s_scaleb"]);
                            if ($sys_b["types"] == 1 && $sys_b["scale"] != 0 && $sys_b["scale"] != "") {
                                $money_2 = $total_fee * $sys_b["scale"];
                            } else if ($sys_b["types"] == 2 && $sys_b["scale"] != 0 && $sys_b["scale"] != "") {
                                $money_2 = $sys_b["scale"];
                            } else {
                                $money_2 = $total_fee * 0.2;
                            }
                        }
                        //如果有分销金额产生，则执行分销
                        if ($money_2 != 0) {
                            M("usermanage")->where("id=" . $data["u_theb"])->setInc("u_commission", $money_2);
                            $finance = array(
                                "f_types" => 5,
                                "f_text" => "订单" . $MP_order["o_sn"] . "奖励",
                                "f_price" => $money_2,
                                "f_uid" => $data["u_theb"],
                                "f_datetime" => time()
                            );
                            M("financemanage")->data($finance)->add();
                        }
                        //进行用户分代资格判断，叠加返佣
                        $trose = M("usermanage")->where("id=" . $data["u_theb"])->getField("u_level");
                        if ($trose == 2) {
                            $sys_t = unserialize($sys_scale["s_trose"]);
                            if ($sys_t["types"] == 1 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                $tmoney = $total_fee * $sys_t["scale"];
                            } else if ($sys_t["types"] == 2 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                $tmoney = $sys_t["scale"];
                            } else {
                                $tmoney = $total_fee * 0.03;
                            }
                            if ($tmoney != 0 && $tmoney != "") {
                                M("usermanage")->where("id=" . $data["u_theb"])->setInc("u_commission", $tmoney);
                                $finance = array(
                                    "f_types" => 5,
                                    "f_text" => "分代：订单" . $MP_order["o_sn"] . "奖励",
                                    "f_price" => $tmoney,
                                    "f_uid" => $data["u_theb"],
                                    "f_datetime" =>time()
                                );
                                M("financemanage")->data($finance)->add();
                            }
                        }
                    }
                }
                if ($data["u_thec"] != 0) {
                    $check_dis=M("usermanage")->where("id=".$data["u_thec"])->getField("u_level");
                    if($check_dis>0) {
                        $money_3 = 0;
                        //获取产品一级分销比例，进行判断赋值
                        if ($money_3 == 0) {
                            $goodslist = unserialize($MP_order["o_ginfo"]);
                            foreach ($goodslist as $key => $val) {
                                $goodsinfo = M("goodsmanage")->where("id=" . $val["gid"])->field("g_scalec,g_vipprice")->find();
                                $goods_c = unserialize($goodsinfo["g_scalec"]);
                                $goods_m = $goodsinfo["g_vipprice"];
                                if ($goods_c["types"] == 1 && $goods_c["scale"] != 0 && $goods_c["scale"] != "") {
                                    $money_3 += ($goods_m * $goods_c["scale"] * $val["gnum"]);
                                } else if ($goods_c["types"] == 2 && $goods_c["scale"] != 0 && $goods_c["scale"] != "") {
                                    $money_3 += ($goods_c["scale"] * $val["gnum"]);
                                }
                            }
                        }
                        //获取用户分销比例，进行判断赋值
                        if ($money_3 == 0) {
                            $scale = M("usermanage")->where("id=" . $data["u_thec"])->getField("u_scale");
                            if ($scale != "") {
                                $money_3 = $total_fee * $scale;
                            }
                        }
                        //获取系统一级分销比例，进行判断赋值
                        if ($money_3 == 0) {
                            $sys_c = unserialize($sys_scale["s_scalec"]);
                            if ($sys_c["types"] == 1 && $sys_c["scale"] != 0 && $sys_c["scale"] != "") {
                                $money_3 = $total_fee * $sys_c["scale"];
                            } else if ($sys_c["types"] == 2 && $sys_c["scale"] != 0 && $sys_c["scale"] != "") {
                                $money_3 = $sys_c["scale"];
                            } else {
                                $money_3 = $total_fee * 0.3;
                            }
                        }
                        //如果有分销金额产生，则执行分销
                        if ($money_3 != 0) {
                            M("usermanage")->where("id=" . $data["u_thec"])->setInc("u_commission", $money_3);
                            $finance = array(
                                "f_types" => 5,
                                "f_text" => "订单" . $MP_order["o_sn"] . "奖励",
                                "f_price" => $money_3,
                                "f_uid" => $data["u_thec"],
                                "f_datetime" => time()
                            );
                            M("financemanage")->data($finance)->add();
                        }
                        //进行用户分代资格判断，叠加返佣
                        $trose = M("usermanage")->where("id=" . $data["u_thec"])->getField("u_level");
                        if ($trose == 2) {
                            $sys_t = unserialize($sys_scale["s_trose"]);
                            if ($sys_t["types"] == 1 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                $tmoney = $total_fee * $sys_t["scale"];
                            } else if ($sys_t["types"] == 2 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                $tmoney = $sys_t["scale"];
                            } else {
                                $tmoney = $total_fee * 0.03;
                            }
                            if ($tmoney != 0 && $tmoney != "") {
                                M("usermanage")->where("id=" . $data["u_thec"])->setInc("u_commission", $tmoney);
                                $finance = array(
                                    "f_types" => 5,
                                    "f_text" => "分代：订单" . $MP_order["o_sn"] . "奖励",
                                    "f_price" => $tmoney,
                                    "f_uid" => $data["u_thec"],
                                    "f_datetime" => time()
                                );
                                M("financemanage")->data($finance)->add();
                            }
                        }
                    }
                }
            }
            echo 1;
        }
        else{
            echo 0;
        }
    }
    public function cancel(){
        $id=I('get.id');
        $this->ostatus=I('get.ostatus');
        $refund=M('refund');
        $info=$refund->where("r_oid=".$id)->find();
        $info['r_img']=unserialize($info['r_img']);
        $this->assign('info',$info);
        $this->display();
    }
    public function saveCancel(){
        $info=I('post.');
        $rs= M('refund')->where('id='.$info['id'])->setField('r_status',$info['r_status']);
        if($info['r_status']==2&&$rs){
            $st=M('ordermanage')->where('id='.$info['r_oid'])->setField('o_status',7);
            if($st){
                $MP_order=M("ordermanage")->where("id=".$info['r_oid'])->field("o_uid,o_ginfo,o_sn,o_price")->find();
                $total_fee=$MP_order["o_price"];
                //奖励部分
                $data=M("usermanage")->where("id=".$MP_order["o_uid"])->field("u_thea,u_theb,u_thec,u_rose,u_isrose")->find();
                //如果该用户不是总代，并且是推广用户，则进行奖励
                if($data["u_isrose"]==0 && $data["u_thec"]!=0) {
                    $sys_scale=M("sysmanage")->where("id=1")->find();
                    if ($data["u_thea"] != 0) {//3ji
                        $check_dis=M("usermanage")->where("id=".$data["u_thea"])->getField("u_level");
                        if($check_dis>0) {
                            $money_1 = 0;
                            //获取产品三级分销比例，进行判断赋值
                            if ($money_1 == 0) {
                                $goodslist = unserialize($MP_order["o_ginfo"]);
                                foreach ($goodslist as $key => $val) {
                                    $goodsinfo = M("goodsmanage")->where("id=" . $val["gid"])->field("g_scalea,g_vipprice")->find();
                                    $goods_a = unserialize($goodsinfo["g_scalea"]);
                                    $goods_m = $goodsinfo["g_vipprice"];
                                    if ($goods_a["types"] == 1 && $goods_a["scale"] != 0 && $goods_a["scale"] != "") {
                                        $money_1 += ($goods_m * $goods_a["scale"] * $val["gnum"]);
                                    } else if ($goods_a["types"] == 2 && $goods_a["scale"] != 0 && $goods_a["scale"] != "") {
                                        $money_1 += ($goods_a["scale"] * $val["gnum"]);
                                    }
                                }
                            }
                            //获取用户分销比例，进行判断赋值
                            if ($money_1 == 0) {
                                $scale = M("usermanage")->where("id=" . $data["u_thea"])->getField("u_scale");
                                if ($scale != "") {
                                    $money_1 = $total_fee * $scale;
                                }
                            }
                            //获取系统三级分销比例，进行判断赋值
                            if ($money_1 == 0) {
                                $sys_a = unserialize($sys_scale["s_scalec"]);
                                if ($sys_a["types"] == 1 && $sys_a["scale"] != 0 && $sys_a["scale"] != "") {
                                    $money_1 = $total_fee * $sys_a["scale"];
                                } else if ($sys_a["types"] == 2 && $sys_a["scale"] != 0 && $sys_a["scale"] != "") {
                                    $money_1 = $sys_a["scale"];
                                } else {
                                    $money_1 = $total_fee * 0.1;
                                }
                            }
                            //如果有分销金额产生，则执行分销
                            if ($money_1 != 0) {
                                M("usermanage")->where("id=" . $data["u_thea"])->setInc("u_commission", $money_1);
                                $finance = array(
                                    "f_types" => 5,
                                    "f_text" => "订单" . $MP_order["o_sn"] . "奖励",
                                    "f_price" => $money_1,
                                    "f_uid" => $data["u_thea"],
                                    "f_datetime" => time()
                                );
                                M("financemanage")->data($finance)->add();
                            }
                            //进行用户分代资格判断，叠加返佣
                            $trose = M("usermanage")->where("id=" . $data["u_thea"])->getField("u_level");
                            if ($trose == 2) {
                                $sys_t = unserialize($sys_scale["s_trose"]);
                                if ($sys_t["types"] == 1 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                    $tmoney = $total_fee * $sys_t["scale"];
                                } else if ($sys_t["types"] == 2 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                    $tmoney = $sys_t["scale"];
                                } else {
                                    $tmoney = $total_fee * 0.03;
                                }
                                if ($tmoney != 0 && $tmoney != "") {
                                    M("usermanage")->where("id=" . $data["u_thea"])->setInc("u_commission", $tmoney);
                                    $finance = array(
                                        "f_types" => 5,
                                        "f_text" => "分代：订单" . $MP_order["o_sn"] . "奖励",
                                        "f_price" => $tmoney,
                                        "f_uid" => $data["u_thea"],
                                        "f_datetime" => time()
                                    );
                                    M("financemanage")->data($finance)->add();
                                }
                            }
                        }
                    }
                    if ($data["u_theb"] != 0) {
                        $check_dis=M("usermanage")->where("id=".$data["u_theb"])->getField("u_level");
                        if($check_dis>0) {
                            $money_2 = 0;
                            //获取产品二级分销比例，进行判断赋值
                            if ($money_2 == 0) {
                                $goodslist = unserialize($MP_order["o_ginfo"]);
                                foreach ($goodslist as $key => $val) {
                                    $goodsinfo = M("goodsmanage")->where("id=" . $val["gid"])->field("g_scaleb,g_vipprice")->find();
                                    $goods_b = unserialize($goodsinfo["g_scaleb"]);
                                    $goods_m = $goodsinfo["g_vipprice"];
                                    if ($goods_b["types"] == 1 && $goods_b["scale"] != 0 && $goods_b["scale"] != "") {
                                        $money_2 += ($goods_m * $goods_b["scale"] * $val["gnum"]);
                                    } else if ($goods_b["types"] == 2 && $goods_b["scale"] != 0 && $goods_b["scale"] != "") {
                                        $money_2 += ($goods_b["scale"] * $val["gnum"]);
                                    }
                                }
                            }
                            //获取用户分销比例，进行判断赋值
                            if ($money_2 == 0) {
                                $scale = M("usermanage")->where("id=" . $data["u_theb"])->getField("u_scale");
                                if ($scale != "") {
                                    $money_2 = $total_fee * $scale;
                                }
                            }
                            //获取系统二级分销比例，进行判断赋值
                            if ($money_2 == 0) {
                                $sys_b = unserialize($sys_scale["s_scaleb"]);
                                if ($sys_b["types"] == 1 && $sys_b["scale"] != 0 && $sys_b["scale"] != "") {
                                    $money_2 = $total_fee * $sys_b["scale"];
                                } else if ($sys_b["types"] == 2 && $sys_b["scale"] != 0 && $sys_b["scale"] != "") {
                                    $money_2 = $sys_b["scale"];
                                } else {
                                    $money_2 = $total_fee * 0.2;
                                }
                            }
                            //如果有分销金额产生，则执行分销
                            if ($money_2 != 0) {
                                M("usermanage")->where("id=" . $data["u_theb"])->setInc("u_commission", $money_2);
                                $finance = array(
                                    "f_types" => 5,
                                    "f_text" => "订单" . $MP_order["o_sn"] . "奖励",
                                    "f_price" => $money_2,
                                    "f_uid" => $data["u_theb"],
                                    "f_datetime" => time()
                                );
                                M("financemanage")->data($finance)->add();
                            }
                            //进行用户分代资格判断，叠加返佣
                            $trose = M("usermanage")->where("id=" . $data["u_theb"])->getField("u_level");
                            if ($trose == 2) {
                                $sys_t = unserialize($sys_scale["s_trose"]);
                                if ($sys_t["types"] == 1 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                    $tmoney = $total_fee * $sys_t["scale"];
                                } else if ($sys_t["types"] == 2 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                    $tmoney = $sys_t["scale"];
                                } else {
                                    $tmoney = $total_fee * 0.03;
                                }
                                if ($tmoney != 0 && $tmoney != "") {
                                    M("usermanage")->where("id=" . $data["u_theb"])->setInc("u_commission", $tmoney);
                                    $finance = array(
                                        "f_types" => 5,
                                        "f_text" => "分代：订单" . $MP_order["o_sn"] . "奖励",
                                        "f_price" => $tmoney,
                                        "f_uid" => $data["u_theb"],
                                        "f_datetime" =>time()
                                    );
                                    M("financemanage")->data($finance)->add();
                                }
                            }
                        }
                    }
                    if ($data["u_thec"] != 0) {
                        $check_dis=M("usermanage")->where("id=".$data["u_thec"])->getField("u_level");
                        if($check_dis>0) {
                            $money_3 = 0;
                            //获取产品一级分销比例，进行判断赋值
                            if ($money_3 == 0) {
                                $goodslist = unserialize($MP_order["o_ginfo"]);
                                foreach ($goodslist as $key => $val) {
                                    $goodsinfo = M("goodsmanage")->where("id=" . $val["gid"])->field("g_scalec,g_vipprice")->find();
                                    $goods_c = unserialize($goodsinfo["g_scalec"]);
                                    $goods_m = $goodsinfo["g_vipprice"];
                                    if ($goods_c["types"] == 1 && $goods_c["scale"] != 0 && $goods_c["scale"] != "") {
                                        $money_3 += ($goods_m * $goods_c["scale"] * $val["gnum"]);
                                    } else if ($goods_c["types"] == 2 && $goods_c["scale"] != 0 && $goods_c["scale"] != "") {
                                        $money_3 += ($goods_c["scale"] * $val["gnum"]);
                                    }
                                }
                            }
                            //获取用户分销比例，进行判断赋值
                            if ($money_3 == 0) {
                                $scale = M("usermanage")->where("id=" . $data["u_thec"])->getField("u_scale");
                                if ($scale != "") {
                                    $money_3 = $total_fee * $scale;
                                }
                            }
                            //获取系统一级分销比例，进行判断赋值
                            if ($money_3 == 0) {
                                $sys_c = unserialize($sys_scale["s_scalec"]);
                                if ($sys_c["types"] == 1 && $sys_c["scale"] != 0 && $sys_c["scale"] != "") {
                                    $money_3 = $total_fee * $sys_c["scale"];
                                } else if ($sys_c["types"] == 2 && $sys_c["scale"] != 0 && $sys_c["scale"] != "") {
                                    $money_3 = $sys_c["scale"];
                                } else {
                                    $money_3 = $total_fee * 0.3;
                                }
                            }
                            //如果有分销金额产生，则执行分销
                            if ($money_3 != 0) {
                                M("usermanage")->where("id=" . $data["u_thec"])->setInc("u_commission", $money_3);
                                $finance = array(
                                    "f_types" => 5,
                                    "f_text" => "订单" . $MP_order["o_sn"] . "奖励",
                                    "f_price" => $money_3,
                                    "f_uid" => $data["u_thec"],
                                    "f_datetime" => time()
                                );
                                M("financemanage")->data($finance)->add();
                            }
                            //进行用户分代资格判断，叠加返佣
                            $trose = M("usermanage")->where("id=" . $data["u_thec"])->getField("u_level");
                            if ($trose == 2) {
                                $sys_t = unserialize($sys_scale["s_trose"]);
                                if ($sys_t["types"] == 1 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                    $tmoney = $total_fee * $sys_t["scale"];
                                } else if ($sys_t["types"] == 2 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
                                    $tmoney = $sys_t["scale"];
                                } else {
                                    $tmoney = $total_fee * 0.03;
                                }
                                if ($tmoney != 0 && $tmoney != "") {
                                    M("usermanage")->where("id=" . $data["u_thec"])->setInc("u_commission", $tmoney);
                                    $finance = array(
                                        "f_types" => 5,
                                        "f_text" => "分代：订单" . $MP_order["o_sn"] . "奖励",
                                        "f_price" => $tmoney,
                                        "f_uid" => $data["u_thec"],
                                        "f_datetime" => time()
                                    );
                                    M("financemanage")->data($finance)->add();
                                }
                            }
                        }
                    }
                }
                echo 2;
            }else{
                echo 0;
            }

        }
        if($info['r_status']==1){
            M('ordermanage')->where('id='.$info['r_oid'])->setField('o_status',6);
            echo 1;
        }
        if($info['r_status']==0){
            echo 0;
        }
    }

}