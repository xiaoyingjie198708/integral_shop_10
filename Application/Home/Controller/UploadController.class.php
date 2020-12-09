<?php
namespace Home\Controller;
class UploadController extends CommonController {

    public function index() {
        set_time_limit(0);
        $params = $_REQUEST;
        $type = $params['type'] ? $params['type'] : 'other';
        $upload = new \Think\Upload();// 实例化上传类
        $upload->rootPath = C('UPLOAD_ROOT_PATH');
        $upload->maxSize  = $this->getUploadSize($type); // 设置附件上传大小 默认2M
        $upload->exts     = $this->getUploadExt($type); // 设置附件上传类型
        $upload->savePath = $this->getUploadPath($type); // 设置附件上传目录
        $info = $upload->upload();
        if(!$info) $this->ajaxReturnForUpload(0,$upload->getError(),0);// 上传错误提示错误信息
        //上传成功
        if($params['size']) $this->checkImgSize($info,$params['size']);
        $config = C('upload_sync_config');
        foreach ($config as $k=>$v) {
            $sftp = new \Org\My\Sftp($v);
            foreach ($info as $key=>$val) { //同步远程服务器
                $sftp->send(realpath($upload->rootPath.$val['savepath'].$val['savename']), $v['upload_remote_url'].$val['savepath'].$val['savename']);
            }
        }
        $return = count($info) == 1 ? reset($info) : $info;
        $this->ajaxReturnForUpload($return,0,1);
    }
    //获取图片信息
    public function img(){
        $size = I('size','');
        $name = I('name');
        $name = base64_decode(basename($name,'.jpeg'));
        $name = C('UPLOAD_ROOT_PATH').$name;
        if(!is_file($name)) exit(send_http_status(404));
        if($size == '0_0') redirect(C('BASE_URL').trim($name,'.'));
        $size = explode('_',$size);
        if(count($size) > 1){
           $size[0] = intval($size[0]);
           $size[1] = intval($size[1]);
           $new_url = $this->thumb($name,$size);
        }else{
            $new_url = $this->thumb_img($name,$size[0]);
        }

        redirect(C('BASE_URL').trim($new_url,'.'));
    }
    /*-----------------------------------------------------------------------------------------------------*/
    private function ajaxReturnForUpload($data,$info,$status) {
        $arr = array(
            'data'=>$data,
            'info'=>$info,
            'status'=>$status
        );
        exit(json_encode($arr));
    }
    //获取上传限制文件大小
    private function getUploadSize($type='other') {
        return C('FILE_TYPE.'.$type)['size'] ? C('FILE_TYPE.'.$type)['size'] : C('UPLOAD_DEFAULT_SIZE');
    }
    //获取上传文件后缀限制
    private function getUploadExt($type='other') {
        return C('FILE_TYPE.'.$type)['ext'] ? C('FILE_TYPE.'.$type)['ext'] : C('UPLOAD_DEFAULT_EXT');
    }
    //获取保存文件路径
    private function getUploadPath($type='other') {
        if(!C('FILE_TYPE.'.$type)) $this->ajaxReturn(0,'type设置错误',0);
        $path = $type.'/';
        return $path;
    }
    //图片尺寸限制
    private function checkImgSize($info,$size) {
        $size = explode('_',$size);
        $width = intval($size[0]);
        $height = intval($size[1]);
        for($i=0;$i<count($info);$i++) {
            $img_size = getimagesize(C('UPLOAD_ROOT_PATH').$info[$i]['savepath'].$info[$i]['savename']);
            $img_width = $img_size[0];
            $img_height = $img_size[1];
            if($width != $img_width && $height != $img_height) $this->ajaxReturnForUpload(0,'请上传'.$width.'*'.$height.'像素的图片',0);
        }
        return true;
    }
    //图片缩略
    public function thumb($url,$size) {
        $path = dirname($url);
        $filename = basename($url);
        $new_url = $path.'/'.str_replace('.','_'.implode('_',$size).'.',$filename);
        if(is_file($new_url)) return $new_url;
		$Think_img = new \Think\Image();
        $Think_img->open($url)->thumb($size[0],$size[1], 1)->save($new_url);
        return $new_url;
    }

    /*
    * $img_path 被压缩的图片的路径
    * $thumb_size 压缩的宽
    * $save_path 压缩后图片的存储路径
    * $is_del 是否删除原文件，默认删除
    */
    public function thumb_img($img_path, $thumb_size){
        $path = dirname($img_path);
        $filename = basename($img_path);
        $image = new \Think\Image();
        $image->open($img_path);
        $width = $image->width(); // 返回图片的宽度
        $height = $image->height();
         //宽度大于或者等于高度
        if($width > $height){
            if($width > $thumb_size){
                $height = $height/$thumb_size; //取得图片的长宽比
                $thumb_w = ceil($width/$height);
            }
            $thumb_h = $thumb_size;
        }
        //高度大于或者等于宽度
        if($width <= $height){
            if($width > $thumb_size){
                $width = $width/$thumb_size; //取得图片的长宽比
                $thumb_h = ceil($height/$width);
            }
            $thumb_w = $thumb_size;
        }
        //如果文件路径不存在则创建
        $new_url = $path.'/'.str_replace('.','_'.$thumb_size.'.',$filename);
        if(is_file($new_url)) return $new_url;
        $image->thumb($thumb_w, $thumb_h)->save($new_url);
        return $new_url;
    }
}