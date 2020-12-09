<?php
/*
* 自定义公共函数文件
*/
/*
* 根据id返回对应名称 用于数组转换
* @param $id int 传入的数组id（键）
* @param $type string 数组名称
* @return string
*/
function id2name($type,$id) {
    return (C($type) ? C($type)[$id] : '');
}
/*
* 根据id返回对应名称 用于数组转换
* @param $name int 传入的数组值
* @param $type string 数组名称
* @return string
*/
function name2id($type,$name) {
    $return = '';
    $data = C($type);
    if(!$data) return $return;
    foreach($data as $k=>$v) {
        if($v == $name) {$return = $k; break;}
    }
    return $return;
}
/*
* 根据键名获取二维数组中对应值的集合
* @param $arr array 二位数组
* @param $key string 获取值集合的键名
* @return array
*/
function getFieldsByKey($arr,$key) {
    $return = array();
    if(is_array($arr)) {
        foreach($arr as $k=>$v) {
            if($v[$key] !== null) $return[] = $v[$key];
        }
    }
    return $return;
}
/*
* 根据键值对获取二维数组中对应的一维数组
* @param $arr array 二位数组
* @param $key string 键
* @param $value string 值
* @return array
*/
function getArrayByKeyAndValue($arr,$key,$value) {
    $return = array();
    if(is_array($arr)) {
        foreach($arr as $k=>$v) {
            if($v[$key] == $value) { $return = $v; break;}
        }
    }
    return $return;
}

/**
 * curl get 方式获取数据
 * @param string $url
 */
function curl_get($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXY, C('PROXY_IP'));
    curl_setopt($ch, CURLOPT_PROXYPORT, C('PROXY_PORT'));
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
    $tmp = curl_exec ($ch);
    curl_close($ch);
    return $tmp;
}
/**
 * curl post 方式获取数据
 * @param string 	$url 	要请求的url
 * @param array 	$params 要传递的参数数组
 * @param array 	$header 要发送的头信息
 * @param int 	    $time   超时时间，缺省10秒
 */
function curl_post($url,$params,$header=array(),$time=10) {
    if(is_array($params)) $paramsStr =  http_build_query($params);
    else $paramsStr = $params;
    $paramsStr = htmlspecialchars_decode($paramsStr);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $paramsStr);
    curl_setopt($ch, CURLOPT_PROXY, C('PROXY_IP'));
    curl_setopt($ch, CURLOPT_PROXYPORT, C('PROXY_PORT'));
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
    if(count($header) > 0) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $returnValue = curl_exec($ch);
    curl_close($ch);
    return $returnValue;
}
/*
* 生成图片路径
* @param string $img 图片路径
* @param string $size 图片尺寸 100_100
* @return string   模版中调用方法 {$img_id|createImageUrl=###,'80_80','png'}
*/
function createImageUrl($img,$size='0_0',$ext='jpeg') {
    if(!$img) return '';
    return C('SERVER_IMAGE_URL').'img/'.$size.'/'.base64_encode($img).'.'.$ext;
}
/*
* 后台判断管理员是否超级管理员
* @param int $uid 管理员ID 默认取当前登录管理员
* @return boolean  通过验证返回true;失败返回false
*/
function is_super_admin($uid=0) {
    $uid = $uid > 0 ? $uid : session('admin_uid');
    $gorup_ids = M('admin_group_access')->where(array('uid'=>$uid))->getField('group_id',true);
    return in_array(C('DEFAULT_ADMIN_GROUP'),$gorup_ids) ? true : false;
}
/*
* 后台判断权限
* @param string or array $rule 权限规则唯一标识 可以为数组和字符串
* @param string $mode        执行check的模式
* @param string $relation    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
* @return boolean  通过验证返回true;失败返回false
*/
function check_admin_access($rule, $type=1, $mode='url', $relation='or') {
    $auth = new Think\Auth();
    $admin_id = session('admin_uid');
    if(!$admin_id) return false;
    return is_super_admin($admin_id) ? true : $auth->check($rule,$admin_id,$type,$mode,$relation);
}
/*
* 截取字符串 支持中文
* @param string $str 判断截取的字符串
* @param int $start 判断截取的开始位置
* @param int $len 判断截取的字符串长度
* @return string
*/
function tksubstr($str,$start,$len) {
    $strlen = mb_strlen($str,'utf-8');
    return ($strlen > $len) ? \Org\Util\String::msubstr($str,$start,$len) : $str;
}
/*
* 比较输出
* @param $param1,$param2  要比较的2个字符串
* @param $return          比较成功后输出的字符串
* @parma $check           检查条件，默认等于 ==，支持（==,!=,>,<,>=,<=,===）
* @return string
*/
function set_class($param1,$param2,$return='',$check='==') {
    $exp = false;
    switch($check) {
        case '==': $exp = ((string)$param1 == (string)$param2);break;
        case '!=': $exp = ($param1 != $param2);break;
        case '>': $exp = ($param1 > $param2);break;
        case '<': $exp = ($param1 < $param2);break;
        case '>=': $exp = ($param1 >= $param2);break;
        case '<=': $exp = ($param1 <= $param2);break;
        case '===': $exp = ($param1 === $param2);break;
        case 'in': $exp = (in_array($param1,$param2));break;
    }
    return ($exp ? $return : '');
}
/*
* 返回列表公共函数
* @param string $url  要跳转的url    Test/test
* @return string
*/
function back_list_url($url='') {
    $url = $url ? $url : CONTROLLER_NAME.'/index';
    $referer = $_SERVER['HTTP_REFERER'];
    $bool = strrpos($referer,$url);
    if($bool) session($url,$referer);
    else $referer = session($url) ? session($url) : U($url);
    return $referer;
}
/*
* 创建订单ID
* @return string
*/
function createOrderId($date=''){
    if(!$date) $date = time();
    $code = date('Ymd');
    $count = M('order_tmpid')->data(array('create_time'=>  date('Y-m-d H:i:s')))->add();
    $len = 8 - strlen($count);
    if ($len > 0)
        for ($i = 0; $i < $len; $i++)
            $count = '0' . $count;
    return $code . $count;
}

