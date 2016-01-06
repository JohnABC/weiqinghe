<?php
class WController extends CController {
    public $layout = '//layouts/GPC';
    
    public function corAjax($data) {
        return $this->onAjax(W::corReturn($data));
    }
    
    public function errAjax($rc, $errMsg = '') {
        return $this->onAjax(W::errReturn($rc, $errMsg));
    }
    
    public function onAjax($response) {
        echo json_encode($response);
        Yii::app()->end();
    }
    
    public function error($msg, $url = '', $timeout = 3) {
        $this->render('index/error', array('msg' => $msg, 'url' => $url, 'timeout' => $timeout));
        Yii::app()->end();
    }
}