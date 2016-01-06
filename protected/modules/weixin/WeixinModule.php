<?php

class WeixinModule extends WModule {
    public function init() {
        $this->setImport(array(
            'weixin.models.*',
            'weixin.components.*',
        ));
    }

    public function beforeControllerAction($controller, $action) {
        if(parent::beforeControllerAction($controller, $action)) {
            return True;
        } else {
            return False;
        }
    }
}
