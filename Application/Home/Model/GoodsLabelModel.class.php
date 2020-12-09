<?php


namespace Home\Model;

/**
 *商品标签模型
 * @author xiao.yingjie
 */
class GoodsLabelModel extends \Think\Model{
    
    protected $tableName = 'goods_label';
    protected $patchValidate = true;
    protected $_validate = array(
        array('label_name','require','请输入商品标签名称'),
    );
}
