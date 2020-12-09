<?php
namespace Api\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
//        $json_data = '{"category_code":"1002","materiel_code":"164905","sync_goods_id":"22","brand_name":"%E5%93%81%E7%89%8C%E5%90%8D%E7%A7%B0","goods_type":"1","goods_status":"11","goods_name":"%E6%B5%8B%E8%AF%95%E5%95%86%E5%93%81ulr%E8%BD%AC%E7%A0%81","goods_desc":"%26nbsp%3B%E5%95%86%E5%93%81%E7%AE%80%E4%BB%8B","goods_unit":"1","search_key_word":"%E5%85%B3%E9%94%AE%E8%AF%8D","product_default_pic":"http%3A%2F%2F192.168.9.129%3A8080%2Fprotect%2FP0201612%2FP020161228%2FP020161228579341125395.jpg","product_pic":"http%3A%2F%2F192.168.9.129%3A8080%2Fwebpic%2FW0201612%2FW020161228%2FW020161228579344429411.jpg%3Bhttp%3A%2F%2F192.168.9.129%3A8080%2Fwebpic%2FW0201612%2FW020161228%2FW020161228579344152690.jpg%3Bhttp%3A%2F%2F192.168.9.129%3A8080%2Fwebpic%2FW0201612%2FW020161228%2FW020161228579343572075.jpg%3Bhttp%3A%2F%2F192.168.9.129%3A8080%2Fwebpic%2FW0201612%2FW020161228%2FW020161228579342871746.jpg%3Bhttp%3A%2F%2F192.168.9.129%3A8080%2Fwebpic%2FW0201612%2FW020161228%2FW020161228579343883764.jpg%3B","goods_special":[{"name":"%E7%89%B9%E6%80%A7%E5%90%8D%E7%A7%B01","sort":"0","pc_content":"%26nbsp%3Bpc%E5%86%85%E5%AE%B9","mobile_content":"%26nbsp%3B%E6%89%8B%E6%9C%BA%E5%86%85%E5%AE%B9%3Cbr+%2F%3E"},{"name":"%E7%89%B9%E6%80%A7%E5%90%8D%E7%A7%B02","sort":"1","pc_content":"%26nbsp%3Bpc%E5%86%85%E5%AE%B9","mobile_content":"%26nbsp%3B%E6%89%8B%E6%9C%BA%E5%86%85%E5%AE%B9%3Cbr+%2F%3E"},{"name":"%E7%89%B9%E6%80%A7%E5%90%8D%E7%A7%B03","sort":"2","pc_content":"%26nbsp%3Bpc%E5%86%85%E5%AE%B9","mobile_content":"%26nbsp%3B%E6%89%8B%E6%9C%BA%E5%86%85%E5%AE%B9%3Cbr+%2F%3E"},{"name":"%E7%89%B9%E6%80%A7%E5%90%8D%E7%A7%B04","sort":"3","pc_content":"%26nbsp%3Bpc%E5%86%85%E5%AE%B9","mobile_content":"%26nbsp%3B%E6%89%8B%E6%9C%BA%E5%86%85%E5%AE%B9%3Cbr+%2F%3E"}],"update_time":"2016-12-28+17%3A57%3A27"}';
//        $list = json_decode($json_data, true);
//        foreach($list as $k=>$val){
//            if($k == 'goods_special'){
//                foreach($val as $j=>$val2){
//                    $temp_special = array(
//                        'name'=>urldecode($val2['name']),
//                        'sort'=>$val2['sort'],
//                        'pc_content'=>urldecode($val2['pc_content']),
//                        'mobile_content'=>urldecode($val2['mobile_content']),
//                    );
//                    $val[$j] = $temp_special;
//                }
//                $list[$k] = $val;
//            }else{
//                $list[$k] = urldecode($val);
//            }
//            
//        }
//        var_dump($list);exit;
//        M('goods_base_info_edit')->where(array('goods_code'=>39))->data(array('relation_goods'=>  json_encode(array('goods_code'=>'39'))))->save();
        var_dump(json_decode('{"sync_goods_id":1}',true));exit;
    }
    
    public function test_sync_goods(){
          $json_str = '[{\\\\\\\"goods_code\\\\\\\":\\\\\\\"1\\\\\\\",\\\\\\\"goods_name\\\\\\\":\\\\\\\"\\\\\\\\u897f\\\\\\\\u5f0f\\\\\\\\u725b\\\\\\\\u6392\\\\\\\"}]';
          $data = array('name'=>'会员入会权益包','code'=>'10001','type'=>1,'desc'=>'会员入会权益包');
          $json_data = json_encode($data);
          $url = 'http://integral_shop.twomi.cn/Api/Goods/sync_goods?token='.  md5($json_data.'|'.$this->key);
          $curl = new \Org\My\Curl();
          $header = array('Content-Type:text/json; charset=utf-8');
          $rs = $curl->post($url, $json_data,$header);
          var_dump($rs);exit;
    }
    
    public function test_sync_bind_member_wealth(){
          $data = array('member_id'=>'CH10001','bind_code'=>'RTFSHW','type'=>1,'number'=>'1','comment'=>'测试赠送');
          $json_data = json_encode($data);
          $url = 'http://integral_shop.twomi.cn/Api/Member/bind_member_wealth?token='.  md5($json_data.'|'.$this->key);
          $curl = new \Org\My\Curl();
          $header = array('Content-Type:text/json; charset=utf-8');
          $rs = $curl->post($url, $json_data,$header);
          var_dump($rs);exit;
    }
    
    public function test_update_goods(){
//          $data = array('category_code'=>base64_encode('1001'),'materiel_code'=>base64_encode('10001'),'sync_goods_id'=>base64_encode(null),'brand_name'=>  base64_encode('中国大剧院'),'goods_type'=>base64_encode(1),'goods_status'=>  base64_encode(1),'goods_name'=>  base64_encode('商品2'));
//          $data['goods_desc'] = base64_encode('');
//          $data['goods_unit'] = base64_encode(1);
//          $data['search_key_word'] = base64_encode('');
//          $data['product_default_pic'] = base64_encode('www');
//          $data['product_pic'] = base64_encode('www');
//          $data['goods_special'] = base64_encode('[{name:"特性1",sort:1,pc_content:"aaaaaa",mobile_content:"11111"},{name:"特性2",sort:2,pc_content:"bbbbbb",mobile_content:"222"}]');;
//          $data['update_time'] = base64_encode('2016-12-28 9:00:00');
//          $data = array('category_code'=>'1001','materiel_code'=>'10001','sync_goods_id'=>null,'brand_name'=>'中国大剧院','goods_type'=>1,'goods_status'=>1,'goods_name'=>'商品2');
//          $data['goods_desc'] = '';
//          $data['goods_unit'] = 1;
//          $data['search_key_word'] = '';
//          $data['product_default_pic'] = 'www.';
//          $data['product_pic'] = 'www.';
//          $data['goods_special'] = '[{name:"特性1",sort:1,pc_content:"aaaaaa",mobile_content:"11111"},{name:"特性2",sort:2,pc_content:"bbbbbb",mobile_content:"222"}]';
//          $data['update_time'] = '2016-12-28 9:00:00';
//          $data['sync_goods_id'] = 54;
//          $json_data = json_encode($data);
//          var_dump(json_decode($json_data,true));exit;
//          $json_data = '{"category_code":"1002","materiel_code":"164905","sync_goods_id":null,"brand_name":"%E5%93%81%E7%89%8C%E5%90%8D%E7%A7%B0","goods_type":"1","g
//oods_status":"11","goods_name":"%E6%B5%8B%E8%AF%95%E5%95%86%E5%93%81","goods_desc":"%26nbsp%3B%E5%95%86%E5%93%81%E7%AE%80%E4%BB%8B","goods_unit":"1","search_key_word":"%E5%85%B3%E9%94%AE%E8%AF%8D","product_default_pic":"http%3A%2F%2F192.168.9.129%3A8080%2Fprotect%2FP0201612%2FP020161228%2FP020161228579341125395.jpg","product_pic":"http%3A%2F%2F192.168.9.129%3A8080%2Fwebpic%2FW0201612%2FW020161228%2FW020161228579344429411.jpg%3Bhttp%3A%2F%2F192.168.9.129%3A8080%2Fwebpic%2FW0201612%2FW020161228%2FW020161228579344152690.jpg%3Bhttp%3A%2F%2F192.168.9.129%3A8080%2Fwebpic%2FW0201612%2FW020161228%2FW020161228579343572075.jpg%3Bhttp%3A%2F%2F192.168.9.129%3A8080%2Fwebpic%2FW0201612%2FW020161228%2FW020161228579342871746.jpg%3Bhttp%3A%2F%2F192.168.9.129%3A8080%2Fwebpic%2FW0201612%2FW020161228%2FW020161228579343883764.jpg%3B","goods_special":[{"name":"%E7%89%B9%E6%80%A7%E5%90%8D%E7%A7%B01","sort":"0","pc_content":"%26nbsp%3Bpc%E5%86%85%E5%AE%B9","mobile_content":"%26nbsp%3B%E6%89%8B%E6%9C%BA%E5%86%85%E5%AE%B9%3Cbr+%2F%3E"},{"name":"%E7%89%B9%E6%80%A7%E5%90%8D%E7%A7%B02","sort":"1","pc_content":"%26nbsp%3Bpc%E5%86%85%E5%AE%B9","mobile_content":"%26nbsp%3B%E6%89%8B%E6%9C%BA%E5%86%85%E5%AE%B9%3Cbr+%2F%3E"},{"name":"%E7%89%B9%E6%80%A7%E5%90%8D%E7%A7%B03","sort":"2","pc_content":"%26nbsp%3Bpc%E5%86%85%E5%AE%B9","mobile_content":"%26nbsp%3B%E6%89%8B%E6%9C%BA%E5%86%85%E5%AE%B9%3Cbr+%2F%3E"},{"name":"%E7%89%B9%E6%80%A7%E5%90%8D%E7%A7%B04","sort":"3","pc_content":"%26nbsp%3Bpc%E5%86%85%E5%AE%B9","mobile_content":"%26nbsp%3B%E6%89%8B%E6%9C%BA%E5%86%85%E5%AE%B9%3Cbr+%2F%3E"}],"update_time":"2016-12-28+16%3A05%3A34"}';
          $json_data = '{"category_code":"1006","materiel_code":"167701","sync_goods_id":null,"brand_name":"%E5%8E%9F%E5%88%9B%E5%89%A7%E7%9B%AE%E3%80%8A%E8%BF%90%E6%B2%B3%E8%B0%A3%E3%80%8B%E7%B3%BB%E5%88%97%E7%A4%BC%E5%93%81","goods_type":"1","goods_status":"11","goods_name":"%E8%BF%90%E6%B2%B3%E8%B0%A3%E6%89%8B%E6%8D%A7%E5%A3%B6","goods_desc":"%E5%8F%96%E6%9D%90%E4%BA%8E%E5%9B%BD%E5%AE%B6%E5%A4%A7%E5%89%A7%E9%99%A2%E5%88%B6%E4%BD%9C%E4%B8%AD%E5%9B%BD%E6%B0%91%E6%97%8F%E6%AD%8C%E5%89%A7%E3%80%8A%E8%BF%90%E6%B2%B3%E8%B0%A3%E3%80%8B%EF%BC%8C%E5%A3%B6%E4%BD%93%E6%B5%81%E6%B7%8C%E7%9A%84%E8%BF%90%E6%B2%B3%E6%B0%B4%E4%B8%8A%EF%BC%8C%E6%9C%B5%E6%9C%B5%E2%80%9C%E5%87%BA%E6%B7%A4%E6%B3%A5%E8%80%8C%E4%B8%8D%E6%9F%93%EF%BC%8C%E6%BF%AF%E6%B8%85%E6%B6%9F%E8%80%8C%E4%B8%8D%E5%A6%96%E2%80%9D%E7%9A%84%E5%BD%A9%E7%BB%98%E7%BA%A2%E8%8E%B2%EF%BC%8C%E9%AB%98%E9%9B%85%E8%84%B1%E4%BF%97%EF%BC%8C%E7%A7%80%E4%B8%BD%E9%9B%85%E8%87%B4%E3%80%82%E7%BA%A2%E8%8E%B2%E4%BB%A5%E8%AF%A5%E5%89%A7%E5%A5%B3%E4%B8%BB%E8%A7%92%E6%B0%B4%E7%BA%A2%E8%8E%B2%E4%B8%BA%E5%8E%9F%E5%9E%8B%E5%88%9B%E4%BD%9C%EF%BC%8C%E7%AA%81%E5%87%BA%E4%BA%86%E5%9C%A8%E5%89%A7%E4%B8%AD%E6%95%A2%E7%88%B1%E6%95%A2%E6%81%A8%E7%9A%84%E6%9B%B2%E8%89%BA%E5%A5%B3%E5%AD%90%E6%B0%B4%E7%BA%A2%E8%8E%B2%E6%B8%A9%E6%83%85%E6%9F%94%E5%92%8C%E7%9A%84%E4%B8%80%E9%9D%A2%E3%80%82%0A%E5%A3%B6%E4%BD%93%E4%B8%8A%E2%80%9C%E8%BF%90%E6%B2%B3%E8%B0%A3%E2%80%9D%E4%B8%89%E4%B8%AA%E6%AF%9B%E7%AC%94%E5%AD%97%E7%94%B1%E4%B9%A6%E6%B3%95%E5%90%8D%E5%AE%B6%E6%AC%A7%E9%98%B3%E4%B8%AD%E7%9F%B3%E9%A2%98%E5%86%99%EF%BC%8C%E4%B8%8E%E4%BD%9C%E5%93%81%E7%9B%B8%E5%BE%97%E7%9B%8A%E5%BD%B0%EF%BC%8C%E5%85%B7%E6%9C%89%E5%90%8D%E5%AE%B6%E9%A3%8E%E8%8C%83%E3%80%82%E5%B7%A5%E8%89%BA%E5%A4%A7%E5%B8%88%E4%BA%B2%E6%89%8B%E7%BB%98%E5%88%B6%EF%BC%8C%E7%94%BB%E5%B7%A5%E7%B2%BE%E6%B9%9B%EF%BC%8C%E6%83%9F%E5%A6%99%E6%83%9F%E8%82%96%E4%BC%A0%E7%A5%9E%E6%8F%8F%E7%BB%98%E2%80%9C%E4%B8%80%E6%9D%A1%E8%BF%90%E6%B2%B3%E5%8D%83%E5%B9%B4%E4%B9%85%EF%BC%8C%E6%B6%9B%E5%A3%B0%E6%B5%86%E5%BD%B1%E5%B2%81%E6%9C%88%E6%B5%81%EF%BC%8C%E5%87%A0%E5%A4%9A%E8%8B%B1%E9%9B%84%E4%BB%8E%E6%AD%A4%E5%87%BA%EF%BC%8C%E9%9D%92%E5%B1%B1%E5%A4%95%E7%85%A7%E6%B0%B4%E6%82%A0%E6%82%A0%E2%80%9D%E7%9A%84%E5%94%AF%E7%BE%8E%E6%84%8F%E5%A2%83%E3%80%82","goods_unit":"1","search_key_word":null,"product_default_pic":"http%3A%2F%2F192.168.9.129%3A8080%2Fprotect%2FP0201703%2FP020170303%2FP020170303608893879305.jpg","product_pic":"http%3A%2F%2F192.168.9.129%3A8080%2Fwebpic%2FW0201703%2FW020170303%2FW020170303609817915688.jpg%3Bhttp%3A%2F%2F192.168.9.129%3A8080%2Fwebpic%2FW0201703%2FW020170303%2FW020170303609817222987.jpg%3Bhttp%3A%2F%2F192.168.9.129%3A8080%2Fwebpic%2FW0201703%2FW020170303%2FW020170303609817701805.jpg%3B","goods_special":[{"name":"%E5%8E%9F%E5%88%9B%E5%89%A7%E7%9B%AE%E3%80%8A%E8%BF%90%E6%B2%B3%E8%B0%A3%E3%80%8B%E7%B3%BB%E5%88%97%E7%A4%BC%E5%93%81","sort":"4","pc_content":"%3Cdiv%3E%E6%AD%8C%E5%89%A7%E8%89%BA%E6%9C%AF%E4%B8%8E%E9%AB%98%E6%A1%A3%E9%AA%A8%E7%93%B7%E7%BB%93%E5%90%88%EF%BC%8C%E5%AE%8C%E7%BE%8E%E8%AF%A0%E9%87%8A%E4%B8%AD%E8%A5%BF%E6%96%87%E5%8C%96%E7%9A%84%E7%A2%B0%E6%92%9E%E4%B8%8E%E8%9E%8D%E5%90%88%E3%80%82%E5%8E%9F%E5%88%9B%E5%89%A7%E7%9B%AE%E3%80%8A%E8%BF%90%E6%B2%B3%E8%B0%A3%E3%80%8B%E7%B3%BB%E5%88%97%E7%A4%BC%E5%93%81%EF%BC%8C%E8%BF%90%E6%B2%B3%E8%B0%A3%E6%89%8B%E6%8D%A7%E5%A3%B6%EF%BC%88%E5%A5%97%EF%BC%89%E3%80%81%E8%BF%90%E6%B2%B3%E8%B0%A3%E6%8A%98%E6%89%87%EF%BC%8C%E5%94%AF%E7%BE%8E%E5%86%8D%E7%8E%B0%E6%B1%9F%E5%8D%97%E6%B0%94%E9%9F%B5%E3%80%82%3Cbr+%2F%3E%0A%3Cimg+src%3D%22http%3A%2F%2Fwww.chncpa.org%2Fhyjlb_366%2Fhysc%2Fsptx%2F201703%2FW020170303611019997465.jpg%22+onclick%3D%22window.open%28%27http%3A%2F%2Fwww.chncpa.org%2Fsubsite%2Fyhy2017%2F%27%29%22+style%3D%22cursor%3A+pointer%3B+border-width%3A+0px%3B%22+alt%3D%22%22+oldsrc%3D%22W020170303611019997465.jpg%22+%2F%3E%3C%2Fdiv%3E","mobile_content":"%26nbsp%3B"}],"update_time":"2017-03-03+18%3A20%3A12"}';
//          $url = 'http://integral_shop.twomi.cn/Api/Goods/update_goods?token='.  md5($json_data.'|'.$this->key);
          $url = 'http://jf_admin.chncpa.org/Api/Goods/update_goods?token='.  md5($json_data.'|'.$this->key);
          $curl = new \Org\My\Curl();
          $header = array('Content-Type:text/json; charset=utf-8');
          $rs = $curl->post($url, $json_data,$header);
          var_dump($rs);exit;
//          var_dump(json_decode($rs));exit;
//          var_dump(json_decode($rs,true));exit;
    }
    
     public function test_update_goods_sale(){
//          $data = array('sync_goods_id'=>'54');
//          $data['goods_desc'] = '';
//          $data['goods_unit'] = 1;
//          $data['search_key_word'] = '';
//          $data['product_default_pic'] = 'www.';
//          $data['product_pic'] = 'www.';
//          $data['goods_special'] = '[{name:"特性1",sort:1,pc_content:"aaaaaa",mobile_content:"11111"}]';
//          $data['update_time'] = '2016-12-28 9:00:00';
          $data['sync_goods_id'] = 52;
          $json_data = json_encode($data);
          $url = 'http://admin.chncpa.org/Api/Goods/update_goods_sale?token='.  md5($json_data.'|'.$this->key);
          $curl = new \Org\My\Curl();
          $header = array('Content-Type:text/json; charset=utf-8');
          $rs = $curl->post($url, $json_data,$header);
        var_dump($rs);exit;
    }
    
    public function test_update_member(){
        $json_data = '{"MemberId":"948fd034-340d-e711-93f5-000c294295e8","WebMemberId":"21484201","MemberName":"18635741135","MobilePhone":"18635741135","CardNumber":"","TierId":"a0f7eee2-ac5d-e511-93f2-000c294295e8","TierName":"普通会员","TierCode":"0100","TierDisCount":1.0000000000,"CurrentPoint":0.0000000000,"StateCode":100000000,"ValidateState":100000001,"CreateTime":"2017/3/20 14:12:50","UpdateTime":"2017/3/20 14:12:50","StorageMoney":0,"StoragePassword":"","StorageState":0}';
        $url = 'http://admin.chncpa.org/Api/member/update_member?token='.  md5($json_data.'|'.$this->key);
          $curl = new \Org\My\Curl();
          $header = array('Content-Type:text/json; charset=utf-8');
          $rs = $curl->post($url, $json_data,$header);
        var_dump($rs);exit;
    }
    
    public function test_sync_cancel_member_wealth(){
        var_dump(json_decode('{"code":502,"success":false,"message":"\u7c7b\u578b\u4e0d\u80fd\u4e3a\u7a7a"}'));exit;
         $data = array('member_id'=>'6314f744-3dce-e611-93f5-000c294295e8','cancel_code'=>'WERU','type'=>2);
          $json_data = json_encode($data);
          $url = 'http://192.168.9.154/Api/Member/cancel_member_wealth?token='.  md5($json_data.'|'.$this->key);
          $curl = new \Org\My\Curl();
          $header = array('Content-Type:text/json; charset=utf-8');
          $rs = $curl->post($url, $json_data,$header);
          var_dump($rs);exit;
    }
    
}