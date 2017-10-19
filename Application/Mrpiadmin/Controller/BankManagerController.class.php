<?php

namespace Mrpiadmin\Controller;

use Think\Controller;
use Think\Page;
use Think\Upload;

class BankManagerController extends ConController {
    //首页
    public function index(){
      $this->getList();
    }
    //增
    public function add(){
      if(IS_POST){

        $data=I('post.');

        $image = $_FILES;

        if($image['bank_logo']['size']>0){

          $upload = new Upload();// 实例化上传类

          $upload->maxSize   =     3145728 ;// 设置附件上传大小
          $upload->exts      =     array('jpg', 'png', 'jpeg');// 设置附件上传类型
          $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录

          $upload->saveName  =	 'time';
          $info   =   $upload->uploadOne($image['bank_logo']);
          if(!$info) {// 上传错误提示错误信息
              $this->error($upload->getError());
          }else{// 上传成功 获取上传文件信息
              $data['bank_logo'] = HOST."Uploads/".$info['savepath'].$info['savename'];
          }
        }
        $data['create_at']=time();
        $data['update_at']=time();

        $add=M('bankmanager')->add($data);


        if($add){
          $this->success('添加成功！',U('BankManager/index'));
        }else{
          $this->error('添加失败！');
        }

      }else{
        $action=U('BankManager/add');

        $this->assign('action',$action);

        $this->assign('action_desc','添加信息');

        $this->getForm();

      }
    }
    //改
    public function edit(){

      if(IS_POST){

        $data=I('post.');

        $image = $_FILES;

        if($image['bank_logo']['size']>0){

          $upload = new Upload();// 实例化上传类

          $upload->maxSize   =     3145728 ;// 设置附件上传大小
          $upload->exts      =     array('jpg', 'png', 'jpeg');// 设置附件上传类型
          $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录

          $upload->saveName  =	 'time';
          $info   =   $upload->uploadOne($image['bank_logo']);
          if(!$info) {// 上传错误提示错误信息
              $this->error($upload->getError());
          }else{// 上传成功 获取上传文件信息
              $data['bank_logo'] = HOST."Uploads/".$info['savepath'].$info['savename'];
          }
        }
        $data['update_at']=time();

        $save=M('bankmanager')->save($data);


        if(false!==$save){
          $this->success('修改成功！',U('BankManager/index'));
        }else{
          $this->error('修改失败！');
        }

      }else{
        $action=U('BankManager/edit');

        $this->assign('action',$action);

          $this->assign('action_desc','提交修改');

        $this->getForm();

      }
    }
    //列表
    public function getList(){

      $page_size=25;
      $count=M('bankmanager')->count();
      $Page=new Page($count,$page_size);

      $banks=M('bankmanager')->limit($Page->firstRow.','.$Page->listRows)->select();

      $show=$Page->show();
      $this->assign('banks',$banks);
      $this->assign('show',$show);
      $this->display('getList');
    }
    //表单
    public function getForm(){
          $id=I('get.id');
              if($id){
                  $bank=M('bankmanager')->where(['id'=>$id])->find();
                  $data['id']=$id;
              }else{
                  $bank="";
                  $data['id']='';
              }

              if($bank['bank_name']){
                    $data['bank_name']=$bank['bank_name'];
              }else{
                    $data['bank_name']="";
              }

              if($bank['bank_logo']){
                    $data['bank_logo']=$bank['bank_logo'];
              }else{
                    $data['bank_logo']="";
              }

              if($bank['bank_activation']){
                    $data['bank_activation']=$bank['bank_activation'];
              }else{
                    $data['bank_activation']="";
              }

              if($bank['bank_ascension']){
                    $data['bank_ascension']=$bank['bank_ascension'];
              }else{
                    $data['bank_ascension']="";
              }

              if($bank['bank_progress']){
                    $data['bank_progress']=$bank['bank_progress'];
              }else{
                    $data['bank_progress']="";
              }

              if($bank['credit']){
                    $data['credit']=$bank['credit'];
              }else{
                    $data['credit']="";
              }

              $this->assign('data',$data);

              $this->display('getForm');
    }
    //删
    public function delete(){
        $id=I('get.id');

        $bank=M('bankmanager')->where(['id'=>$id])->delete();

        if($bank){
          $this->success('删除成功！',U('BankManager/index'));
        }else{
          $this->error('删除失败！');
        }
    }
    //改变状态
    public function changeState(){
        $id=I('post.id');
        if($id){

          $is_hot=M('bankmanager')->where(['id'=>$id])->getField('bank_hot');

          if($is_hot){
              $up=M('bankmanager')->where(['id'=>$id])->setField(['bank_hot'=>0]);
          }else{
              $up=M('bankmanager')->where(['id'=>$id])->setField(['bank_hot'=>1]);
          }
          if(false!=$up){
            echo json_encode(['code'=>200,'message'=>'状态修改成功！','data'=>'']);
          }else{
            echo json_encode(['code'=>100,'message'=>'状态修改失败！','data'=>'']);
          }
        }else{
          echo json_encode(['code'=>100,'message'=>'状态修改失败！','data'=>'']);
        }
    }
    //验证
    public function verification(){

    }
}

 ?>
