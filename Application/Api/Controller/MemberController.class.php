<?php


namespace Api\Controller;

class MemberController extends BaseController{
    
    /**
     * CRM同步绑定优惠券/商品接口
     */
    public function bind_member_wealth(){
        if(IS_POST){
            $token = I('token','','trim,htmlspecialchars');
            $base_data =  file_get_contents('php://input');
            if(!$base_data) $this->customAjaxReturn (501, FALSE, '没有任何数据');
            if(!$this->check_token($token, $base_data)) $this->customAjaxReturn (501,FALSE,'token验证失败');
            $data = json_decode($base_data, true);
            if(!$data['member_id']) $this->customAjaxReturn (502,FALSE,'会员ID不能为空');
            if(!$data['type']) $this->customAjaxReturn (502,FALSE,'类型不能为空');
            if(!$data['bind_code']) $this->customAjaxReturn (502,FALSE,'编码不能为空');
            if(!$data['number']) $this->customAjaxReturn (502,FALSE,'数量不能为空');
            if(!$data['comment']) $this->customAjaxReturn (502,FALSE,'备注不能为空');
            if(!in_array($data['type'], array(1,2))) $this->customAjaxReturn (502,FALSE,'类型错误');
            M('coupon_user_info')->startTrans();
            //优惠券
            if($data['type'] == 1){
                $coupon_info = M('coupon_base_info')->where(array('coupon_code'=>$data['bind_code']))->find();
                if(!$coupon_info) $this->customAjaxReturn (503, FALSE, '优惠券不存在');
                $coupon_user_id = (new \Org\Util\ThinkString())->uuid();
                //用户优惠券
                $coupon_user_data['coupon_user_id'] = $coupon_user_id;
                $coupon_user_data['member_id'] = $data['member_id'];
                $coupon_user_data['coupon_id'] = $coupon_info['coupon_id'];
                $coupon_user_data['coupon_code'] = $coupon_info['coupon_code'];
                $coupon_user_data['coupon_value'] = $coupon_info['coupon_value'];
                $coupon_user_data['is_pc'] = $coupon_info['is_pc'];
                $coupon_user_data['is_wap'] = $coupon_info['is_wap'];
                $coupon_user_data['is_app'] = $coupon_info['is_app'];
                $coupon_user_data['valid_start_time'] = $coupon_info['valid_start_time'];
                $coupon_user_data['valid_end_time'] = $coupon_info['valid_end_time'];
                $coupon_user_data['coupon_status'] = 10;
                $coupon_user_data['update_time'] = date('Y-m-d H:i:s');
                $coupon_user_data['create_time'] = date('Y-m-d H:i:s');
                $add_bool = M('coupon_user_info')->data($coupon_user_data)->add();
            }else{//商品
                $use_code_str = '';
                $goods_info = M('goods_base_info')->where(array('goods_code'=>$data['bind_code']))->find();
                //商品为大礼包
                if($goods_info['goods_type'] == 2){
                    $data['comment'] = $data['comment'].',大礼包商品消费码,大礼包名称：'.$goods_info['goods_name'];
                    $data['comment'] = trim($data['comment'],',');
                    $relation_goods = json_decode($goods_info['relation_goods'],true);
                    $goods_codes = getFieldsByKey($relation_goods, 'goods_code');
                    $goods_list = M('goods_base_info')->where(array('goods_code'=>array('in',$goods_codes)))->select();
                }else{
                    $goods_list = array($goods_info);
                }
                foreach($goods_list as $k=>$goods_info){
                   if(!$goods_info) $this->customAjaxReturn (503, FALSE, '商品不存在');
                    if($goods_info['is_object']) $code_type = 2;
                    else $code_type = 1;
                    $valid_type = 1;
                    $valid_time = strtotime(date("Y-m-d".' 23:59:59',strtotime("+3 month")));
                    for($i=1;$i<= $data['number'];$i++){
                        $use_id = (new \Org\Util\ThinkString())->uuid();
                        $use_code = $this->get_user_code();
                        $use_code_data[] = array(
                            'use_id'=>$use_id,
                            'member_id'=>$data['member_id'],
                            'use_code'=>$use_code,
                            'code_type'=>$code_type,
                            'status'=>10,
                            'remark'=>$data['comment'],
                            'create_time'=>  date('Y-m-d H:i:s'),
                            'update_time'=>  date('Y-m-d H:i:s'),
                            'goods_code'=>$goods_info['goods_code'],
                            'order_id'=>$data['order_id'],
                            'is_valid'=>1,
                            'valid_type'=>$valid_type,
                            'valid_time'=>$valid_time,
                            'buy_type'=>2
                        );
                        $use_code_str .= $use_code.',';
                    } 
                }
                $use_code_bool = M('member_use_code')->addAll($use_code_data);
                $add_bool = $use_code_bool;
            }
            if($add_bool){
                M('coupon_user_info')->commit();
                $this->ajaxReturn(array('code'=>200,'success'=>true,'message'=>'同步成功','use_code'=>trim($use_code_str,',')));
            }
            else {
                M('coupon_user_info')->rollback();
                $this->customAjaxReturn (500, FALSE, '同步失败');
            }
        }
        $this->customAjaxReturn (510,FALSE,'请用POST方法调用');
    }
    
