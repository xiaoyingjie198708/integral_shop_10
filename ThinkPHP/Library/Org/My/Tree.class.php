<?php
namespace Org\My;
class Tree {
    /**
    +----------------------------------------------------------
    * 无限级分类 start
    +----------------------------------------------------------
    */


    //获取树形数组
    public static function getTree($items,$id='id',$pid='p_id',$son = 'son'){
        $tree = array(); //格式化的树
        $tmpMap = array(); //临时扁平数据
        foreach ($items as $item) $tmpMap[$item[$id]] = $item;
        foreach ($items as $item) {
            if (isset($tmpMap[$item[$pid]]) && $item[$id] != $item[$pid]) {
                if (!isset($tmpMap[$item[$pid]][$son]))
                $tmpMap[$item[$pid]][$son] = array();
                $tmpMap[$item[$pid]][$son][] = &$tmpMap[$item[$id]];
            } else {
                $tree[] = &$tmpMap[$item[$id]];
            }
        }
        return $tree;
    }
    //保持原数组 只做排序
    public static function _getTree($data,$id=0,$callstack=0,$name='p_id',$result=array()){
        if($callstack===0) $result=array();
        $startlevel = self::_getchildren($data,$id,$name);
        for($i=0;$i<count($startlevel);$i++){
            $node = self::_gettypeitem($data,$startlevel[$i]["id"],$name);
            $result[] = $startlevel[$i];
            if($node===1){
                $result=self::_getTree($data,$startlevel[$i]["id"],$callstack+1,$name,$result);
            }
        }
        return $result;
    }


    private function _getchildren($data,$id,$name){
        $counter=0;
        for($i=0;$i<count($data);$i++){
            if($data[$i][$name] == "$id"){
                $children[$counter]=$data[$i];
                $counter ++;
            }
        }
        return $children;
    }//取得类型节点

    private function _gettypeitem($data,$id,$name){
        if(self::_getchildren($data,$id,$name)){
            return 1;
        }else{
            return 0;
        }
    }//取得目录树
    /**
    +----------------------------------------------------------
    * 无限级分类 end
    +----------------------------------------------------------
    */
    //保持原数组 只做排序
    public static function _getCustomTree($data,$id=0,$callstack=0,$curr_id='id',$name='p_id',$result=array()){
        if($callstack===0) $result=array();
        $startlevel = self::_getchildren($data,$id,$name);
        for($i=0;$i<count($startlevel);$i++){
            $node = self::_gettypeitem($data,$startlevel[$i][$curr_id],$name);
            $result[] = $startlevel[$i];
            if($node===1){
                $result=self::_getCustomTree($data,$startlevel[$i][$curr_id],$callstack+1,$curr_id,$name,$result);
            }
        }
        return $result;
    }
}


?>