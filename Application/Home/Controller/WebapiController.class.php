<?php

namespace Home\Controller;

use Think\Controller;

class WebapiController extends Controller {

    public function get_my_agent(){ //获取我的代理

        $return ="";

        $post=I('post.');
        $paths=M('userlevel ul')
              ->join('usermanage um on um.id=ul.user_id','right')
              ->join('usermanage m on m.id=ul.parent_id','right')
              ->join('membertype t on t.member_id=m.u_member_id','left')
              ->where(['user_id'=>$post['id']])->find();
        // if(!$paths['parent_id'] && $paths['u_role']==1){ //平台用户 食物链顶端
        //     $return['parent']=['phone'=>'平台直属','role'=>'超级合伙人'];
        //     $where=['paths'=>['like',$post['id'].'%']];
        // }else if(!$paths['parent_id'] && $paths['u_role']==2){
        //     $return['parent']=['phone'=>'尚未查询到用户信息','role'=>'服务商'];
        //     $where=['paths'=>['like',$post['id'].'%']];
        // }else if(!$paths['parent_id'] && $paths['u_role']==2){
        //   $return['parent']=['phone'=>'尚未查询到用户信息','role'=>'普通员工'];
        //   $where=['paths'=>['like',$post['id'].'%']];
        // }else{ //存在上级用户信息

        if(!$paths['parent_id'] && $paths['u_member)_id']){ 
            $return['parent']=['phone'=>'','role'=>$paths['member_name']];
            $where=['paths'=>['like',$post['id'].'%']];
        }else{ //存在上级用户信息
          $parents=M('usermanage um')
          ->join('userlevel ul on ul.parent_id=um.id','left')
          ->join('usermanage m on m.id=ul.parent_id','right')
          ->join('membertype t on t.member_id=m.u_member_id','left')
          ->where(['ul.user_id'=>$post['id']])->find();
          $where=['paths'=>['like',$paths['paths'].','.$post['id'].'%']];

          $rele=$parents['member_name'];
          if($parents){ //存在上级用户信息
            // $rele=$parents['u_role']==1?"超级合伙人":($parents['u_role']==2?"服务商":"普通员工");
            $return['parent']=['phone'=>substr_replace($parents['u_mobile'],'****',3,4),'role'=>$rele];
          }

        }
        //查询当前用户下级数量
      $return['parent']['counts']=M('userlevel ul')
            ->join('usermanage um on ul.user_id=um.id','right')
            ->distinct(true)
            ->field('um.u_mobile')
            ->where($where)
            ->count();

      $lists=M('userlevel ul')
          ->join('usermanage um on ul.user_id=um.id','right')
          ->distinct(true)
          ->where($where)
          ->select();

      foreach($lists as $key=>$val){
        $return['list'][]=[
          'tel'   =>  $val['u_mobile'],
          'role'  =>  $val['u_role']==1?"平台员工":($val['u_role']==2?"服务商":"普通员工"),
        ];
      }

      echo json_encode(['code'=>200,'msg'=>'数据获取成功！','data'=>$return]);

    }

}