    /**
     * CRM同步会员信息到资源管理平台
     */
    public function update_member(){
        if(IS_POST){
            $token = I('token','','trim,htmlspecialchars');
            $base_data =  file_get_contents('php://input');
            if(!$base_data) $this->customAjaxReturn (501, FALSE, '没有任何数据');
            if(!$this->check_token($token, $base_data)) $this->customAjaxReturn (501,FALSE,'token验证失败');
            $data = json_decode($base_data, true);
            if(!$data['MemberId']) $this->customAjaxReturn (502,FALSE,'会员ID不能为空');
            if(!$data['MobilePhone']) $this->customAjaxReturn (502,FALSE,'手机号码不能为空');
            $user_data['member_id'] = $data['MemberId'];
            $user_data['member_code'] = $data['WebMemberId'];
            $user_data['member_pass'] = '';
            $user_data['member_account'] = $data['MobilePhone'];
            $user_data['username'] = $data['MobilePhone'];
            $user_data['nick_name'] = $data['MemberName'];
            $user_data['register_ip'] = '';
            $user_data['register_date'] = $data['CreateTime'];
            $user_data['last_login_ip'] = '';
            $user_data['last_login_date'] = $data['CreateTime'];
            $user_data['login_count'] = 1;
            $user_data['status'] = 1;//$data['StateCode'];
            $user_data['income_type'] = 1;
            $user_data['verified'] = 1;//$data['ValidateState'];
            $user_data['update_time'] = $data['UpdateTime'];
            $user_data['create_time'] = $data['CreateTime'];
            $user_data['card_number'] = $data['CardNumber'];
            $user_data['tier_id'] = $data['TierId'];
            $user_data['tier_name'] = $data['TierName'];
            $user_data['tier_code'] = $data['TierCode'];
            $user_data['tier_disCount'] = $data['TierDisCount'];
            $user_data['current_point'] = $data['CurrentPoint'];
            $user_data['state_code'] = $data['StateCode'];
            $user_data['validate_state'] = $data['ValidateState'];
            $user_data['storage_money'] = $data['StorageMoney'];
            $user_data['storage_password'] = $data['StoragePassword'];
            $user_data['storage_state'] = $data['StorageState'];
            $where = array('member_id'=>$user_data['member_id']);
            $user_info = M('user_member_base_info')->where($where)->find();
            M('user_member_base_info')->startTrans();
            if($user_info){
                $save_bool = M('user_member_base_info')->where($where)->data($user_data)->save();
            }else{
                $save_bool = M('user_member_base_info')->data($user_data)->add();
            }
            if($save_bool){
                
                M('user_member_base_info')->commit();
                $this->customAjaxReturn (200, true, '更新成功');
            }
            else {
                M('user_member_base_info')->rollback();
                $this->customAjaxReturn (500, FALSE, '更新失败');
            }
        }
        $this->customAjaxReturn (510,FALSE,'请用POST方法调用');
    }
    
    public function cancel_member_wealth(){
        if(IS_POST){
            $token = I('token','','trim,htmlspecialchars');
            $base_data =  file_get_contents('php://input');
            if(!$base_data) $this->customAjaxReturn (501, FALSE, '没有任何数据');
            if(!$this->check_token($token, $base_data)) $this->customAjaxReturn (501,FALSE,'token验证失败');
            $data = json_decode($base_data, true);
            if(!$data['type']) $this->customAjaxReturn (502,FALSE,'类型不能为空');
            if(!$data['cancel_code']) $this->customAjaxReturn (502,FALSE,'取消编码不能为空');
            //商品码
            if($data['type'] == 2){
                $use_code_info = M('member_use_code')->where(array('use_code'=>$data['cancel_code']))->find();
                if($use_code_info['status'] == 20){
                    $code_data['remark'] = '订单被取消，但是已经被消费';
                }else{
                    $code_data['status'] = 0;
                    $code_data['remark'] = '订单取消，编码无效';
                }
                $code_data['update_time'] = date('Y-m-d H:i:s');
                $bool = M('member_use_code')->where(array('use_code'=>$data['cancel_code']))->data($code_data)->save();
                if($bool === FALSE) $this->customAjaxReturn (500, FALSE, '更新失败');
                else $this->customAjaxReturn (200, true, '更新成功');
            }else{
                $coupon_user_info = M('coupon_user_info')->where(array('coupon_code'=>$data['cancel_code']))->find();
                $bool = true;
                if(!$coupon_user_info['coupon_status'] == 20){
                    $code_data['coupon_status'] = 0;
                    $code_data['update_time'] = date('Y-m-d H:i:s');
                    $bool = M('coupon_user_info')->where(array('coupon_code'=>$data['cancel_code']))->data($code_data)->save();
                }else{
                }
                if($bool === FALSE) $this->customAjaxReturn (500, FALSE, '更新失败');
                else $this->customAjaxReturn (200, true, '更新成功');
            }
        }
        $this->customAjaxReturn (510,FALSE,'请用POST方法调用');
    }
    
    //获取使用码
    private function get_user_code(){
        return (new \Org\Util\ThinkString())->randString(4, 2);
    }
    
}