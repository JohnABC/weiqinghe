<?php
class WeixinRequest {
    public static $urlConfig = array(
        'getAccessToken' => array(
            'reqUrl' => 'token?grant_type=client_credential&appid=%s&secret=%s'
            //次数限制等
        ),
        'getWeixinServerIPList' => array(
            'reqUrl' => 'getcallbackip?access_token=%s'
        ),
        'menuGet' => array(
            'reqUrl' => 'menu/get?access_token=%s'
        ),
        'menuCreate' => array(
            'reqUrl' => 'menu/create?access_token=%s'
        ),
        'menuDelete' => array(
            'reqUrl' => 'menu/delete?access_token=%s'
        ),
        
    );
}