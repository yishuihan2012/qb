<?php

namespace Mrpiadmin\Controller;

use Think\Controller;

class PlanTaskController extends ConController {

    /**
     * 获取新的提现申请
     * @return [type] [description]
     */
    public function getNewWithdrawals(){
        $where['cash_state']=0;
        $noHandleCount=M('usercash')->where($where)->count();
        if($noHandleCount){
           echo $noHandleCount;die;
        }else{
            echo 0;die;
        }
    }
}