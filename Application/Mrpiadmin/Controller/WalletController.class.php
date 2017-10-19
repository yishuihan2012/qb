<?php
namespace Mrpiadmin\Controller;
use Think\Controller;
use Think\Page;

// 用户钱包
class OrderfyController extends ConController { //钱包信息
    public function index(){

      $page_size=20;

      $page=I('get.page');

      $count=M('orderfy o')
            ->join('usermanage u on o.order_user_id=u.id','left')
            ->count();

      $page=new Page($count,$page_size);

      $orders=M('orderfy o')
            ->join('usermanage u on o.order_user_id=u.id','left')
            ->limit($page->firstRow.','.$page->listRows)
            ->select();

      $show=$page->show();

      $this->assign('orders',$orders);

      $this->assign('show',$show);

      $this->display('index');
    }
}
