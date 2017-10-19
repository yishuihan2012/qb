<?php
namespace Home\Controller;
use Think\Controller;
class ConController extends Controller {
    public function _initialize(){
            $id=$this->islogin();
            if($id){
                $this->id=$id;
                $this->u_level_l=M('usermanage')->where('id='.$id)->getField('u_level');
                $u_mobile_c=M('usermanage')->where('id='.$id)->getField('u_mobile');
                $this->u_mobile_c=$u_mobile_c;
                $this->u_mobile_h= substr_replace($u_mobile_c, '*****', 3, 6);
                $this->u_code=M('usermanage')->where('id='.$id)->getField('u_code');
			
				/*$MP_my=M("usermanage")->where("id=".$id)->find();
				$path=explode("/",$MP_my['u_ewm']);
				$ewm='./'.$path[3]."/".$path[4].'/'.$path[5];
				$ewmpath='./'.$path[3]."/".$path[4];
				if(!file_exists($ewmpath)){
					mkdir($ewmpath);
				}
				if(!file_exists($ewm)){
					vendor("phpqrcode.phpqrcode");
					//$data = $i_data["text"];//生成内容
					$data = "http://www.xyclsw.com".U("Home/Index/Reg",array("code"=>$MP_my["u_code"]));//生成内容
					$lv = "L";//容错级别L,M,Q,H
					$size = 10;//大小1~10
					$filename = $path[5];//图片名称
					\QRcode::png($data, $ewmpath.'/'.$filename, $lv, $size); 
				}		*/
            }

    }
    public function islogin()
    {
        $id = session("login");
        if ($id) {
            $r = M('usermanage')->where("id=".$id)->count();
            if ($r == 1) {
                return $id;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}