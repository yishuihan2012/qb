<?php
namespace Home\Controller;
use Think\Controller;
class EwmController extends Controller {
    public function index(){
        //text二维码内容logo二维码中心图片
        //$i_data=I("post.");
        //if($i_data) {
            vendor("phpqrcode.phpqrcode");
            //$data = $i_data["text"];//生成内容
            $data = "http://shimakeji.yuantuoweb.com/admin/index.php";//生成内容
            $lv = "L";//容错级别L,M,Q,H
            $size = 10;//大小1~10
            $path = "./Uploads/".date("Y-m-d",time())."/";//图片保存地址
            if(!file_exists($path)){
                mkdir($path);
            }
            $filename = "yt" . time() . $size . ".png";//图片名称
            \QRcode::png($data, $path.$filename, $lv, $size);
            if(!empty($i_data["logo"])){
                $logo=$i_data["logo"];
                $QR=$path.$filename;
                $QR = imagecreatefromstring(file_get_contents($QR));
                $logo = imagecreatefromstring(file_get_contents($logo));
                $QR_width = imagesx($QR);//二维码图片宽度
                $QR_height = imagesy($QR);//二维码图片高度
                $logo_width = imagesx($logo);//logo图片宽度
                $logo_height = imagesy($logo);//logo图片高度
                $logo_qr_width = $QR_width / 5;
                $scale = $logo_width/$logo_qr_width;
                $logo_qr_height = $logo_height/$scale;
                $from_width = ($QR_width - $logo_qr_width) / 2;
                //重新组合图片并调整大小
                imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
                $QR_img=imagepng($QR,$path.$filename);
            }
        //}
        $QR_img=$path.$filename;
        print_r($QR_img);
        $this->display();
    }

}