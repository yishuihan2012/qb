<?php

namespace Api\Controller;

use Think\Controller;

class QuckpaymentController extends Controller
{
    protected $payGateway = "http://pay.mishua.cn/zhonlinepay/service/down/trans/payDzero"; //4.3.2.无卡支付接口（网关）
    /**
     * 获取银行卡列表
     * @return [type] [description]
     */
    public function getCardList()
    {
        $post = I('post.');
        if (!is_login($post['token'], $post['user'])) {
            exit(json_encode(['code' => 112, 'msg' => '登录信息失效！请重新登录', 'data' => '']));
        }
        if($post['type']<2){ 
            $where['card_type']=$post['type'];
        }
        $where['user_id'] = $post['user'];
        $list             = M('quickpaycard')->where($where)->select();
        foreach ($list as $k => $v) {
            $bank = M('bankmanager')->where(array('bank_name' => $v['card_bank']))->find();
            if ($bank) {
                $list[$k]['bank_logo'] = $bank['bank_logo'];
            } else {
                $list[$k]['bank_logo'] = '';
            }
        }
        if ($list) {
            exit(json_encode(['code' => 200, 'msg' => 'success', 'data' => ['list' => $list]]));
        } else {
            exit(json_encode(['code' => 100, 'msg' => '暂无绑定的银行卡', 'data' => ['list' => $list]]));
        }
    }
    /**
     * 绑定新的银行卡
     * @return [type] [description]
     */
    public function bindNewCard()
    {
        $post = I('post.');
        if (!is_login($post['token'], $post['user'])) {
            exit(json_encode(['code' => 112, 'msg' => '登录信息失效！请重新登录', 'data' => '']));
        }
        $cert = M('usercertification')->where(array('usercertification_user_id' => $post['user']))->find();
        if ($cert) {
            if ($cert['usercertification_state'] == 1) {
                $name          = $cert['usercertification_name'];
                $card_identify = $cert['usercertification_card'];
            } else {
                exit(json_encode(['code' => 101, 'msg' => '该用户还未实名认证成功', 'data' => '']));
            }
        } else {
            exit(json_encode(['code' => 101, 'msg' => '该用户还未实名认证', 'data' => '']));
        }
        $quickpaycard = array(
            'user_id'       => $post['user'],
            'card_number'   => $post['card_number'],
            'card_type'     => $post['card_type'],
            'card_tel'      => $post['card_tel'],
            'card_bank'     => $post['card_bank'],
            'card_name'     => $name,
            'card_identify' => $card_identify,
        );
        $card = M('quickpaycard')->add($quickpaycard);
        if ($card) {
            exit(json_encode(['code' => 200, 'msg' => '绑定成功', 'data' => '']));
        } else {
            exit(json_encode(['code' => 100, 'msg' => '绑定失败', 'data' => '']));
        }
    }
    /**
     * 解除绑定银行卡
     */
    public function UnboundCard()
    {
        $post = I('post.');
        if (!is_login($post['token'], $post['user'])) {
            exit(json_encode(['code' => 112, 'msg' => '登录信息失效！请重新登录', 'data' => '']));
        }
        $card_id          = $post['card_id'];
        $where['user_id'] = $post['user'];
        $where['card_id'] = $post['card_id'];
        if (M('quickpaycard')->where($where)->find()) {
            $res = M('quickpaycard')->where($where)->delete();
            if ($res) {
                exit(json_encode(['code' => 200, 'msg' => '解除绑定成功', 'data' => '']));
            } else {
                exit(json_encode(['code' => 101, 'msg' => '解除绑定失败', 'data' => '']));
            }
        } else {
            exit(json_encode(['code' => 100, 'msg' => '未查询到该银行卡', 'data' => '']));
        }
    }
    /**
     * 获取银行列表
     * @return [type] [description]
     */
    public function getBankList()
    {
        $banks = M('bankmanager')->field('id,bank_name,bank_logo')->select();
        if ($banks) {
            exit(json_encode(['code' => 200, 'msg' => '获取成功', 'data' => ['list' => $banks]]));
        } else {
            exit(json_encode(['code' => 100, 'msg' => '暂无银行', 'data' => '']));
        }
    }
    /**
     * 快捷支付
     * @return [type] [description]
     */
    public function quckPayment()
    {
        $post = I('post.');
        if (!is_login($post['token'], $post['user'])) {
            exit(json_encode(['code' => 112, 'msg' => '登录信息失效！请重新登录', 'data' => '']));
        }

        $price = $post['price'];
        $des   = '快捷支付';

        $config=M('channeltype')->where(array('ct_unique_sign'=>'mishua'))->find();
        if($config){
           $ct_min_money=$config['ct_min_money'];
           $ct_max_money=$config['ct_max_money'];
           if($price<$ct_min_money){
                 exit(json_encode(['code' => 101, 'msg' => '最小刷卡金额为'.$ct_min_money.'元', 'data' => '']));
           }
           if($price>$ct_max_money){
                 exit(json_encode(['code' => 101, 'msg' => '最大刷卡金额为'.$ct_max_money.'元', 'data' => '']));
           }
        }

        $payCardId     = $post['payCardId'];
        $recieveCardId = $post['recieveCardId'];
        //获取两个卡号信息
        $paycardinfo = M('quickpaycard')->where(array('card_id' => $payCardId))->find();
        if ($paycardinfo) {
            $payCardNo = $paycardinfo['card_number'];
        } else {
            exit(json_encode(['code' => 101, 'msg' => '获取支付卡号信息失败', 'data' => '']));
        }
        $acceptcardinfo = M('quickpaycard')->where(array('card_id' => $recieveCardId))->find();
        if ($acceptcardinfo) {
            $accName   = $acceptcardinfo['card_name'];
            $accIdCard = $acceptcardinfo['card_identify'];
            $bankName  = $acceptcardinfo['card_bank'];
            $cardNo    = $acceptcardinfo['card_number'];
        } else {
            exit(json_encode(['code' => 101, 'msg' => '获取收款卡号信息失败', 'data' => '']));
        }

        $mchNo   = C('quckpayment_mchNo');
        $tradeNo = $this->getTradeNo();
        //获取结算费率，代付费信息。
          $user = M('usermanage')
                  ->join('membertype t on t.member_id =usermanage.u_member_id')
                  ->where(array('id' => $post['user']))->find();
          if (!$user) {
              exit(json_encode(['code' => 100, 'msg' => '获取用户信息失败,请稍后重试', 'data' => '']));
          }
          $downPayFee=$user['member_rate'];
          $downDrawFee=$user['member_service_charge'];
          if($post['user']==1){
            $downDrawFee=0;
          }
        #1判断有没有绑定过快捷支付，绑定过查询数据库取用户相关银行卡信息
        #
        $arr = array(
            'versionNo'   => 1, //版本固定为1
            'mchNo'       => $mchNo, //商户号
            'price'       => $price, //单位为元，精确到0.01,必须大于1元
            'description' => $des, //交易描述
            'orderDate'   => date('YmdHis', time()), //订单日期
            'tradeNo'     => $tradeNo, //商户平台内部流水号，请确保唯一
            // 'notifyUrl'  =>HOST."callback/quckPaymentCallback.php",//异步通知URLqucikPayCallBack
            'notifyUrl'   => HOST . "/index.php?s=/Api/Quckpayment/qucikPayCallBack", //异步通知URL
            'callbackUrl' => HOST . "/index.php?s=/Api/Quckpayment/turnurl", //页面回跳地址
            'payCardNo'   => $payCardNo, //支付卡卡号。上游要求支付卡和结算卡为同一个持卡人名下
            'accName'     => $accName, //持卡人姓名 必填
            'accIdCard'   => $accIdCard, //卡人身份证  必填
            'bankName'    => $bankName, //  结算卡开户行  必填  结算卡开户行
            'cardNo'      => $cardNo, //算卡卡号 必填  结算卡卡号
            'downPayFee'  => $downPayFee, //结算费率  必填  接入机构给商户的费率，D0直清按照此费率结算，千分之X， 精确到0.01
            'downDrawFee' => $downDrawFee, // 代付费 选填  每笔扣商户额外代付费。不填为不扣。
        );
        $insert = array(
            'qp_uid'         => $post['user'],
            'qp_price'       => $price,
            'qp_des'         => $des,
            'qp_orderDate'   => $arr['orderDate'],
            'qp_create_time' => time(),
            'qp_tradeno'     => $tradeNo,
            'qp_paycard'     => $payCardNo,
            'qp_accname'     => $accName,
            'qp_idcard'      => $accIdCard,
            'qp_bankname'    => $bankName,
            'qp_cardno'      => $cardNo,
            'downpayfee'     => $downPayFee,
        );
        //信息存入订单表*******************
        $payload = $this->getPayload($arr);
        $sign    = $this->getSign($payload);
        $request = array(
            'mchNo'   => $mchNo,
            'payload' => $payload,
            'sign'    => $sign,
        );
        $res    = $this->curlPost($this->payGateway, 'post', json_encode($request));
        $result = json_decode($res, true);
        if ($result['code'] == 0) {
            $datas = A('MCrypt')->decrypt($result['payload']);
            $datas = trim($datas);
            $datas = substr($datas, 0, strpos($datas, '}') + 1);
            $resul = json_decode($datas, true);
            //下单结果存入订单表*******************、、、
            $insert['qp_status']        = 1;
            $insert['qp_transno']       = $resul['transNo'];
            $insert['qp_transtr']       = $resul['tranStr'];
            $insert['qp_statusdesc']    = $resul['statusDesc'];
            $insert['qp_downrealprice'] = $resul['downRealPrice'];
            $insert['qp_profit']        = $resul['profit'];
            $insert                     = M('quickpayorder')->add($insert);
            exit(json_encode(['code' => '200', 'msg' => 'success', 'data' => ['url' => $resul['tranStr']]]));
        } else {
            $insert['qp_status']     = 0;
            $insert['qp_failreason'] = $result['message'];
            $insert                  = M('quickpayorder')->add($insert);
            exit(json_encode(['code' => $result['code'], 'msg' => $result['message'], 'data' => '']));
        }
    }
    public function getTradeNo()
    {
        return date('YmdHis', time()) . mt_rand(1000, 9999);
    }
    /**
     * [发送CURL请求]
     * @param  [type] $url    [请求URL]
     * @param  string $method [请求方法]
     * @param  string $data   [请求数据]
     * @return [type]         [description]
     */
    public function curlPost($url, $method = 'post', $data = '')
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json; charset=utf-8"));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $temp = curl_exec($ch);
        return $temp;
    }
    public function getSign($data)
    {
        #4 拼接字符串
        $str = $data . C('quckpayment_signkey');
        #5 md5加密
        $md5 = md5($str);
        #6 转成大写
        $upper = strtoupper($md5);
        return $upper;
    }
    /**
     * 获取签名
     * @return [type] [description]
     */
    public function getPayload($data)
    {
        #0检查参数有效性
        // $data=checkData($data);
        if ($data) {
            #1 转成json
            $data = json_encode($data);
            #2 AES加密
            $encrypt = A('MCrypt')->encrypt($data);
            return $encrypt;
        } else {
            return 0;
        }
    }
    /**
     * 验证参数，所有选项都是必填
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function checkData($data)
    {
        if ($data) {
            $flg = 1;
            foreach ($data as $k => $v) {
                if (!$v) {
                    $flg = 0;
                }
            }
            return $flg;
        } else {
            return 0;
        }
    }
    public function turnurl()
    {
        $request = $_REQUEST;
        // $request['transNo']='20170907090922436609';
        $data    = M('quickpayorder')->where(array('qp_transno' => $request['transNo']))->find();
        if ($request['status'] == '00') {
            $data['qp_cardno']        = substr($data['qp_cardno'], -4);
            $data['qp_downrealprice'] = number_format($data['qp_downrealprice'], 2);
            $data['result']           = 1;
        } else {
            $data['rsult'] = 0;
        }
        $this->assign('data', $data);
        $this->display('/quickPpayResult');
    }
    /**
     * 异步回调处理
     * @return [type] [description]
     */
    public function qucikPayCallBack()
    {
        // 获取传过来参数
        $data = file_get_contents("php://input");
        $data = trim($data);
        // file_put_contents('filecontent.txt',$data);
        $data = json_decode($data, true);
        // file_put_contents('success.txt',$data['state']);
        //回调详细信息
        $info = A('MCrypt')->decrypt($data['payload']);

        // file_put_contents('datas1.txt',$info);

        $datas = trim($info);
        $datas = substr($datas, 0, strpos($datas, '}') + 1);

        // file_put_contents('datas2.txt', $datas);
        //返回结果
        $resul = json_decode($datas, true);
        //订单详情
        $order                = M('quickpayorder')->where(array('qp_transno' => $resul['transNo']))->find();
        $pay_card             = $order['qp_paycard'];
        $get_card             = $order['qp_cardno'];
        $user_id              = $order['qp_uid'];
        $where['card_number'] = $pay_card;
        $where['user_id']     = $user_id;
        $param['card_number'] = $get_card;
        $param['user_id']     = $user_id;
        //00代表成功
        if ($resul['status'] == '00') {

            if ($order && $order['qp_paystatus'] == '01') {
                //未修改过状态
                // file_put_contents('result.txt', '取到订单而且没改状态'.date('Y-m-d H:i:s'));
                //标记卡号为有效卡
                $upcard['card_validate'] = 1;
                $payinfo                 = M('quickpaycard')->where($where)->save($upcard);
                //更新银行卡的状态
                $update['qp_paystatus'] = $resul['status'];
                $update['qp_paydes']    = $resul['statusDesc'];
                if ($resul['qfStatus']) {
                    $update['qp_qfsatus'] = $resul['qfStatus'];
                    $update['qftime']    = time();
                    // file_put_contents('status.txt', $resul['qfStatus']);
                    if ($resul['qfStatus'] != 'FAILED') {
                        // $payinfo = M('quickpaycard')->where($param)->save($upcard);
                        // $this->subProfit($order);
                    } else {
                        //删除收款失败卡号
                        $getinfo = M('quickpaycard')->where($param)->find();
                        if ($getinfo['card_validate'] == 0) {
                            M('quickpaycard')->where($param)->delete();
                        }
                    }
                }
                $res = M('quickpayorder')->where(array('qp_transno' => $resul['transNo']))->save($update);
                if ($res) {
                    // file_put_contents('result1.txt', '取到订单而且修改完状态'.date('Y-m-d H:i:s'));
                } else {
                    // file_put_contents('result2.txt', '取到订单而且改状态失败'.date('Y-m-d H:i:s'));
                }
            } else if ($order && $order['qp_paystatus'] == '00') {
                //更改为支付成功后，判断代付结果
                $order = M('quickpayorder')->where(array('qp_transno' => $resul['transNo']))->find();
                if ($order['qp_qfsatus']!="SUCCESS") {
                    $updates['qp_qfsatus'] = $resul['qfStatus'];
                    $updates['qftime']     = time();
                    $res                   = M('quickpayorder')->where(array('qp_transno' => $resul['transNo']))->save($updates);
                    //代付成功，分润
                    if ($resul['qfStatus'] == 'SUCCESS') {
                        $payinfo = M('quickpaycard')->where($param)->save($upcard);
                        $this->subProfit($order);
                    } else {
                        //删除收款失败卡号
                        $getinfo = M('quickpaycard')->where($param)->find();
                        if ($getinfo['card_validate'] == 0) {
                            M('quickpaycard')->where($param)->delete();
                        }
                    }
                    echo 'success';die;
                }else if($order['qp_qfsatus']=="SUCCESS" ){

                    echo 'success';die;
                }
            }
        } else {
            // 将当前银行卡修改为验证失败[支付失败直接删除卡号]
            $payinfo = M('quickpaycard')->where($where)->find();
            if ($payinfo['card_validate'] == 0) {
                M('quickpaycard')->where($where)->delete();
            }

            // file_put_contents('result5.txt', 'status' . $resul['status']);
        }
    }
    /**
     * 分润测试
     * @return [type] [description]
     */
    public function fenruntest($id)
    {
        $order = M('quickpayorder')->where(array('qp_id' =>$id))->find();
        $this->subProfit($order);
    }
    /**
     * 分润
     * @return [type] [description]
     */
    public function subProfit($order)
    {
        $uid      = $order['qp_uid'];
         $userinfo = M('usermanage')
                  ->join('membertype t on t.member_id =usermanage.u_member_id','left')
                  ->join('userlevel l on l.user_id=usermanage.id','left')
                  ->where(array('id' => $uid))->find();

        #1计算分润金额
        // 当前用户比例-上三级比例 再乘以总钱数=当前分润总金额。
        $now_rate=$userinfo['member_rate'];
        // if(!$userinfo['path_3rd']){//没有上级不用分润 
        //    echo"无上级不用分润"; die;
        // }
        // if (strpos(',', $userLevel['path_3rd'])) {
        //     $ceil_uid=explode(',', $userinfo['path_3rd']);
        //     $uid_end=end($ceil_uid);
        //     $baseinfo = M('membertype')->where(array('member_id' => $uid_end))->find();
        //     $rate_base=$baseinfo['member_rate'];
        // }else{
        //   $count=1;
        //   $cel_uid1=$userLevel['path_3rd'];
        // }
        $base=M('membertype')->where(array('member_allow_profit'=>1))->find();
        $base_rate=$base['member_rate'];
        // 分润总金额=(当前用户费率-可分润角色的费率)*交易总额
        $subProfit = ($now_rate - $base_rate) * $order['qp_price'] / 1000; 
        if($subProfit<0 || $subProfit==0){
             // echo"最高级别不用分润";die;
        }else{
            // var_dump($subProfit); 
            // 查询分润比例
            $sysconf = M('sysxfmanage')->find(); //根据ID找到对应的 配置信息
            if ($sysconf) {
                $sys          = unserialize($sysconf['info']);
                $divide_prop  = explode(':', $sys['prox_price']);
                //如果两级分，计算两级别各得多花钱；
                $AA1  = ($divide_prop[0] + $divide_prop[2]) / 10; //第三级上级的分润给直接上级
                $AA2 = $divide_prop[1] / 10;
                $AA1  = $AA1 * $subProfit;
                $AA2 = $AA2 * $subProfit;
                // 如果三级分
                $firstrate  = $divide_prop[0] / 10;
                $secondrate = $divide_prop[1] / 10;
                $thirdrate  = $divide_prop[2] / 10;

                $AAA1  = $firstrate * $subProfit;
                $AAA2 = $secondrate * $subProfit;
                $AAA3  = $thirdrate * $subProfit;
            }else{
                // exit('获取配置信息失败');
            }
            M('quickpayorder')->where(array('qp_id' => $order['qp_id']))->save(array('subprofit' => $subProfit));
            #2计算其上级分润比例
            // 1直接上级
            // echo $userinfo['path_3rd'];
            if ($userinfo['path_3rd']) {
                if (strpos($userinfo['path_3rd'],',')) {
                    $level    = explode(',',$userinfo['path_3rd']);
                    $count    = count($level);
                    // var_dump($count);die;
                    $firstUid = $level[0];
                    if ($count == 2) {   //两个人 情况 AB | BA | AA 
                        $secondUid = $level[1];
                        // 俩人分 没上三级的加给第一个
                        $first_result=$this->isGetProfit($firstUid);
                        $second_result=$this->isGetProfit($secondUid);
                        // var_dump($first_result);
                        // var_dump($second_result);die;
                        if($first_result && $second_result){ //俩人都达到分润级别
                             // 分别记录日志
                            $this->addUserMoney($firstUid, $AA1, $order['qp_id'],$userinfo);
                            $this->addUserMoney($secondUid, $AA2, $order['qp_id'],$userinfo);
                        }else{
                            if($first_result){ //直接上级达到分润级别全部金额给她
                                $this->addUserMoney($firstUid, $subProfit, $order['qp_id'],$userinfo);
                            }
                            if($second_result){ //间接上级达到分润级别全部金额给她
                                $this->addUserMoney($secondUid, $subProfit, $order['qp_id'],$userinfo);
                            }
                        }
                    } else if ($count == 3) { // AAA  |AAC |BAA  |ABC | BAC |BCA 
                        $secondUid  = $level[1];
                        $thirdUid   = $level[2];
                        //查看有没有分润 
                        $first_result=$this->isGetProfit($firstUid);
                        $second_result=$this->isGetProfit($secondUid);
                        $third_result=$this->isGetProfit($thirdUid);
                        if($first_result && $second_result && $third_result){ //AAA

                            $this->addUserMoney($firstUid, $AAA1, $order['qp_id'],$userinfo);
                            $this->addUserMoney($secondUid, $AAA2, $order['qp_id'],$userinfo);
                            $this->addUserMoney($thirdUid, $AAA3, $order['qp_id'],$userinfo);
                        }
                        if($first_result==1 && $second_result==1 && $third_result==0){  //AAB
                            $this->addUserMoney($firstUid, $AA1, $order['qp_id'],$userinfo);
                            $this->addUserMoney($secondUid, $AA2, $order['qp_id'],$userinfo);
                        }
                        if($first_result==0 && $second_result==1 && $third_result==1){  //BAA
                            $this->addUserMoney($secondUid, $AA1, $order['qp_id'],$userinfo);
                            $this->addUserMoney($thirdUid, $AA2, $order['qp_id'],$userinfo);
                        }
                        if($first_result==1 && $second_result==0 && $third_result==0){  //ABC

                            $this->addUserMoney($firstUid, $subProfit, $order['qp_id'],$userinfo);
                        }
                        if($first_result==0 && $second_result==1 && $third_result==0){  //BAB

                            $this->addUserMoney($secondUid, $subProfit, $order['qp_id'],$userinfo);
                        }
                        if($first_result==0 && $second_result==0 && $third_result==1){  //CBA
                            echo
                            $this->addUserMoney($thirdUid, $subProfit, $order['qp_id'],$userinfo);
                        }
                    }
                } else {
                    // 一个上级。自己分
                    if( $this->isGetProfit($userinfo['path_3rd']) ){
                        $this->addUserMoney($userinfo['path_3rd'], $subProfit, $order['qp_id'],$userinfo);
                    }
                }
            }else{
                // exit('无上级');
            }
        }
    }
    /**
     * 查询该用户是否能分润
     * @param  [type]  $uid [description]
     * @return boolean      [description]
     */
    public function isGetProfit($uid){
        $info=M('usermanage')
                ->join('membertype t on t.member_id =usermanage.u_member_id','left')
                ->where(array('id' => $uid))->find();
        return $info['member_allow_profit'];  
    }
    /**
     * 给会员加余额
     */
    public function addUserMoney($uid, $subProfit, $info_id,$userinfo)
    {
        if($subProfit>0.01){
            $pay_mobile=$userinfo['u_mobile'];
            $log_describe=$pay_mobile.'刷卡';
            // 查询钱包 
            $wallet = M('userwallet')->where(['wallet_user_id' => $uid])->find();
            //添加余额
            $update = M('userwallet')->where(['user_wallet_id' => $wallet['user_wallet_id'], 'wallet_amount' => $wallet['wallet_amount']])->save(['wallet_amount' => bcadd($wallet['wallet_amount'], $subProfit, 4), 'update_at' => date("Y-m-d H:i:s", time())]);
            //添加日志
            $logs_commission = M('userwalletlog')->add(['log_wallet_id' => $wallet['user_wallet_id'], 'log_amount' => $subProfit, 'service_charge' => 0, 'account' => '会员收款', 'account_name' => '会员分润', 'log_option' => 3, 'log_describe'=>$log_describe,'other_info' =>$info_id, 'log_state' => 1, 'create_at' => date('Y-m-d H:i:s', time())]);
        }
    }
}
