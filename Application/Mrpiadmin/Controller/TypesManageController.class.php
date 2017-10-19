<?php

namespace Mrpiadmin\Controller;

use Think\Controller;
use Think\Upload;

class TypesManageController extends ConController
{

    public function index()
    {

        //$this->display('login');

    }

    public function TypesAdd()
    {

        $MP_data = I("post.");

        if ($MP_data) {

            $MP_sql = M("gtypemanage")->data($MP_data)->add();

            if ($MP_sql) {

                echo 1;

            } else {

                echo 0;

            }

        } else {

            $MP_sql = M("gtypemanage")->select();

            $this->assign("type_list", $MP_sql);

            $this->display();

        }

    }

    public function TypesList()
    {

        $MP_sql = M("gtypemanage")->select();

        if ($MP_sql) {

            foreach ($MP_sql as $key => $val) {

                if ($val["g_parentid"] != 0) {

                    $val["g_parent"] = M("gtypemanage")->where("id=" . $val["g_parentid"])->getField("g_name");

                } else {

                    $val["g_parent"] = "顶级栏目";

                }

                $type_list[$key] = $val;

            }

            $this->assign("type_list", $type_list);

        }

        $this->display();

    }

    public function TypesEdit()
    {

        $MP_data = I("post.");

        if ($MP_data) {

            $id = $MP_data["id"];

            $MP_sql = M("gtypemanage")->where("id=" . $id)->data($MP_data)->save();

            if ($MP_sql) {

                echo 1;

            } else {

                echo 0;

            }

        } else {

            $id = I("get.id");

            $MP_sql = M("gtypemanage")->where("id=" . $id)->find();

            $MP_list = M("gtypemanage")->where("id<>" . $id)->select();

            if ($MP_sql["g_parentid"] != 0) {

                $MP_sql["g_parent"] = M("gtypemanage")->where("id=" . $MP_sql["g_parentid"])->getField("g_name");

            } else {

                $MP_sql["g_parent"] = "顶级栏目";

            }

            $this->assign("type_info", $MP_sql);

            $this->assign("type_list", $MP_list);

            $this->display();

        }

    }

    public function TypesDel()
    {

        $MP_data = I("post.");

        if ($MP_data) {

            $id = $MP_data["id"];

            $MP_sql = M("gtypemanage")->where("id=" . $id)->delete();

            if ($MP_sql) {

                echo 1;

            } else {

                echo 0;

            }

        } else {

            $this->error("非法操作");

        }

    }
    /**
     * 会员分类列表
     */
    public function MemberTypesList()
    {
        $data = M('membertype')->order('member_sort','asc')->select();
        $this->assign('list', $data);
        $this->display();
    }
    /**
     * 添加会员分类
     */
    public function MemberTypesAdd()
    {
        if ($post = I('post.')) {
            $post['member_create_time'] = time();

            $image = $_FILES;
            if ($image['member_head_img']['size'] > 0) {

                $upload = new Upload(); // 实例化上传类

                $upload->maxSize  = 3145728; // 设置附件上传大小
                $upload->exts     = array('jpg', 'png', 'jpeg'); // 设置附件上传类型
                $upload->rootPath = './Uploads/'; // 设置附件上传根目录

                $upload->saveName = 'time';
                $info             = $upload->uploadOne($image['member_head_img']);
                if (!$info) {
                    // 上传错误提示错误信息
                    $this->error($upload->getError());
                } else {
                    // 上传成功 获取上传文件信息
                    $post['member_head_img'] = HOST . "Uploads/" . $info['savepath'] . $info['savename'];
                }
            }
            $res = M('membertype')->add($post);
            if ($res) {
                $this->success('添加成功', U('TypesManage/MemberTypesList'));
            } else {
                $this->error('添加失败', U('TypesManage/MemberTypesAdd'));
            }
        } else {
            $this->display();
        }
    }
    /**
     * 修改会员分类
     */
    public function MemberTypesUpdate($id)
    {
        $this->assign('id', $id);
        if ($post = I('post.')) {
            $post['member_create_time'] = time();

            $image = $_FILES;
            if ($image['member_head_img']['size'] > 0) {

                $upload = new Upload(); // 实例化上传类

                $upload->maxSize  = 3145728; // 设置附件上传大小
                $upload->exts     = array('jpg', 'png', 'jpeg'); // 设置附件上传类型
                $upload->rootPath = './Uploads/'; // 设置附件上传根目录

                $upload->saveName = time().mt_rand(1,999);
                $info             = $upload->uploadOne($image['member_head_img']);
                if (!$info) {
                    // 上传错误提示错误信息
                    $this->error($upload->getError());
                } else {
                    // 上传成功 获取上传文件信息
                    $post['member_head_img'] =  HOST ."Uploads/" . $info['savepath'] . $info['savename'];
                }
            }

            $res = M('membertype')->where(array('member_id' => $id))->save($post);
            if ($res) {
                $this->success('修改成功', U('TypesManage/MemberTypesList'));
            } else {
                $this->error('修改失败');
            }
        } else {
            $data = M('membertype')->where(array('member_id' => $id))->find();
            $this->assign('data', $data);
            $this->display();
        }
    }
    /**
     * 删除会员分类
     * @param [type] $id [description]
     */
    public function MemberTypeDelete($id)
    {

        $res = M('membertype')->where(array('member_id' => $id))->delete();
        echo $res;
    }
    /**
     * 文章分类列表
     */
    public function ArticleTypesList(){
        // $data=M('articletype')->order('article_sort asc')->select();
        $data=M('articlecategory')->order('category_sort asc')->select();
        $this->assign('list',$data);
        $this->display();
    }
     /**
     * 添加会员分类
     */
    public function ArticleTypesAdd()
    {
        if ($post = I('post.')) {
            $post['update_at'] = time();
            $res = M('articlecategory')->add($post);
            if ($res) {
                $this->success('添加成功', U('TypesManage/ArticleTypesList'));
            } else {
                $this->error('添加失败', U('TypesManage/ArticleTypesAdd'));
            }
        } else {
            $this->display();
        }
    }
    /**
     * 修改会员分类
     */
    public function ArticleTypesUpdate($id)
    {
        $this->assign('id', $id);
        if ($post = I('post.')) {
            $post['create_at'] = time();

            $res = M('articlecategory')->where(array('category_id' => $id))->save($post);
            if ($res) {
                $this->success('修改成功', U('TypesManage/ArticleTypesList'));
            } else {
                $this->error('修改失败');
            }
        } else {
            $data = M('articlecategory')->where(array('category_id' => $id))->find();
            $this->assign('data', $data);
            $this->display();
        }
    }
    /**
     * 删除会员分类
     * @param [type] $id [description]
     */
    public function ArticleTypeDelete($id)
    {
        $res = M('articlecategory')->where(array('category_id' => $id))->delete();
        echo $res;  
    }
}
