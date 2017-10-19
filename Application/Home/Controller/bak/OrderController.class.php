<?php 
namespace Home\Controller;
use Think\Controller;
class OrderController extends ConController {
	public function index() {
		$id = $this->islogin();
		if ($id) {
			$MP_sql = M("ordermanage")->where("o_uid=" . $id)->order("id desc")->select();
			foreach ($MP_sql as $key => $val) {
				$val["o_ginfo"] = unserialize($val["o_ginfo"]);
				foreach ($val["o_ginfo"] as $k => $v) {
					$val["o_ginfo"][$k] = M("goodsmanage")->where("id=" . $v["gid"])->field("id,g_thumb,g_title,g_vipprice")->find();
					$val["o_ginfo"][$k]["ginfo"] = $v["ginfo"];
					$val["o_ginfo"][$k]["gnum"] = $v["gnum"];
				}
				if ($val["o_status"] == 0) {
					$list["status0"][] = $val;
				} else if ($val["o_status"] == 1) {
					$list["status1"][] = $val;
				} else if ($val["o_status"] == 2) {
					$list["status2"][] = $val;
				} else if ($val["o_status"] == 3 || $val["o_status"] == 7) {
					$list["status3"][] = $val;
				}
				$MP_list[$key] = $val;
			}
			$this->assign("o", $list);
			$this->assign("order_list", $MP_list);
			$this->display();
		} else {
			$this->redirect("Index/login", "请登录");
		}
	}
	public function OrderCon() {
		$id = $this->islogin();
		if ($id) {
			$MP_data = I("post.");
			if ($MP_data) {
				foreach ($MP_data["cart"] as $key => $val) {
					M("goodsmanage")->where("id=" . $val["gid"])->setInc("g_salesnum", $val["gnum"]);
					M("goodsmanage")->where("id=" . $val["gid"])->setDec("g_stock", $val["gnum"]);
				}
				$MP_data["o_datetime"] = NOW_TIME;
				$MP_data["o_sn"] = "C" . time() . $id;
				$MP_data["o_ginfo"] = serialize($MP_data["cart"]);
				$MP_data["o_uid"] = $id;
				$MP_data["o_status"] = 0;
				$MP_data["o_sid"] = M("usermanage")->where("id=" . $id)->getField("u_thec");
				$MP_sql = M("ordermanage")->data($MP_data)->add();
				if ($MP_sql) {
					session("OrderID", $MP_sql);
					$MP_data["o_gnum"] = 0;
					$MP_address = M("addressmanage")->where("id=" . $MP_data["o_aid"])->find();
					$data['o_provice'] = $MP_address['a_provice'];
					$data['o_city'] = $MP_address['a_city'];
					$data['o_county'] = $MP_address['a_county'];
					$data['o_address'] = $MP_address['a_address'];
					$data['o_postcode'] = $MP_address['a_postcode'];
					$data['o_name'] = $MP_address['a_consignee'];
					$data['o_mobile'] = $MP_address['a_mobile'];
					M('ordermanage')->where('id=' . $MP_sql)->setField($data);
					foreach ($MP_data["cart"] as $key => $val) {
						$MP_goods[$key] = M("goodsmanage")->where("id=" . $val["gid"])->field("g_thumb,g_title,g_vipprice")->find();
						$MP_goods[$key]["gnum"] = $val["gnum"];
						$MP_goods[$key]["ginfo"] = $val["ginfo"];
						$MP_data["o_gnum"]+= $val["gnum"];
						M("cartmanage")->where("c_gid={$val["gid"]} and c_uid={$id}")->delete();
					}
					if ($MP_address['a_provice'] == "新疆维吾尔自治区" || $MP_address['a_provice'] == "西藏自治区") {
						$MP_data['o_price'] = $MP_data['o_price'] + 15 * $MP_data["o_gnum"];
						$this->yf = 15 * $MP_data["o_gnum"];
					}
					$this->assign("goods_list", $MP_goods);
					$this->assign("a", $MP_address);
					$this->assign("o", $MP_data);
				} else {
					$this->error("下单失败，请重新下单");
				}
			} else {
				$oid = I("get.id");
				session("OrderID", $oid);
				$MP_sql = M("ordermanage")->where("id=" . $oid)->find();
				$MP_sql["o_ginfo"] = unserialize($MP_sql["o_ginfo"]);
				$MP_sql["o_gnum"] = 0;
				$MP_address = M("addressmanage")->where("id=" . $MP_sql["o_aid"])->find();
				$data['o_provice'] = $MP_address['a_provice'];
				$data['o_city'] = $MP_address['a_city'];
				$data['o_county'] = $MP_address['a_county'];
				$data['o_address'] = $MP_address['a_address'];
				$data['o_postcode'] = $MP_address['a_postcode'];
				$data['o_name'] = $MP_address['a_consignee'];
				$data['o_mobile'] = $MP_address['a_mobile'];
				M('ordermanage')->where('id=' . $MP_sql["o_aid"])->setField($data);
				//$MP_sql["o_ginfo"]=unserialize($MP_sql["o_ginfo"]);
				foreach ($MP_sql["o_ginfo"] as $key => $val) {
					$MP_goods[$key] = M("goodsmanage")->where("id=" . $val["gid"])->field("g_thumb,g_title,g_vipprice")->find();
					$MP_goods[$key]["gnum"] = $val["gnum"];
					$MP_goods[$key]["ginfo"] = $val["ginfo"];
					$MP_sql["o_gnum"]+= $val["gnum"];
				}
				if ($MP_address['a_provice'] == "新疆维吾尔自治区" || $MP_address['a_provice'] == "西藏自治区") {
					$MP_sql['o_price'] = $MP_sql['o_price'] + 15 * $MP_sql["o_gnum"];
					$this->yf = 15 * $MP_sql["o_gnum"];
				}
				$this->assign("goods_list", $MP_goods);
				$this->assign("a", $MP_address);
				$this->assign("o", $MP_sql);
			}
			if ($MP_sql['o_status'] > 1) {
				$typeCom = $_GET["com"]; //快递公司
				$typeNu = $MP_sql["o_enumber"]; //快递单号
				//               $AppKey=C('LSZ_KUAIDI100_APP_KEY');
				//               $this->$appkey=$AppKey;
				//               if(!empty($AppKey)){
				//                   $url =C('LSZ_KUAIDI100_URL').'/api?id='.$AppKey.'&com='.$typeCom.'&nu='.$typeNu.'&show=0&muti=1&order=asc';
				//                   if (function_exists('curl_init') == 1){
				//                       $curl = curl_init();
				//                       curl_setopt ($curl, CURLOPT_URL, $url);
				//                       curl_setopt ($curl, CURLOPT_HEADER,0);
				//                       curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
				//                       curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
				//                       curl_setopt ($curl, CURLOPT_TIMEOUT,5);
				//                       $get_content = curl_exec($curl);
				//                       curl_close ($curl);
				//                   }else{
				//                       $snoopy = new \Org\Util\Snoopy();
				//                       $snoopy->referer = 'http://www.google.com/';//伪装来源
				//                       $snoopy->fetch($url);
				//                       $get_content = $snoopy->results;
				//                   }
				//                   $get_content=json_decode($get_content,true);
				//                   if($get_content['status'] == 1){
				//                       $content=$get_content['data'];
				//                   }
				//                   $this->status=$get_content['status'];
				//                   $this->content=$content;
				//                   $this->powered = C('LSZ_KUAIDI100_POWERED');
				//               }
				
			}
			$this->display();
		} else {
			$this->redirect("Index/login", "请登录");
		}
	}
	public function OrderDel() {
		$id = $this->islogin();
		if ($id) {
			$oid = I("post.id");
			$MP_goods = M("ordermanage")->where("o_status=0 and id=" . $oid)->getField("o_ginfo");
			if ($MP_goods) {
				$MP_goods = unserialize($MP_goods);
				foreach ($MP_goods as $key => $val) {
					M("goodsmanage")->where("id=" . $val["gid"])->setDec("g_salesnum", $val["gnum"]);
					M("goodsmanage")->where("id=" . $val["gid"])->setInc("g_stock", $val["gnum"]);
				}
			}
			$MP_sql = M("ordermanage")->where("id=" . $oid)->delete();
			if ($MP_sql) {
				echo 1;
			} else {
				echo 0;
			}
		} else {
			$this->redirect("Index/login", "请登录");
		}
	}
	public function OrderSure() {
		$id = $this->islogin();
		if ($id) {
			$oid = I("post.id");
			$MP_sql = M("ordermanage")->where("id=" . $oid)->setField("o_status", 3);
			//            $MP_sql=true;
			if ($MP_sql) {
				$MP_order = M("ordermanage")->where("id=" . $oid)->field("o_uid,o_ginfo,o_sn,o_price")->find();
				$total_fee = $MP_order["o_price"];
				//奖励部分
				$data = M("usermanage")->where("id=" . $MP_order["o_uid"])->field("u_thea,u_theb,u_thec,u_rose,u_isrose")->find();
				//如果该用户不是总代，并且是推广用户，则进行奖励
				if ($data["u_isrose"] == 0 && $data["u_thec"] != 0) {
					$sys_scale = M("sysmanage")->where("id=1")->find();
					if ($data["u_thea"] != 0) {
						$check_dis = M("usermanage")->where("id=" . $data["u_thea"])->getField("u_level");
						if ($check_dis > 0) {
							$money_1 = 0;
							//获取产品三级分销比例，进行判断赋值
							if ($money_1 == 0) {
								$goodslist = unserialize($MP_order["o_ginfo"]);
								foreach ($goodslist as $key => $val) {
									$goodsinfo = M("goodsmanage")->where("id=" . $val["gid"])->field("g_scalea,g_vipprice")->find();
									$goods_a = unserialize($goodsinfo["g_scalea"]);
									$goods_m = $goodsinfo["g_vipprice"];
									if ($goods_a["types"] == 1 && $goods_a["scale"] != 0 && $goods_a["scale"] != "") {
										$money_1+= ($goods_m * $goods_a["scale"] * $val["gnum"]);
									} else if ($goods_a["types"] == 2 && $goods_a["scale"] != 0 && $goods_a["scale"] != "") {
										$money_1+= ($goods_a["scale"] * $val["gnum"]);
									}
								}
							}
							//获取用户分销比例，进行判断赋值
							if ($money_1 == 0) {
								$scale = M("usermanage")->where("id=" . $data["u_thea"])->getField("u_scale");
								if ($scale != "") {
									$money_1 = $total_fee * $scale;
								}
							}
							//获取系统三级分销比例，进行判断赋值
							if ($money_1 == 0) {
								$sys_a = unserialize($sys_scale["s_scalec"]);
								if ($sys_a["types"] == 1 && $sys_a["scale"] != 0 && $sys_a["scale"] != "") {
									$money_1 = $total_fee * $sys_a["scale"];
								} else if ($sys_a["types"] == 2 && $sys_a["scale"] != 0 && $sys_a["scale"] != "") {
									$money_1 = $sys_a["scale"];
								} else {
									$money_1 = $total_fee * 0.1;
								}
							}
							//如果有分销金额产生，则执行分销
							if ($money_1 != 0) {
								M("usermanage")->where("id=" . $data["u_thea"])->setInc("u_commission", $money_1);
								$finance = array("f_types" => 5, "f_text" => "订单" . $MP_order["o_sn"] . "奖励", "f_price" => $money_1, "f_uid" => $data["u_thea"], "f_datetime" => time());
								M("financemanage")->data($finance)->add();
							}
							//进行用户分代资格判断，叠加返佣
							$trose = M("usermanage")->where("id=" . $data["u_thea"])->getField("u_level");
							if ($trose == 2) {
								$sys_t = unserialize($sys_scale["s_trose"]);
								if ($sys_t["types"] == 1 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
									$tmoney = $total_fee * $sys_t["scale"];
								} else if ($sys_t["types"] == 2 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
									$tmoney = $sys_t["scale"];
								} else {
									$tmoney = $total_fee * 0.03;
								}
								if ($tmoney != 0 && $tmoney != "") {
									M("usermanage")->where("id=" . $data["u_thea"])->setInc("u_commission", $tmoney);
									$finance = array("f_types" => 5, "f_text" => "分代：订单" . $MP_order["o_sn"] . "奖励", "f_price" => $tmoney, "f_uid" => $data["u_thea"], "f_datetime" => time());
									M("financemanage")->data($finance)->add();
								}
							}
						}
					}
					if ($data["u_theb"] != 0) {
						$check_dis = M("usermanage")->where("id=" . $data["u_theb"])->getField("u_level");
						if ($check_dis > 0) {
							$money_2 = 0;
							//获取产品二级分销比例，进行判断赋值
							if ($money_2 == 0) {
								$goodslist = unserialize($MP_order["o_ginfo"]);
								foreach ($goodslist as $key => $val) {
									$goodsinfo = M("goodsmanage")->where("id=" . $val["gid"])->field("g_scaleb,g_vipprice")->find();
									$goods_b = unserialize($goodsinfo["g_scaleb"]);
									$goods_m = $goodsinfo["g_vipprice"];
									if ($goods_b["types"] == 1 && $goods_b["scale"] != 0 && $goods_b["scale"] != "") {
										$money_2+= ($goods_m * $goods_b["scale"] * $val["gnum"]);
									} else if ($goods_b["types"] == 2 && $goods_b["scale"] != 0 && $goods_b["scale"] != "") {
										$money_2+= ($goods_b["scale"] * $val["gnum"]);
									}
								}
							}
							//获取用户分销比例，进行判断赋值
							if ($money_2 == 0) {
								$scale = M("usermanage")->where("id=" . $data["u_theb"])->getField("u_scale");
								if ($scale != "") {
									$money_2 = $total_fee * $scale;
								}
							}
							//获取系统二级分销比例，进行判断赋值
							if ($money_2 == 0) {
								$sys_b = unserialize($sys_scale["s_scaleb"]);
								if ($sys_b["types"] == 1 && $sys_b["scale"] != 0 && $sys_b["scale"] != "") {
									$money_2 = $total_fee * $sys_b["scale"];
								} else if ($sys_b["types"] == 2 && $sys_b["scale"] != 0 && $sys_b["scale"] != "") {
									$money_2 = $sys_b["scale"];
								} else {
									$money_2 = $total_fee * 0.2;
								}
							}
							//如果有分销金额产生，则执行分销
							if ($money_2 != 0) {
								M("usermanage")->where("id=" . $data["u_theb"])->setInc("u_commission", $money_2);
								$finance = array("f_types" => 5, "f_text" => "订单" . $MP_order["o_sn"] . "奖励", "f_price" => $money_2, "f_uid" => $data["u_theb"], "f_datetime" => time());
								M("financemanage")->data($finance)->add();
							}
							//进行用户分代资格判断，叠加返佣
							$trose = M("usermanage")->where("id=" . $data["u_theb"])->getField("u_level");
							if ($trose == 2) {
								$sys_t = unserialize($sys_scale["s_trose"]);
								if ($sys_t["types"] == 1 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
									$tmoney = $total_fee * $sys_t["scale"];
								} else if ($sys_t["types"] == 2 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
									$tmoney = $sys_t["scale"];
								} else {
									$tmoney = $total_fee * 0.03;
								}
								if ($tmoney != 0 && $tmoney != "") {
									M("usermanage")->where("id=" . $data["u_theb"])->setInc("u_commission", $tmoney);
									$finance = array("f_types" => 5, "f_text" => "分代：订单" . $MP_order["o_sn"] . "奖励", "f_price" => $tmoney, "f_uid" => $data["u_theb"], "f_datetime" => time());
									M("financemanage")->data($finance)->add();
								}
							}
						}
					}
					if ($data["u_thec"] != 0) {
						$check_dis = M("usermanage")->where("id=" . $data["u_thec"])->getField("u_level");
						if ($check_dis > 0) {
							$money_3 = 0;
							//获取产品一级分销比例，进行判断赋值
							if ($money_3 == 0) {
								$goodslist = unserialize($MP_order["o_ginfo"]);
								foreach ($goodslist as $key => $val) {
									$goodsinfo = M("goodsmanage")->where("id=" . $val["gid"])->field("g_scalec,g_vipprice")->find();
									$goods_c = unserialize($goodsinfo["g_scalec"]);
									$goods_m = $goodsinfo["g_vipprice"];
									if ($goods_c["types"] == 1 && $goods_c["scale"] != 0 && $goods_c["scale"] != "") {
										$money_3+= ($goods_m * $goods_c["scale"] * $val["gnum"]);
									} else if ($goods_c["types"] == 2 && $goods_c["scale"] != 0 && $goods_c["scale"] != "") {
										$money_3+= ($goods_c["scale"] * $val["gnum"]);
									}
								}
							}
							//获取用户分销比例，进行判断赋值
							if ($money_3 == 0) {
								$scale = M("usermanage")->where("id=" . $data["u_thec"])->getField("u_scale");
								if ($scale != "") {
									$money_3 = $total_fee * $scale;
								}
							}
							//获取系统一级分销比例，进行判断赋值
							if ($money_3 == 0) {
								$sys_c = unserialize($sys_scale["s_scalec"]);
								if ($sys_c["types"] == 1 && $sys_c["scale"] != 0 && $sys_c["scale"] != "") {
									$money_3 = $total_fee * $sys_c["scale"];
								} else if ($sys_c["types"] == 2 && $sys_c["scale"] != 0 && $sys_c["scale"] != "") {
									$money_3 = $sys_c["scale"];
								} else {
									$money_3 = $total_fee * 0.3;
								}
							}
							//如果有分销金额产生，则执行分销
							if ($money_3 != 0) {
								M("usermanage")->where("id=" . $data["u_thec"])->setInc("u_commission", $money_3);
								$finance = array("f_types" => 5, "f_text" => "订单" . $MP_order["o_sn"] . "奖励", "f_price" => $money_3, "f_uid" => $data["u_thec"], "f_datetime" => time());
								M("financemanage")->data($finance)->add();
							}
							//进行用户分代资格判断，叠加返佣
							$trose = M("usermanage")->where("id=" . $data["u_thec"])->getField("u_level");
							if ($trose == 2) {
								$sys_t = unserialize($sys_scale["s_trose"]);
								if ($sys_t["types"] == 1 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
									$tmoney = $total_fee * $sys_t["scale"];
								} else if ($sys_t["types"] == 2 && $sys_t["scale"] != 0 && $sys_t["scale"] != "") {
									$tmoney = $sys_t["scale"];
								} else {
									$tmoney = $total_fee * 0.03;
								}
								if ($tmoney != 0 && $tmoney != "") {
									M("usermanage")->where("id=" . $data["u_thec"])->setInc("u_commission", $tmoney);
									$finance = array("f_types" => 5, "f_text" => "分代：订单" . $MP_order["o_sn"] . "奖励", "f_price" => $tmoney, "f_uid" => $data["u_thec"], "f_datetime" => date("Y-m-d", time()));
									M("financemanage")->data($finance)->add();
								}
							}
						}
					}
				}
				echo 1;
			} else {
				echo 0;
			}
		} else {
			$this->redirect("Index/login", "请登录");
		}
	}
	public function OrderRefund() {
		$id = $this->islogin();
		if ($id) {
			$oid = I("post.id");
			$MP_sql = M("ordermanage")->where("id=" . $oid)->setField("o_status", 5);
			if ($MP_sql) {
				echo 1;
			} else {
				echo 0;
			}
		} else {
			$this->redirect("Index/login", "请登录");
		}
	}
	public function FOrderCon() {
		$id = $this->islogin();
		if ($id) {
			$FMoney = M("sysmanage")->where("id=1")->getField("s_trosem");
			$Order = array("f_uid" => $id, "f_price" => $FMoney, "f_sn" => "F" . time() . $id, "f_datetime" => date("Y-m-d H:i:s", time()));
			$MP_order = M("fordermanage")->data($Order)->add();
			session("FOrderID", $MP_order);
			$this->assign("f", $Order);
			$this->display();
		} else {
			$this->redirect("Index/login", "请登录");
		}
	}
	public function refund() {
		$id = I('get.id');
		$info = M('ordermanage')->find($id);
		$info['o_ginfo'] = unserialize($info['o_ginfo']);
		foreach ($info['o_ginfo'] as $k => $v) {
			$info['o_ginfo'][$k] = M("goodsmanage")->where("id=" . $v["gid"])->field("id,g_thumb,g_title,g_vipprice")->find();
			$info['o_ginfo'][$k]["ginfo"] = $v["ginfo"];
			$info['o_ginfo'][$k]["gnum"] = $v["gnum"];
			$info['count']+= $v["gnum"];
		}
		$this->assign('info', $info);
		$this->display();
	}
	public function refundInfo() {
		$id = I('get.id');
		$info = M('ordermanage')->find($id);
		$info['o_ginfo'] = unserialize($info['o_ginfo']);
		foreach ($info['o_ginfo'] as $k => $v) {
			$info['o_ginfo'][$k] = M("goodsmanage")->where("id=" . $v["gid"])->field("id,g_thumb,g_title,g_vipprice")->find();
			$info['o_ginfo'][$k]["ginfo"] = $v["ginfo"];
			$info['o_ginfo'][$k]["gnum"] = $v["gnum"];
			$info['count']+= $v["gnum"];
		}
		$map['r_osn'] = $info['o_sn'];
		$rI = M('refund')->where($map)->find();
		$rI['r_img'] = unserialize($rI['r_img']);
		$this->rI = $rI;
		$this->assign('info', $info);
		$this->display();
	}
	public function saveRefund() {
		$info = I('post.');
		$data['r_reason'] = $info['r_reason'];
		$data['r_osn'] = $info['r_osn'];
		$data['r_oid'] = $info['r_oid'];
		$data['r_status'] = 0;
		$data['r_img'] = serialize($info['g_image']);
		M('ordermanage')->where('id=' . $info['r_oid'])->setField("o_status", 5);
		if (M('refund')->data($data)->add()) {
			echo 1;
		} else {
			echo 0;
		}
	}
	public function summernote() {
		$upload = new \Think\Upload(); // 实例化上传类
		$upload->maxSize = 104857600;
		$upload->exts = array(); // 设置附件上传类型
		$upload->saveName = 'mp';
		$upload->rootPath = './Uploads/'; // 设置附件上传根目录
		// 上传文件
		$info = $upload->upload();
		if (!$info) { // 上传错误提示错误信息
			$this->error($upload->getError());
		} else { // 上传成功 获取上传文件信息
			foreach ($info as $file) {
				$file_info[] = "/uploads/" . $file['savepath'] . $file['savename'];
			}
			die(json_encode($file_info));
		}
	}
}
