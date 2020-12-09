<?php


namespace Api\Controller;

/**
 *获取奔驰用车卡
 * @author xiao.yingjie
 */
class TestBenzSyncController extends BaseController{
    
    private $url = 'http://benz.wx.fractalist.com.cn/Theatre/AssignCard';
    
    private $appkey = 'fractalist_theatre_relation';
    
    public function sync(){
        $this->benz_code(1, 100);
    }
    
    private function benz_code($pageIndex=1, $page_size=10){
        $where = $this->get_where();
        $count = M('order_base_info as info,tm_order_goods_info as order_goods')->where('info.order_id=order_goods.order_id')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('order_goods_info as order_goods,tm_order_base_info as info')->where('order_goods.order_id=info.order_id')->field('order_goods.*,info.member_id')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('order_goods.create_time asc')->select();
        $goods_codes = getFieldsByKey($list, 'goods_code');
        $goods_types = M('goods_base_info')->where(array('goods_code'=>array('in',$goods_codes)))->getField('goods_code,goods_type',true);
        foreach($list as $k=>$order_goods){
            $order_type = $goods_types[$order_goods['goods_code']];
            //奔驰用车卡
            if(in_array($order_type, array(6,7))){
                $curl = new \Org\My\Curl();
                $params['timestamp'] = ''.time().'';
                $params['cardtype'] = $order_type - 6;//0-代金券，1-尊享卡
                $params['signature'] = $this->get_signature($params);
                $result_json = $curl->post($this->url,$params);
                $result = json_decode($result_json,true);
                //接口错误
                if(intval($result['ReturnCode']) != 200) {
                    $other_data = array(
                        'type'=>3,//奔驰
                        'other_id'=>$order_goods['order_goods_id'],
                        'status'=>1,
                        'send_content'=> serialize($params),
                        'create_time'=>date('Y-m-d H:i:s'),
                        'update_time'=>date('Y-m-d H:i:s'),
                    );
                    M('other_call_info')->data($other_data)->add();
                    \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',请求数据：'.  json_encode($params).'，获取用车卡失败,失败原因：'.$result_json."\n", 'benz_code-error.log',date('Y-m-d'));
                }else{
                    $member_account = M('user_member_base_info')->where(array('member_id'=>$order_goods['member_id']))->getField('member_account');
                    if($order_type == 6) $code_type = 1;
                    else $code_type = 2;
                    $other_data['use_code'] = $result['Data']['CardNumber'];
                    $other_data['code_pwd'] = $result['Data']['CardPwd'];
                    //有效时间
                    $other_data['valid_time'] = date('Y-m-d H:i:s',  strtotime($result['Data']['ValidEndTime']));
                    $other_data['code_type'] = $code_type;
                    $other_data['create_time'] = date('Y-m-d H:i:s');
                    $other_data['update_time'] = date('Y-m-d H:i:s');
                    $other_data['member_id'] = $order_goods['member_id'];
                    $other_data['shops_code'] = $order_goods['shops_code'];
                    $other_data['goods_code'] = $order_goods['goods_code'];
                    $other_data['order_id'] = $order_goods['order_id'];
                    $other_data['user_name'] = $member_account;
                    $other_data['order_goods_id'] = $order_goods['order_goods_id'];
                    M('other_use_code')->data($other_data)->add();
                    \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',请求数据：'.  json_encode($params).'，获取用车卡成功,返回数据：'.$result_json."\n", 'benz_code-success.log',date('Y-m-d'));
                }
            }
        }
    }
    
    private function get_where(){
        $where = array();
//        $where['info.shops_code'] = array('neq','');
//        $modifytime = \Org\My\MyLog::read_log('create_time', 'benz_code-info.log');
//        if(!$modifytime) $modifytime = '2017-05-04 00:00:00';
//        $where['order_goods.create_time'] = array('gt',$modifytime);
        $where['info.order_cur_id'] = '2018112200049299';
        return $where;
    }
    
    private function get_signature($params){
        $str = $this->appkey.$params['timestamp'].$params['cardtype'];
        return md5($str);
    }
}
