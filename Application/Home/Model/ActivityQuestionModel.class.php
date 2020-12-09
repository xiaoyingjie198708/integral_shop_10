<?php


namespace Home\Model;

class ActivityQuestionModel extends BaseModel{
    protected $tableName = 'activity_question';
    protected $patchValidate = true;
    protected $_validate = array(
        array('title','require','请输入问题标题'),
    );
}
