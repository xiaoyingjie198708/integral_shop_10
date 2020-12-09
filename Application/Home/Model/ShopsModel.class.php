<?php

namespace Home\Model;

class ShopsModel extends \Think\Model{
    protected $tableName = 'shops_base_info';
    protected $patchValidate = true;
    protected $_validate = array(
        array('shops_name','require','请输入商家名称'),
        array('shop_address','require','请输入商家地址'),
        array('shop_tel','require','请输入商家电话'),
        array('office_hours','require','请输入营业时间')
    );
}
