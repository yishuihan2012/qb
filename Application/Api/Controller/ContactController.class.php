<?php
namespace Api\Controller;
use Think\Controller;

class ContactController extends Controller{
	
	/**
	 * 获取客服qq和电话
	 */
	public function getContact()
	{
		//qq
		$contact_qq_result = M('contact_info')->field('contact_no')->where('type = 1')->select();
		
		$contact_qq_array = array();
		
		foreach ($contact_qq_result as $val)
		{
			$contact_qq_array[] = $val['contact_no'];
		}
		
		//tel
		$contact_tel_result = M('contact_info')->field('contact_no')->where('type = 2')->order('id desc')->find();
		
		$array = array('contact_qq_list' => $contact_qq_array, 'contact_tel' => $contact_tel_result['contact_no']);
		
		echo json_encode(['code'=>200,'msg'=>'获取成功！', 'data'=>$array]);
	}
}