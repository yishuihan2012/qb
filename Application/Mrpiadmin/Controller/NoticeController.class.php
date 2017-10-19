<?php
namespace Mrpiadmin\Controller;

class NoticeController extends ConController{
	
	/**
	 * 	公告列表
	 */
	public function notice_list()
	{
		//查询总条数
		$notice_obj = M('notice as n');
		$notice_obj->where(array( 'n.is_del' => 0 ));		
		$rowCount = $notice_obj->count();
		
		//分页
		$page = new \Think\Page($rowCount, 25);
		
		//查询公告
		$notice_obj->join(array('LEFT JOIN tringroominfo as t ON n.public_admin = t.id'));
		$notice_obj->field('n.id, n.title, t.t_name, n.create_time');
		$notice_obj->limit($page->firstRow, $page->listRows);
		$notice_obj->where(array( 'n.is_del' => 0 ));
		$notice_result = $notice_obj->select();

		$this->assign('page', $page->show());		
		$this->assign('notice_result', $notice_result);		
		$this->display('notice_list');
	}
	
	
	/**
	 * 	公告详情
	 */
	public function notice_detail()
	{
		$id = I('get.id');
		 
		if (empty($id))
		{
			$this->error("请重新操作");
		}
		 
		$notice_obj = M('notice as n');
		$notice_obj->join('LEFT JOIN tringroominfo as t ON n.public_admin = t.id');
		$notice_obj->field = ('n.title, n.content, t.t_name, n.create_time');
		$notice_obj->where(array( 'n.id' => $id ));
		$notice_result = $notice_obj->find();
		 
		$this->assign('notice_result', $notice_result);
		$this->display('notice_detail');
	}
	
	
	/**
	 * 	增加公告
	 */
	public function add_notice()
	{
		$this->display('add_notice');
	}
	
	
	/**
	 * 	增加公告
	 */
	public function add_notice_act()
	{
		$title = I('post.title');
		
		$content = I('post.content');
		
		$admin_id = session('id');
		 
		if (empty($title) || empty($content))
		{
			echo 0;exit();
		}
		 
		//增加公告
		$insertArray = array(
				'title' => $title,
				'content' => $content,
				'public_admin' => $admin_id,
				'create_time' => date('Y-m-d H:i:s')
		);
		 
		M('notice')->data($insertArray)->add();
		 
		echo 1;
	}
	
	
	/**
	 * 删除公告
	 */
	public function notice_del()
	{
		$id = I('get.id');
	
		if (empty($id))
		{
			$this->error("请重新操作");
		}
		 
		M('notice')->where(array( 'id' => $id ))->delete();
		 
		$this->redirect('notice/notice_list');
	}
	
	
	/**
	 * 修改公告
	 */
	public function notice_modify()
	{
		$id = I('get.id');
	
		if (empty($id))
		{
			$this->error("请重新操作");
		}
			
		$notice_result = M('notice')->field('id, title, content')->where(array( 'id' => $id ))->find();
			
		$this->assign('notice_result', $notice_result);
		$this->display();
	}
	
	
	/**
	 * 修改公告操作
	 */
	public function notice_modify_act()
	{
		$id = I('post.id');
		$title = I('post.title');
		$content = I('post.content');
	
		if (empty($id) || empty($title) || empty($content))
		{
			echo 0;exit();
		}
			
		M('notice')->data(['title' => $title, 'content' => $content])->where(array( 'id' => $id ))->save();
		
		echo 1;
	}
}
?>