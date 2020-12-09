<?php


namespace Home\Model;

/**
 *优惠券模型
 * @author xiao.yingjie
 */
class CouponEditModel extends \Think\Model{
    
    protected $tableName = 'coupon_base_info_edit';
    protected $patchValidate = true;
    protected $_validate = array(
        array('coupon_category_type','require','请选择优惠券分类'),
        array('coupon_name','require','请输入优惠券名称'),
    );
}
