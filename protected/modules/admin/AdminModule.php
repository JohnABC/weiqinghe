<?php

class AdminModule extends WModule {
    public function init() {
        $this->setImport(array(
            'admin.models.*',
            'admin.components.*',
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
