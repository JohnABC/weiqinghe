<?php
class Weixin {
    public static function getXMLData() {
        $postData = file_get_contents('php://input');
        if (!empty($postData) || !($postData = simplexml_load_string($postData, 'SimpleXMLElement', LIBXML_NOCDATA))) {
            return W::errReturn(Rc::RC_WX_XML_ERROR);
        }
        
        return W::corReturn(json_decode(json_encode($postData), True));
    }
    
    public static function getSignature($timestamp, $nonce, $signature) {
        if (!$signature || !$timestamp || !$nonce) {
            return W::errReturn(Rc::RC_VAR_ERROR);
        }
    
        $tmp = array(WeixinConfig::WX_TOKEN, $timestamp, $nonce);
        sort($tmp, SORT_STRING);
    
        return sha1(implode($tmp));
    }
    
    public static function checkSignature($timestamp, $nonce, $signature) {
        return self::getSignature($timestamp, $nonce, $signature) == $signature;
    }
}