<?php
namespace Mrpiadmin\Controller;

use Think\Controller;

class OperationManageController extends Controller
{
    /**
     * 分享页图片修改
     */
    public function ShareManage()
    {
        if($file=$_FILES){
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   =     2097152 ;// 设置附件上传大小
            $upload->exts      =     array('png');// 设置附件上传类型
            $upload->replace = true;
            $upload->rootPath  =      './Public/web/images/'; // 设置附件上传根目录
            $upload->autoSub=false;
            $upload->savePath  =      ''; // 设置附件上传（子）目录
            $upload->saveName = 'bg_sharecode';
            // 上传文件 
            foreach ($file as $k => $v) {
                if($k=='second'){
                    $info   =   $upload->uploadOne($file['second']);
                    if(!$info) {// 上传错误提示错误信息
                        $this->error($upload->getError());
                    }else{// 上传成功 获取上传文件信息
                        $this->success('上传成功');
                         // echo $info['savepath'].$info['savename'];die;
                    }
                }
            }
        }else{
            $img=HOST.'Public/web/images/bg_sharecode.png';
            $this->assign('img',$img);
            $this->display();
        }
       
    }
}
