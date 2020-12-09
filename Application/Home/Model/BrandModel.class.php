<?php

namespace Home\Model;

/**
 *品牌模型
 * @author xiao.yingjie
 */
class BrandModel extends \Think\Model{
    protected $tableName = 'goods_brand';
    protected $patchValidate = true;
    protected $_validate = array(
        array('brand_name','require','请输入品牌名称'),
    );
}
