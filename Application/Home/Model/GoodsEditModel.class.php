<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Model;

/**
 * 商品编辑模型
 *
 * @author xiao.yingjie
 */
class GoodsEditModel extends \Think\Model{
    protected $tableName = 'goods_base_info_edit';
    protected $patchValidate = true;
    protected $_validate = array(
        array('goods_category_id','require','请选择商品分类'),
        array('goods_name','require','请输入商品名称'),
    );
}
