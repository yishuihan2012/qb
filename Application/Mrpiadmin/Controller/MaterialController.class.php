<?php
namespace Mrpiadmin\Controller;

class MaterialController extends ConController {

	/**
	 * 	素材列表
	 */
    public function material_list()
    {
    	$material_result = M('material')->where(array( 'is_del' => 0 ))->select();
    	$this->assign('material_result', $material_result);
    	$this->display('material_list');
    }
    
    
    /**
     * 	素材详情
     */
    public function material_detail()
    {
    	$id = I('get.id');
    	
    	if (empty($id)) 
    	{
    		$this->error("请重新操作");
    	}
    	
    	$material_result = M('material')->where(array( 'id' => $id ))->find();
    	$material_img_result = M('material_img')->where(array( 'material_id' => $id ))->select();
    	
    	$this->assign('material_result', $material_result);
    	$this->assign('material_img_result', $material_img_result);   	
    	$this->display('material_detail');
    }

    
    /**
     * 	增加素材
     */
    public function add_material()
    {
    	$this->display('add_material');
    }
    
    
    /**
     * 	增加素材
     */
    public function add_material_act()
    {
    	$title = I('post.title');
    	$content = I('post.content');
    	$image_str = I('post.imgList');
    	
    	if (empty($title) || empty($content)) 
    	{
    		echo 0;exit();
    	}
    	
    	//增加素材
    	$insertArray = array(
    		'title' => $title,
    		'content' => $content,
    		'create_time' => date('Y-m-d H:i:s')		
    	);
    	
    	$insert_id = M('material')->data($insertArray)->add();

    	if (!empty($image_str)) 
    	{
    		$material_obj = M('material_img');
    		
    		$image_array = explode(',', trim($image_str, ','));
    		 
    		foreach ($image_array as $img)
    		{
    			$insert_array = array('material_id' => $insert_id, 'img' => $img);
    			$material_obj->data($insert_array);
    			$material_obj->add();
    		}
    	}
    	
    	echo 1;
    }
    
    
    /**
     * 删除素材
     */
    public function material_del()
    {
    	$id = I('get.id');
    	 
    	if (empty($id))
    	{
    		$this->error("请重新操作");
    	}
    	
    	M('material')->where(array( 'id' => $id ))->delete();
    	M('material_img')->where('material_id', $id)->delete();
    	
    	$this->redirect('Material/material_list');
    }
}

