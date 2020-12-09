<?php
class Xml {
    public $xml = '<?xml version="1.0" encoding="UTF-8"?><tree id="0">';

    public function createXml($data) {
        foreach($data as $k=>$v) {
            $this->xml .= '<item text="'.$v['name'].($v['url'] ? '（'.$v['url'].'）' : '').'" checked="'.$v['checked'].'" '.($v['close'] ? '' : 'open="1"').' id="'.$v['id'].'">';
            if($v['son']) $this->xml .= self::createXml($v['son']);
            $this->xml .= '</item>';
        }
    }
}
?>