<?php


namespace Api\Controller;

/**
 *商品预操作
 * @author xiao.yingjie
 */
class ExpectupGoodsController extends BaseController{
    
    //商品预上架
    public function goods_up(){
        $where = array();
        $where['pre_up_time'] = array('lt',  date('Y-m-d H:i:s'));
        $where['goods_status'] = 35;
        M('goods_base_info')->where(array($where))->data(array('goods_status'=>30,'update_time'=>  date('Y-m-d H:i:s')))->save();
    }
    
    //预约下架
    public function goods_down(){
        $where = array();
        $where['expect_down_time'] = array('between',array('2017-10-14 00:00:00',date('Y-m-d H:i:s')));
        $where['goods_status'] = 30;
       M('goods_base_info')->where($where)->data(array('goods_status'=>31,'update_time'=>  date('Y-m-d H:i:s')))->save();
    }
}