/**
 * 生成优惠码
 * @return type
 */
function createCouponCode(){
    $code = (new \Org\Util\ThinkString())->randString(6, 2);
    $count = M('coupon_code_info')->where(array('coupon_code'=>$code))->count();
    if($count) createCouponCode ();
    return $code;
}

/**
 * 货币格式化
 * @param type $price
 */
function price_format($price){
    return number_format($price,2,'.','');
}

/**
 * 发送短信
 * @param type $send_data
 * @return type
 */
function sendSms($send_data){
    $url = 'http://101.251.214.153:8901/MWGate/wmgw.asmx/MongateSendSubmit';
    $data['userId'] = 'JI0209';
    $data['password'] = '966967';
    $data['pszMobis'] = $send_data['phone'];
    $data['pszMsg'] = $send_data['content'];
    $data['iMobiCount'] = '1';
    $data['pszSubPort'] = '*';
    $data['MsgId'] = (new \Org\Util\ThinkString())->randString(64, 1);
    $result = curl_post($url, $data);
    $send_data['send_date'] = date('Y-m-d H:i:s');
    $send_data['send_log'] = serialize($result);
    //发送失败
    if(count_chars($result) < 15){
        $send_data['send_status'] = 2;
    }
    $rs = M('sms_log')->data($send_data)->add();
    return $rs;
}

/**
 * 资源管理平台调用CRM积分扣减
 * @param type $data
 */
function changePoints($data){
    $conn = oci_connect(C('DB_ORICLE')['DB_USER'], C('DB_ORICLE')['DB_PWD'], '//'.C('DB_ORICLE')['DB_HOST'].'/'.C('DB_ORICLE')['DB_NAME']);
    if (!$conn) {
      $e = oci_error();
      return false;
    }else {
        $member_id = $data['member_id'];
        $point = $data['point'];
        $orderid = $data['OrderId'];
        $out_status = '';
        //设置绑定
        $sql_sp = "BEGIN YL_UTN_MEMBERSHIP.CANCEL_POINTS_ZY(:IN_MEMBERID,:IN_POINTS,:IN_ORDERID,:OUT_STATUS); END;";
        $stmt = oci_parse($conn, $sql_sp);
        //执行绑定
        oci_bind_by_name($stmt, ":IN_MEMBERID",$member_id, 100);
        oci_bind_by_name($stmt, ":IN_POINTS",$point, 10);
	oci_bind_by_name($stmt, ":IN_ORDERID",$orderid, 36);
	oci_bind_by_name($stmt, ":OUT_STATUS",$out_status, 10);
        oci_execute($stmt);
        //释放资源  
       oci_free_statement($stmt);  
       oci_close($conn);
       if(intval($out_status) == 100) {
           \Org\My\MyLog::write_log('【SUCCESS】执行时间：'.  date('Y-m-d H:i:s').',资源平台数据：'.  json_encode($data).'，CRM返回数据：'.  serialize($out_status)."\n", 'crm-change_point.log',date('Y-m-d'));
           return true;
       }else {
          \Org\My\MyLog::write_log('【ERROR】执行时间：'.  date('Y-m-d H:i:s').',资源平台数据：'.  json_encode($data).'，CRM返回数据：'.  serialize($out_status)."\n", 'crm-change_point.log',date('Y-m-d'));
          return FALSE;
       }
    }
}

