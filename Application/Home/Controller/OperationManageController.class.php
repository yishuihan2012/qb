<?php
namespace Home\Controller;

use Think\Controller;

class OperationManageController extends Controller
{
    /**
     * 分享页图片修改
     */
    public function ShareManage()
    {
        if($_FILES){

        }else{
            $img=HOST.'/Public/web/images/share_code_center.png';
            $this->assign('img',$img);
            $this->display();
        }
       
    }
}
