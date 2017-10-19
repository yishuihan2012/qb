<?php
namespace Home\Controller;
use Think\Controller;
class NotifyController extends Controller {
    public function index(){
        $this->display();
    }
    public function wxnotify(){
        Vendor('Wxpay.WxPayPubHelper.WxPayPubHelper');
        $xmldata=$GLOBALS['HTTP_RAW_POST_DATA'];
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xmldata, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if($data){
            $notify = new \Notify_pub();
            if($data["return_code"]=="SUCCESS"){
                $t_order=substr($data["out_trade_no"],0,1);
                if($t_order=="C") {
                    $check = M("ordermanage")->where("o_sn='{$data["out_trade_no"]}'")->find();
                    if ($check) {
                        if ($check["o_status"] == 0) {
                            $set_info=array(
                                "o_status"=>1,
                                "o_paytime"=>NOW_TIME,
                            );
                            $MP_set = M("ordermanage")->where("id=" . $check["id"])->setField($set_info);
                            if ($MP_set) {
                                $check_dis=M("usermanage")->where("id=".$check["o_uid"])->getField("u_level");
                                if($check_dis==0){
                                    $dis_set=M("usermanage")->where("id=".$check["o_uid"])->setField("u_level",3);
                                }
                                $notify->setReturnParameter("return_code", "SUCCESS");
                            } else {
                                $notify->setReturnParameter("return_code", "FAIL");
                            }
                            $returnXml = $notify->returnXml();
                            echo $returnXml;
                        } else {
                            file_put_contents('a.txt', "订单：" . $data["out_trade_no"] . "不可重复操作\r\t", FILE_APPEND);
                        }
                    } else {
                        file_put_contents('b.txt', "没有" . $data["out_trade_no"] . "相关订单\r\t", FILE_APPEND);
                    }
                }
                else if($t_order=="F"){
                    $check = M("fordermanage")->where("f_sn='{$data["out_trade_no"]}'")->find();
                    if ($check) {
                        if ($check["f_status"] == 0) {
                            $MP_set = M("fordermanage")->where("id=" . $check["id"])->setField("f_status", 1);
                            if ($MP_set) {
                                $notify->setReturnParameter("return_code", "SUCCESS");
                            } else {
                                $notify->setReturnParameter("return_code", "FAIL");
                            }
                            $returnXml = $notify->returnXml();
                            echo $returnXml;
                        } else {
                            file_put_contents('a.txt', "订单：" . $data["out_trade_no"] . "不可重复操作\r\t", FILE_APPEND);
                        }
                    } else {
                        file_put_contents('b.txt', "没有" . $data["out_trade_no"] . "相关订单\r\t", FILE_APPEND);
                    }
                }
            }
            else{
                file_put_contents('c.txt', $data["out_trade_no"]."支付失败\r\t",FILE_APPEND);
            }
        }
        else{
            file_put_contents('d.txt', $xmldata ,FILE_APPEND);
        }
    }
    public function alipaynotifyurl(){
        //商户订单号
        $out_trade_no = $_GET['out_trade_no'];
        //支付宝交易号
        $trade_no = $_GET['trade_no'];
        //交易状态
        $trade_status = $_GET['trade_status'];
        $total_fee = $_GET['total_fee'];
        $parameter = array(
            "out_trade_no"	=> $out_trade_no, //商户订单编号；
            "trade_no"			=> $trade_no,     //支付宝交易号；
            "trade_status"	=> $trade_status, //交易状态
        );
        if($trade_status == 'TRADE_FINISHED') {
//            echo "success";
            $this->redirect("Order/index");
        }
        else if ($trade_status == 'TRADE_SUCCESS') {
            $t_order=substr($out_trade_no,0,1);
            if($t_order=="C") {
                if (!checkorderstatus($out_trade_no)) {
                    orderhandle($parameter);
                    //进行订单处理，并传送从支付宝返回的参数；
//                echo "success";
                    $this->redirect("Order/index");
                }
            }
            else if($t_order=="F"){
                if (!fcheckorderstatus($out_trade_no)) {
                    forderhandle($parameter);
                    //进行订单处理，并传送从支付宝返回的参数；
//                echo "success";
                    $this->redirect("Order/index");
                }
            }
        }
        else{
//            echo "fail";
            $this->error("支付失败");
        }
    }
}