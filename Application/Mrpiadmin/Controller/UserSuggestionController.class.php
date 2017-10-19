<?php
namespace Mrpiadmin\Controller;
use Think\Controller;

class UserSuggestionController extends ConController {
	
	/**
	 * 获取用户反馈列表
	 */
	public function getSuggestList()
	{
		//查询总条数
		$suggestion_obj = M('usersuggestion as us');
		$rowCount = $suggestion_obj->count();
		
		//分页
		$page = new \Think\Page($rowCount, 25);
		
		//查询反馈
		$suggestion_obj->join(array('LEFT JOIN usermanage as um ON us.user_id = um.id'));
		$suggestion_obj->field('us.id, um.u_account, left(us.suggestion_info, 15) as suggestion_info, us.create_at');
		$suggestion_obj->limit($page->firstRow, $page->listRows);
		$suggestion_obj->order('us.id desc');
		$suggestion_result = $suggestion_obj->select();
		
		$this->assign('page', $page->show());
		$this->assign('suggestion_result', $suggestion_result);
		$this->display('suggestion_list');
	}
	
	
	
	/**
	 * 获取用户反馈详情
	 */
	public function getSuggestDetail()
	{
		$id = I('get.id');
		
		if (empty($id))
		{
			$this->error("请重新操作");
		}
		
		//查询反馈
		$suggestion_obj = M('usersuggestion as us');
		$suggestion_obj->where('us.id = ' . $id);
		$suggestion_obj->join(array('LEFT JOIN usermanage as um ON us.user_id = um.id'));
		$suggestion_obj->field('us.id, um.u_account, us.suggestion_info, us.create_at');
		$suggestion_result = $suggestion_obj->find();
	
		$this->assign('suggestion_result', $suggestion_result);
		$this->display('suggest_detail');
	}
	
	
	/**
	 * 删除用户反馈
	 */
	public function delSuggest()
	{
		$id = I('get.id');
		
		if (empty($id))
		{
			$this->error("请重新操作");
		}
		
		//查询反馈
		$suggestion_obj = M('usersuggestion');
		$suggestion_obj->where('id = ' . $id);
		$suggestion_obj->delete();
		
		$this->redirect('getSuggestList');
	}
	
	
	/**
	 * 获取常见问题列表
	 */
	public function getQuestionList()
	{
		//查询总条数
		$problem_obj = M('problems');
		$rowCount = $problem_obj->count();
	
		//分页
		$page = new \Think\Page($rowCount, 25);
	
		//查询问题列表
		$problem_obj->field('problem_id, problem_title, create_time,is_show');
		$problem_obj->limit($page->firstRow, $page->listRows);
		$problem_obj->order('problem_id desc');
		$problem_result = $problem_obj->select();
	
		$this->assign('page', $page->show());
		$this->assign('problem_result', $problem_result);
		$this->display('question_list');
	}
	
	
	/**
	 * 获取常见问题详情
	 */
	public function getQuestionDetail()
	{
		$id = I('get.id');
	
		if (empty($id))
		{
			$this->error("请重新操作");
		}
	
		//查询问题
		$problem_result = M('problems') -> where('problem_id = ' . $id) -> find();

		// dump($problem_result);

		//每到此方法一次 浏览数量加一次
		$str['view'] = $problem_result['view']+1;
		$re = M('problems') -> where(array('problem_id' => $id)) -> save($str);

		$this->assign('problem_result', $problem_result);
		$this->display('question_detail');
	}
	
	
	/**
	 * 删除常见问题
	 */
	public function delQuestion()
	{
		$id = I('get.id');
	
		if (empty($id))
		{
			$this->error("请重新操作");
		}
	
		//查询问题
		$suggestion_obj = M('problems');
		$suggestion_obj->where('problem_id = ' . $id);
		$suggestion_obj->delete();
	
		$this->redirect('getQuestionList');
	}
	
	
	/**
	 * 增加常见问题
	 */
	public function addQuestion()
	{
		$this->display('question_add');
	}
	
	
	/**
	 * 增加常见问题操作
	 */
	public function addQuestion_act()
	{
		$title = $_POST['title'];
		$content = $_POST['content'];

		if (empty($title) || empty($content))
		{
			echo 0; exit();
		}
		 
		$insertArray = array(
				'problem_title' => $title,
				'problem_info' => $content
		);
		
		M('problems')->data($insertArray)->add();
		 
		echo 1;
	}
	
	
	/**
	 * 修改常见问题
	 */
	public function modifyQuestion()
	{
		$id = I('get.id');
	
		if (empty($id))
		{
			$this->error("请重新操作");
		}
	
		//查询问题
		$problem_obj = M('problems');
		$problem_obj->where('problem_id = ' . $id);
		$problem_obj->field('problem_id, problem_title, problem_info,is_show');
		$problem_result = $problem_obj->find();
		$this->assign('problem_result', $problem_result);
		$this->display('question_modify');
	}
	
	
	/**
	 * 修改常见问题操作
	 */
	public function modifyQuestion_act()
	{
		$id = $_POST['id'];
		$title = $_POST['title'];
		$content = $_POST['content'];
		$is_show=$_POST['is_show'];
		if (empty($id) || empty($title) || empty($content))
		{
			echo 0; exit();
		}
		 
		$insertArray = array(
				'problem_title' => $title,
				'problem_info' => $content,
				'is_show'     =>$is_show,
		);
		
		M('problems')->where('problem_id  = ' . $id)->data($insertArray)->save();
		 
		echo 1;
	}
}
