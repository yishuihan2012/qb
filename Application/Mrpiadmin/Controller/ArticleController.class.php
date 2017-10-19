<?php

namespace Mrpiadmin\Controller;

use Think\Controller;
use Think\Upload;

class ArticleController extends ConController {

	//文章列表
	public function index()
	{
		$table = M('articlemanager');
		$count = $table -> count();
		$page = new \Think\Page($count,10);
		$show = $page->show();
		$data = $table -> order('article_id') -> limit($page->firstRow.','.$page->listRows) -> order('create_at desc') -> select();
		for($i = 0;$i<count($data);$i++){
			$category = M('articlecategory') -> field('category_id,category_name') -> where(array('category_id' => $data[$i]['article_category'])) -> find();
			$data[$i]['category_id'] = $category['category_id'];
			$data[$i]['category_name'] = $category['category_name'];
		}
		$this->assign('data',$data);
		$this->assign('page',$show);
		$this->display();
	}

	//添加文章
	public function add()
	{
		if($_POST){
			$data = $_POST;
			$data['update_at'] = date('Y-m-d H:i:s');
			//$data['create_at'] = date('Y-m-d H:i:s');


				//上传图片
				$image = $_FILES;
				$upload = new \Think\Upload();// 实例化上传类
				$upload->maxSize   =     3145728 ;// 设置附件上传大小
				$upload->exts      =     array('jpg', 'png', 'jpeg');// 设置附件上传类型
	    		$upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
	    		$upload->saveName  =	 'time';
			    $info   =   $upload->uploadOne($_FILES['article_image']);
			    if($info) 
			    {
			    	// 上传成功 获取上传文件信息
			        $data['article_image'] = "/Uploads/".$info['savepath'].$info['savename'];
			    }
				//添加数据


			$re = M('articlemanager') -> add($data);
			if($re){
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}else{
			$category = M('articlecategory') -> field('category_id,category_name') -> where(array('category_state' => 1)) -> select();
			$this->assign('category',$category);
			$this->display();
		}
	}


	//文章详情
	public function show()
	{
			$id = $_GET['id'];
			$data = M('articlemanager') -> where(array('article_id' => $id)) -> find();
			$category = M('articlecategory') -> where(array('category_id' => $data['article_category'])) -> find();
			$str['article_view'] = $data['article_view']+1;
			M('articlemanager') -> where(array('article_id' => $id)) -> save($str);
			$this->assign('category',$category);
			$this->assign('data',$data);
			$this->display();
	}

	//修改文章
	public function update(){
		if($_POST){
			$post = $_POST;
			$id = $post['id'];
			unset($post['id']);
			$post['update_at'] = date('Y-m-d H:i:s');
			//上传图片
			if($_FILES){

				$image = $_FILES;
				$upload = new \Think\Upload();// 实例化上传类
				$upload->maxSize   =     3145728 ;// 设置附件上传大小
				$upload->exts      =     array('jpg', 'png', 'jpeg');// 设置附件上传类型
	    		$upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
	    		$upload->saveName  =	 'time';
			    $info   =   $upload->uploadOne($image['article_image']);
			    if($info) {
			      $post['article_image'] = "/Uploads/".$info['savepath'].$info['savename'];
			    }
			}


			$re = M('articlemanager')->save($post);


			if($re){
				$this->success('修改成功',U('article/index'));
			}else{
				$this->error('您未做修改');
			}
		}else{
			$id = $_GET['id'];
			$data = M('articlemanager') -> where(array('article_id' => $id)) -> find();
			$category = M('articlecategory') -> where(array('category_state' => 1)) -> select();
			$this->assign('category',$category);
			$this->assign('data',$data);
			$this->display();
		}
	}

	//删除文章
	public function delete(){
		$id = $_GET['id'];
		$re = M('articlemanager') -> where(array('article_id' => $id)) -> delete();
		if($re){
			echo 1;
		}else{
			echo 0;
		}
	}
}
