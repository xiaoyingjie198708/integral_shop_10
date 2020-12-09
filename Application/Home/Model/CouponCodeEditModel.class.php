<?php


namespace Home\Model;

/**
 *优惠码编辑管理
 * @author xiao.yingjie
 */
class CouponCodeEditModel extends \Think\Model{
    
    protected $tableName = 'coupon_code_base_info_edit';
    protected $patchValidate = true;
    protected $_validate = array(
        array('coupon_category_type','require','请选择优惠码分类'),
        array('coupon_code_name','require','请输入优惠码名称'),
    );
}