/**
 * 获取会员信息
 * @param type $data
 */
function getMemberPoint($data){
    $conn = oci_connect(C('DB_ORICLE')['DB_USER'], C('DB_ORICLE')['DB_PWD'], '//'.C('DB_ORICLE')['DB_HOST'].'/'.C('DB_ORICLE')['DB_NAME']);
    if (!$conn) {
        $e = oci_error();
        return false;
    }else {
        //设置绑定
        $phone = $data['member_account'];
        $cardno = $data['card_number'];
        $sql_sp = "BEGIN YL_UTN_MEMBERSHIP.QUERYUSERINFO(:IN_PHONENO,:IN_CARDNO,:userinfo); END;";
        $stmt = oci_parse($conn, $sql_sp);
        //执行绑定
        oci_bind_by_name($stmt, ":IN_PHONENO",$phone, 11);//参数说明：绑定php变量$id到位置:id，并设定绑定长度16位
        oci_bind_by_name($stmt, ":IN_CARDNO",$cardno, 20);
        $userinfo = oci_new_cursor($conn);
        oci_bind_by_name($stmt, ':userinfo',$userinfo, -1,OCI_B_CURSOR);
        oci_execute($stmt);
        oci_execute($userinfo);
        $user_info = oci_fetch_array($userinfo,OCI_BOTH);
        //释放资源
        oci_free_statement($stmt);
        oci_free_statement($userinfo);
        oci_close($conn);
        return $user_info;
    }
}





/**
 * 拆单，单一个店铺的订单不拆，多个店铺拆成多个订单，原单不动，借鉴京东订单管理,支付不拆分
 * @param type $base_order_id
 */
