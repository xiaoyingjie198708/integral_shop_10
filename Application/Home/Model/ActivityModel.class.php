<?php


namespace Home\Model;

class ActivityModel extends BaseModel{
    
    protected $tableName = 'activity';
    protected $patchValidate = true;
    protected $_validate = array(
        array('activity_name','require','请输入活动名称'),
    );
}
