<?php
class IndexController extends WController {
    public function actionIndex() {
        $signature = Yii::app()->request->getQuery('signature');
        $timestamp = Yii::app()->request->getQuery('timestamp');
        $nonce = Yii::app()->request->getQuery('nonce');
        $echostr = Yii::app()->request->getQuery('echostr'); //接口验证时使用
        
        if (!Weixin::checkSignature($timestamp, $nonce, $signature)) {
            Yii::app()->end();
        } elseif ($echostr) {
        	Yii::app()->end($echostr);
        }
        
        //解析传入的XML
    }
}