function part_order($base_order_id){
    //已经拆分
    $is_part = M('order_base_info')->where(array('parent_id'=>$base_order_id))->count();
    if($is_part) return true;
    //查找主商品
    $order_goods_list = M('order_goods_info')->where(array('order_id'=>$base_order_id,'is_primary'=>1))->select();
    $shops_codes = getFieldsByKey($order_goods_list, 'shops_code');
    $shops_codes = array_unique($shops_codes);
    //主商品只有一个店铺时候不拆单子
    if(count($shops_codes) == 1){
        $order_goods = $order_goods_list[0];
        $where = array('order_id'=>$order_goods['order_id']);
        $data = array('shops_code'=>$order_goods['shops_code'],'update_time'=>  date('Y-m-d H:i:s'));
        return M('order_base_info')->where($where)->data($data)->save();
    }else{//需要拆单
        $order_info = M('order_base_info')->where(array('order_id'=>$base_order_id))->find();
        //发票
        if($order_info['is_invoice']){
            $invoice_info = M('order_invoice_info')->where(array('order_id'=>$order_info['order_id']))->find();
        }
        $new_order_goods_list = array();
        foreach($order_goods_list as $k=>$order_goods){
            $shops_order_goods_list = $new_order_goods_list[$order_goods['shops_code']];
            if(!$shops_order_goods_list) $shops_order_goods_list = array();
            $shops_order_goods_list[] = $order_goods;
            $new_order_goods_list[$order_goods['shops_code']] = $shops_order_goods_list;
        }
        //订单日志
        $order_loginfo_list = M('order_loginfo')->where(array('order_id'=>$base_order_id))->select();
        //订单支付信息
        $base_payment_info = M('order_payment_info')->where(array('order_id'=>$base_order_id))->find();
        //具体的生成新的子订单
        $child_order_list = array();
        $child_order_goods_list = array();
        $child_invoice_list = array();
        $child_pay_discount_list = array();
        $child_order_goods_discount_list = array();
        $child_order_loginfo_list = array();
        $update_time = date('Y-m-d H:i:s');
        foreach($new_order_goods_list as $shops_code=>$child_order_goods_arr){
            $total_price = 0;
            $discount_value = 0;
            $temp_order_info = $order_info;
            $temp_order_id = (new \Org\Util\ThinkString())->uuid();
            $temp_order_info['parent_id'] = $order_info['order_id'];
            $temp_order_info['order_id'] = $temp_order_id;
            $temp_order_info['order_cur_id'] = createOrderId();
            $temp_order_info['shops_code'] = $shops_code;
            $must_point = 0;
            $use_total_point = 0;
            foreach($child_order_goods_arr as $k=>$child_order_goods){
                $old_order_goods_id = $child_order_goods['order_goods_id'];
                $temp_order_goods_id = (new \Org\Util\ThinkString())->uuid();
                $child_order_goods['order_id'] = $temp_order_id;
                $child_order_goods['order_goods_id'] = $temp_order_goods_id;
                //订单商品
                $child_order_goods_list[] = $child_order_goods;
                //带有赠品，需要把赠品附带给主商品
                if($child_order_goods['order_goods_type'] == 2){
                    $gift_list = M('order_goods_info')->where(array('primary_order_goods_id'=>$old_order_goods_id,'is_primary'=>0))->select();
                    foreach($gift_list as $gift_goods){
                        $gift_goods['primary_order_goods_id'] = $temp_order_goods_id;
                        $gift_goods['order_goods_id'] = (new \Org\Util\ThinkString())->uuid();
                        $gift_goods['create_time'] = $update_time;
                        $gift_goods['update_time'] = $update_time;
                        $child_order_goods_list[] = $gift_goods;
                    }
                }
                $total_price += $child_order_goods['goods_price'] * $child_order_goods['goods_number'];
                if($child_order_goods['integral']) $must_point += $child_order_goods['max_integral'] * $child_order_goods['goods_number'];
                //订单商品优惠
                $goods_discount_list = M('order_goods_discount_info')->where(array('order_goods_id'=>$old_order_goods_id))->select();
                foreach ($goods_discount_list as $goods_discount){
                    unset($goods_discount['id']);
                    if($goods_discount['discount_type'] == 5) $use_total_point += intval ($goods_discount['discount_code']);
                    else $discount_value += $goods_discount['discount_value'];
                    $goods_discount['order_id'] = $temp_order_id;
                    $goods_discount['order_goods_id'] = $temp_order_goods_id;
                    $child_order_goods_discount_list[] = $goods_discount;
                }
            }
            //发票
            if($invoice_info){
                $temp_invoice_info = $invoice_info;
                unset($temp_invoice_info['id']);
                $temp_invoice_info['order_id'] = $temp_order_id;
                $child_invoice_list[] = $temp_invoice_info;
            }
            //订单日志
            foreach($child_order_loginfo_list as $log_key=>$order_loginfo){
                unset($order_loginfo['id']);
                $order_loginfo['order_id'] = $temp_order_id;
                $child_order_loginfo_list[] = $order_loginfo;
            }
            $child_order_loginfo_list[] = array('order_id'=>$temp_order_id,'comment'=>'订单拆单成功','type'=>1,'operate_user_id'=>'','create_time'=>  date('Y-m-d H:i:s'),'update_time'=>  date('Y-m-d H:i:s'));
            //总的优惠
            if($use_total_point) $discount_value += (($use_total_point - $must_point)/C('INTEGRAL_RATE')['point']) * C('INTEGRAL_RATE')['rmb'];
            $pay_total_cash = $total_price - $discount_value;
            $temp_order_info['total_value'] = $total_price;
            $temp_order_info['pay_total_cash'] = $pay_total_cash;
            $temp_order_info['pay_value'] = $pay_total_cash;
            $temp_order_info['create_time'] = $update_time;
            $temp_order_info['update_time'] = $update_time;
            $temp_order_info['is_part'] = 1;//已拆分
            $child_order_list[] = $temp_order_info;
            //订单支付信息
            if($base_payment_info){
                unset($base_payment_info['id']);
                $temp_payment_info = $base_payment_info;
                $temp_payment_info['order_id'] = $temp_order_id;
                $temp_payment_info['payment_fee'] = $pay_total_cash;
                $child_payment_list[] = $temp_payment_info;
            }
        }
        M('order_base_info')->where(array('order_id'=>$order_info['order_id']))->data(array('order_status'=>2))->save();
        //保存订单数据
        $order_bool = M('order_base_info')->addAll($child_order_list);
        //保存订单商品
        $order_goods_bool = M('order_goods_info')->addAll($child_order_goods_list);
        //保存订单发票
        $order_invoice_bool = true;
        if($child_invoice_list) $order_invoice_bool = M('order_invoice_info')->addAll($child_invoice_list);
        //保存订单支付信息
        $order_payment_bool = true;
        if($child_payment_list) $order_payment_bool = M('order_payment_info')->addAll($child_payment_list);
        //保存订单商品优惠信息
        $order_goods_discount_bool = true;
        if($child_order_goods_discount_list){
            $coupon_code_arr = array();
            $coupon_arr = array();
            $total_point_arr = array();
            $storage_arr = array();
            foreach($child_order_goods_discount_list as $k=>$goods_discount){
                //优惠券
                if($goods_discount['discount_type'] == 3){
                    $discount_value = $coupon_arr[$goods_discount['order_id']][$goods_discount['discount_code']];
                    $discount_value += $goods_discount['discount_value'];
                    $coupon_arr[$goods_discount['order_id']][$goods_discount['discount_code']] = $discount_value;
                }
                //优惠码
                if($goods_discount['discount_type'] == 4){
                    $discount_value = $coupon_code_arr[$goods_discount['order_id']][$goods_discount['discount_code']];
                    $discount_value += $goods_discount['discount_value'];
                    $coupon_code_arr[$goods_discount['order_id']][$goods_discount['discount_code']] = $discount_value;
                }
                //积分
                if($goods_discount['discount_type'] == 5){
                    $total_point = $total_point_arr[$goods_discount['order_id']];
                    $total_point += $goods_discount['discount_code'];
                    $total_point_arr[$goods_discount['order_id']] = $total_point;
                }
                //储值卡
                if($goods_discount['discount_type'] == 6){
                    $storage_money = $storage_arr[$goods_discount['order_id']];
                    $storage_money += $goods_discount['discount_value'];
                    $storage_arr[$goods_discount['order_id']] = $storage_money;
                }
            }
            foreach($coupon_code_arr as $order_id=>$temp_coupon_code_arr){
                foreach($temp_coupon_code_arr as $coupon_code=>$discount_value){
                    $child_pay_discount_list[] = array(
                        'order_id'=>$order_id,
                        'discount_type'=>2,
                        'discount_code'=>$coupon_code,
                        'discount_value'=>$discount_value,
                        'create_time'=>  date('Y-m-d H:i:s')
                    );
                }
            }
            foreach($coupon_arr as $order_id=>$temp_coupon_arr){
                foreach($temp_coupon_arr as $coupon_code=>$discount_value){
                    $child_pay_discount_list[] = array(
                        'order_id'=>$order_id,
                        'discount_type'=>3,
                        'discount_code'=>$coupon_code,
                        'discount_value'=>$discount_value,
                        'create_time'=>  date('Y-m-d H:i:s')
                    );
                }
            }
            foreach($total_point_arr as $order_id=>$total_point){
                $child_pay_discount_list[] = array(
                    'order_id'=>$order_id,
                    'discount_type'=>4,
                    'discount_code'=>$total_point,
                    'discount_value'=>($total_point/C('INTEGRAL_RATE')['point']) * C('INTEGRAL_RATE')['rmb'],
                    'create_time'=>  date('Y-m-d H:i:s')
                );
            }
            foreach($storage_arr as $order_id=>$storage_money){
                $child_pay_discount_list[] = array(
                    'order_id'=>$order_id,
                    'discount_type'=>5,
                    'discount_code'=>'',
                    'discount_value'=>$storage_money,
                    'create_time'=>  date('Y-m-d H:i:s')
                );
            }
            $order_goods_discount_bool = M('order_goods_discount_info')->addAll($child_order_goods_discount_list);
        }
        //保存订单优惠信息
        $order_discount_bool = true;
        if($child_pay_discount_list) {
            $order_discount_bool = M('order_pay_discount_info')->addAll($child_pay_discount_list);
        }
        $order_log_bool = M('order_loginfo')->addAll($child_order_loginfo_list);
        return $order_log_bool && $order_bool && $order_goods_bool && $order_invoice_bool && $order_discount_bool && $order_goods_discount_bool && $order_payment_bool;
    }
}
/**
 * 生成使用码
 * @return type
 */
