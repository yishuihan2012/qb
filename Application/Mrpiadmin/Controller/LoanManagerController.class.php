<?php
namespace Mrpiadmin\Controller;
use Think\Controller;
use Think\Page;
use Think\Upload;

class LoanManagerController extends ConController {
    //首页
    public function index(){
      $this->getList();
    }
    //增
    public function add(){
      if(IS_POST){

        $data=I('post.');

        $image = $_FILES;

        if($image['image']['size']>0){

          $upload = new Upload();// 实例化上传类

          $upload->maxSize   =     3145728 ;// 设置附件上传大小
          $upload->exts      =     array('jpg', 'png', 'jpeg');// 设置附件上传类型
          $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录

          $upload->saveName  =	 'time';
          $info   =   $upload->uploadOne($image['image']);
          if(!$info) {// 上传错误提示错误信息
              $this->error($upload->getError());
          }else{// 上传成功 获取上传文件信息
              $data['image'] = HOST."Uploads/".$info['savepath'].$info['savename'];
          }
        }
        $data['creat_at']=time();

        $add=M('credit_way')->add($data);

        if($data['tags']){ //更新标签信息
            M('credit_of_tags')->where(['cradit_id'=>$add])->delete();
            foreach($data['tags'] as $key=>$val){
                M('credit_of_tags')->add(['cradit_id'=>$add,'tag_id'=>$val]);
            }
        }


        if($add){
          $this->success('添加成功！',U('LoanManager/index'));
        }else{
          $this->error('添加失败！');
        }

      }else{

        $action=U('LoanManager/add');

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

        if($image['image']['size']>0){

          $upload = new Upload();// 实例化上传类

          $upload->maxSize   =     3145728 ;// 设置附件上传大小
          $upload->exts      =     array('jpg', 'png', 'jpeg');// 设置附件上传类型
          $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录

          $upload->saveName  =	 'time';
          $info   =   $upload->uploadOne($image['image']);
          if(!$info) {// 上传错误提示错误信息
              $this->error($upload->getError());
          }else{// 上传成功 获取上传文件信息
              $data['image'] = HOST."Uploads/".$info['savepath'].$info['savename'];
          }
        }

        $save=M('credit_way')->save($data);

        if($data['tags']){ //更新标签信息
            M('credit_of_tags')->where(['cradit_id'=>$data['id']])->delete();
            foreach($data['tags'] as $key=>$val){
                M('credit_of_tags')->add(['cradit_id'=>$data['id'],'tag_id'=>$val]);
            }
        }


        if(false!==$save){
          $this->success('修改成功！',U('LoanManager/index'));
        }else{
          $this->error('修改失败！');
        }

      }else{

        $id=I('get.id');

        $select=M('credit_of_tags')->where(['cradit_id'=>$id])->select();

        $tag_selected="";

        foreach($select as $key=>$val){
            $tag_selected[]=$val['tag_id'];
        }

        $this->assign('tag_selected',$tag_selected);

        $action=U('LoanManager/edit');

        $this->assign('action',$action);

        $this->assign('action_desc','提交修改');

        $this->getForm();

      }
    }
    //列表
    public function getList(){

      $page_size=25;

      $count=M('credit_way')->count();

      $Page=new Page($count,$page_size);

      $credits=M('credit_way')->limit($Page->firstRow.','.$Page->listRows)->select();

      $show=$Page->show();

      $this->assign('credits',$credits);

      $this->assign('show',$show);

      $this->display('getList');

    }
    //表单
    public function getForm(){

          $id=I('get.id');
              if($id){
                  $bank=M('credit_way')->where(['id'=>$id])->find();
                  $data['id']=$id;
              }else{
                  $bank="";
                  $data['id']='';
              }

              if($bank['credit_way']){
                    $data['credit_way']=$bank['credit_way'];
              }else{
                    $data['credit_way']="";
              }

              if($bank['credit_way_info']){
                    $data['credit_way_info']=$bank['credit_way_info'];
              }else{
                    $data['credit_way_info']="";
              }

              if($bank['title']){
                    $data['title']=$bank['title'];
              }else{
                    $data['title']="";
              }

              if($bank['short_desc']){
                    $data['short_desc']=$bank['short_desc'];
              }else{
                    $data['short_desc']="";
              }

              if($bank['image']){
                    $data['image']=$bank['image'];
              }else{
                    $data['image']="";
              }

              if($bank['quota']){
                    $data['quota']=$bank['quota'];
              }else{
                    $data['quota']="";
              }
              if($bank['rate']){
                    $data['rate']=$bank['rate'];
              }else{
                    $data['rate']="";
              }

              if($bank['view']){
                    $data['view']=$bank['view'];
              }else{
                    $data['view']="";
              }

              if($bank['sort']){
                    $data['sort']=$bank['sort'];
              }else{
                    $data['sort']="";
              }

              $tags=M('credit_tags')->select();

              $this->assign('data',$data);

              $this->assign('tags',$tags);

              $this->display('getForm');
    }
    //删
    public function delete(){
        $id=I('get.id');

        $bank=M('credit_way')->where(['id'=>$id])->delete();

        if($bank){
          $this->success('删除成功！',U('LoanManager/index'));
        }else{
          $this->error('删除失败！');
        }
    }
    //验证
    public function verification(){

    }
}

 ?>
