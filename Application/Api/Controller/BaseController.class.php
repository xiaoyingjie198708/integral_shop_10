<?php


namespace Api\Controller;

class BaseController extends \Think\Controller{
    
    private $key = 'woshitiancai';
    //验证token
    protected function check_token($token,$data){
        if($token == md5($data.'|'.$this->key)) return true;
        return true;
    }
    
    //自定义返回
    protected function customAjaxReturn($code,$bool,$message){
        $this->ajaxReturn(array('code'=>$code,'success'=>$bool,'message'=>$message));
    }
    
    protected function post_crm_data($param,$data){
        $params = array(
            'sync_order_info'=>'OrderSync/update_order_info',
            'sync_order_goods'=>'OrderSync/update_order_goods',
            'sync_order_payment'=>'OrderSync/update_order_payment',
            'sync_order_pay_discount'=>'OrderSync/update_order_pay_discount',
            'sync_order_goods_discount'=>'OrderSync/update_order_goods_discount',
            'sync_goods_info'=>'Goods/update_goods',
            'sync_coupon_info'=>'Goods/update_coupon_info',
            'sync_coupon_user'=>'Member/update_coupon_user',
            'sync_crm_reverse_order'=>'Member/update_crm_reverse_order',
            'sync_reverse_order_goods'=>'Member/update_reverse_order_goods',
            'sync_crm_member_use_code'=>'Member/update_crm_member_use_code',
            'sync_reverse_money'=>'Member/update_reverse_money'
        );
        $curl = new \Org\My\Curl();
        $header = array('Content-Type:text/json; charset=utf-8');
        $url = C('CRM_API_URL').$params[$param];
        $rs = $curl->post($url, json_encode($data), $header);
        return json_decode($rs,true);
    }
}