function createUserCode(){
    $use_code = (new \Org\Util\ThinkString())->randString(6, 2);
    $count = M('member_use_code')->where(array('use_code'=>$use_code))->count();
    if($count) $use_code = createUserCode ();
    else return $use_code;
}

/**
 * 生成订单日志
 */
function order_loginfo($order_id,$comment,$type=1,$user_id){
    $data = array(
        'order_id'=>$order_id,
        'comment'=>$comment,
        'type'=>$type,
        'operate_user_id'=>$user_id,
        'create_time'=>  date('Y-m-d H:i:s'),
        'update_time'=>  date('Y-m-d H:i:s'),
    );
    return M('order_loginfo')->data($data)->add();
}

/**
 * 去掉订单
 */
function cancel_order($order_info){
        //积分
        $use_points = M('order_pay_discount_info')->where(array('order_id'=>$order_info['order_id'],'discount_type'=>4))->getField('discount_code');
        //储值卡
        $use_money = M('order_pay_discount_info')->where(array('order_id'=>$order_info['order_id'],'discount_type'=>5))->getField('discount_value');
        //如果有积分或者储值卡生成退货订单
        $reverse_order_bool = true;
        if($use_points || $use_money){
            $reverse_data = array('order_id'=>$order_info['order_id'],'forward_point'=>$use_points,'forward_storage_money'=>$use_money,'reason'=>'取消订单生成退货单');
            $reverse_order_bool = cancel_reverse_order($reverse_data);
        }
        $cancel_order_data['cancel_way'] = 3;
        $cancel_order_data['order_status'] = 11;
        $cancel_order_data['update_time'] = date('Y-m-d H:i:s');
        //订单取消
        $order_bool = M('order_base_info')->where(array('order_id'=>$order_info['order_id']))->data($cancel_order_data)->save();
        //订单商品取消
        $cancel_order_goods_data['order_goods_status'] = 11;
        $cancel_order_goods_data['update_time'] = date('Y-m-d H:i:s');
        $order_goods_bool = M('order_goods_info')->where(array('order_id'=>$order_info['order_id']))->data($cancel_order_goods_data)->save();
        //退还库存
        $order_goods_list = M('order_goods_info')->where(array('order_id'=>$order_info['order_id']))->select();
        foreach($order_goods_list as $k=>$order_goods){
            M('goods_stock_info')->where(array('goods_code'=>$order_goods['goods_code']))->setDec('occupy_stocks',$order_goods['goods_number']);
        }
        //订单日志
        $loginfo_data['order_id'] = $order_info['order_id'];
        $loginfo_data['comment'] = '订单被取消';
        $loginfo_data['type'] = 1;
        $loginfo_data['operate_user_id'] = '';
        $loginfo_data['create_time'] = date("Y-m-d H:i:s");
        $loginfo_data['update_time'] = date("Y-m-d H:i:s");
        $log_bool = M('order_loginfo')->data($loginfo_data)->add();
        if($reverse_order_bool && $order_bool && $order_goods_bool && $log_bool){
            return true;
        }else{
            return FALSE;
        }
}
/**
 * 获取用户可用的积分值
 * @return type
 */
