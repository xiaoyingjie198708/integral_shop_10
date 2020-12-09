<?php


namespace Home\Model;

/**
 * 商品规格管理
 * @author xiao.yingjie
 */
class GoodsSpecificationModel extends \Think\Model{
    
    protected $tableName = 'product_specification';
    protected $patchValidate = true;
    protected $_validate = array(
        array('specification_name','require','请输入商品规格名称'),
        array('type_id','require','请选择商品类型'),
    );
}
