<?php
namespace Mrpiadmin\Controller;

use Think\Controller;

class ActivityController extends ConController {

	//the table for activity
	public $table="activity";

	public $table_s="activity a";

	//set pagesize for one page
	public $pagesize='10';

	//Activity lists
	public function index()
	{
		//get Activity lists to view
		$count=M($this->table_s)
			->join('activity b on a.activity_type=b.id','left')
			-> count();

		$page = new \Think\Page($count,$this->pagesize);

		$show = $page->show();

		$data = M($this->table_s)
			->field('a.*,b.type_name')
			->join('activity_type b on a.activity_type=b.id','left')
			->order('a.id') 
			->limit($page->firstRow.','.$page->listRows) 
			->select();

		$this->assign('data',$data);

		$this->assign('page',$show);

		$this->display();

	}

	//blank_list
	public function add_activity()
	{

		if(IS_POST){

			$data = $_POST;

			$data['activity_time'] = time();

			$data['activity_href'] = $data['activity_href']=='' ? '#' : $data['activity_href'];

			if(M($this->table) -> add($data)){

				$this->success('添加成功');

			}else{

				$this->error('添加失败');

			}

		}else{

			//get all type
			$activity_type=M('activity_type') -> select();

			$this->assign('activity_type',$activity_type);

			$this->display();
		}

	}


	//show activity info
	public function show($id)
	{

		if(IS_POST){

			$data = $_POST;

			$data['activity_time'] = time();

			$data['activity_href'] = $data['activity_href']=='' ? '#' : $data['activity_href'];

			if(M($this->table) -> where(array('id' => $data['id'])) -> save($data))

				$this->success('修改成功',U('Activity/index'));

			else

				$this->error('修改失败');

		}else{

			if(!$id)
				$this->error('参数错误');

			$data = M($this->table) -> where(array('id' => $id)) -> find();

			$activity_type = M('activity_type') -> select();

			$this->assign('activity_type',$activity_type);

			$this->assign('data',$data);

			$this->display();

		}

	}



	//droup activity
	public function droup($id)
	{
		if(!$id)
			$this->error('参数错误');

		if(M($this->table) -> where(array('id' => $id)) -> delete())
			echo 'success';
		else
			echo 'error';
		
	}


}