function refundTicket($data){
    try {
       $member_info = M('user_member_base_info')->where(array('member_id'=>$data['MemberId']))->field('member_account,card_number')->find();
       $client = new \SoapClient(C('CRM_WSAL_API_URL'),['soap_version' => SOAP_1_1]);
       $parm = array(
           'ValidateCode'=>'CrmConfig',
           'UserType'=>'100000003',
           'PhoneNumber'=>$member_info['member_account'],
           'OrderId'=>$data['OrderId'],
           'Refund'=>$data['UseBalance'],
       );
       $result = $client->RefundTicket(array('request'=>$parm));
       \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',资源平台数据：'.  json_encode($parm).'，CRM返回数据：'.  serialize($result)."\n", 'crm-refund-ticket.log',date('Y-m-d'));
       if($result->RefundTicketResult->ResultCode == 0) return array('success'=>true);
       else return array('success'=>false,'info'=> trim ($result->RefundTicketResult->ResultDesc, '参数无效:'));  
   } catch (Exception $exc) {
       \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',资源平台数据：'.  json_encode($data).'，调用储值卡支付失败：'.serialize($exc)."\n", 'crm-refund-ticket.log',date('Y-m-d'));
       return array('success'=>false,'info'=>'调用储值卡支付失败');
   }
}

