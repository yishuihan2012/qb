<?php

namespace Mrpiadmin\Controller;

use Think\Controller;

class UploadController extends ConController {

    public function summernote(){

        $upload = new \Think\Upload();// 实例化上传类

        $upload->maxSize=104857600;

        $upload->exts      =     array();// 设置附件上传类型

        $upload->saveName =     'mp';

        $upload->rootPath  =    './Uploads/'; // 设置附件上传根目录

        // 上传文件

        $info   =   $upload->upload();

        if(!$info) {// 上传错误提示错误信息

            $this->error($upload->getError());

        }else{// 上传成功 获取上传文件信息

            foreach($info as $file){

                $file_info[]=HOST."Uploads/".$file['savepath'].$file['savename'];

            }

            die(json_encode($file_info));

        }

    }

}