/**
 * 取消生成反向退货订单
 * 条件：订单使用了积分、储值卡
 * @param type $reverse_data【order_id,reason，forward_point，forward_storage_money】
 */
function cancel_reverse_order($reverse_data){
        $order_id = $reverse_data['order_id'];
        $use_point = intval($reverse_data['forward_point']);
        $use_storage_money = floatval($reverse_data['forward_storage_money']);
        $where = array('order_id'=>$order_id);
        $order_info = M('order_base_info')->where($where)->find();
        $comment = $reverse_data['reason'];
        $goods_list = M('order_goods_info')->where($where)->select();
        $reverse_order_id = (new \Org\Util\ThinkString())->uuid();
        foreach($goods_list as $k=>$order_goods){
            $reverse_goods_data[] = array(
                'order_goods_id'=>(new \Org\Util\ThinkString())->uuid(),
                'forward_order_goods_id'=>$order_goods['order_goods_id'],
                'reverse_order_id'=>$reverse_order_id,
                'numbers'=>$order_goods['goods_number'],
                'order_goods_type'=>$order_goods['order_goods_type'],
                'is_primary'=>$order_goods['is_primary'],
                'primary_order_goods_id'=>$order_goods['primary_order_goods_id'],
                'goods_code'=>$order_goods['goods_code'],
                'goods_category_code'=>$order_goods['goods_category_code'],
                'goods_type_code'=>$order_goods['goods_type_code'],
                'goods_name'=>$order_goods['goods_name'],
                'goods_materiel_code'=>$order_goods['goods_materiel_code'],
                'goods_price'=>$order_goods['goods_price'],
                'promotion_content'=>$order_goods['promotion_content'],
                'shops_code'=>$order_goods['shops_code'],
                'create_time'=>date('Y-m-d H:i:s')
            );
        }
        $reverse_order_data = array(
            'reverse_order_id'=>$reverse_order_id,
            'forward_order_id'=>$order_info['order_id'],
            'order_type'=>3,//退货（取消）订单
            'order_cur_id'=>  createOrderId(),
            'member_id'=>$order_info['member_id'],
            'create_time'=>  date('Y-m-d H:i:s'),
            'update_time'=>  date('Y-m-d H:i:s'),
            'shops_code'=>$order_info['shops_code'],
            'order_status'=>50,//完成
            'forward_order_cur_id'=>$order_info['order_cur_id'],
            'comment'=>$comment
        );
        $reverse_money_data = array();
        $reverse_money_data['reverse_order_id'] = $reverse_order_data['reverse_order_id'];
        $reverse_money_data['forward_order_id'] = $reverse_order_data['forward_order_id'];
        $reverse_money_data['forward_point'] = $use_point;
        $reverse_money_data['reverse_point'] = $use_point;
        $reverse_money_data['forward_storage_money'] = $use_storage_money;
        $reverse_money_data['reverse_storage_money'] = $use_storage_money;
        $reverse_money_data['reverse_status'] = 2;//已退款
        $reverse_money_data['create_time'] = date('Y-m-d H:i:s');
        $reverse_money_data['update_time'] = date('Y-m-d H:i:s');
//        D('ReverseOrder')->startTrans();
        M('reverse_order_base_info')->startTrans();
//        $reverse_order_bool = D('ReverseOrder')->data($reverse_order_data)->add();
        $reverse_order_bool = M('reverse_order_base_info')->data($reverse_order_data)->add();
        $reverse_order_goods_bool = M('reverse_order_goods_info')->addAll($reverse_goods_data);
        $reverse_money_bool = M('reverse_money_info')->data($reverse_money_data)->add();
        if($reverse_money_bool && $reverse_order_bool && $reverse_order_goods_bool){
            M('reverse_order_base_info')->commit();
            return true;
        }else{
            M('reverse_order_base_info')->rollback();
            return false;
        }
}
?>