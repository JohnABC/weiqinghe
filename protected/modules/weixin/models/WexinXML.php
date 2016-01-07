<?php
class WeixinXML {
    public static $passivityMsgXMLFrame = <<<EOF
        <xml>
            <ToUserName><![CDATA[<{ToUserName}>]]></ToUserName>
            <FromUserName><![CDATA[<{FromUserName}>]]></FromUserName>
            <CreateTime><{CreateTime}></CreateTime>
            <MsgType><![CDATA[<{MsgType}>]]></MsgType>
            <<{{content}}>>
        </xml>
EOF;
    
    private static function _getPassivityMsgTextXML() {
    	return <<<EOF
            <Content><![CDATA[<{Content}>]]></Content>
EOF;
    }
    
    private static function _getPassivityMsgImageXML() {
        return <<<EOF
            <Image>
                <MediaId><![CDATA[<{MediaId}>]]></MediaId>
            </Image>
EOF;
    }
    
    private static function _getPassivityMsgVoiceXML() {
        return <<<EOF
            <Voice>
                <MediaId><![CDATA[<{MediaId}>]]></MediaId>
            </Voice>
EOF;
    }
    
    private static function _getPassivityMsgVideoXML() {
        return <<<EOF
            <Video>
                <MediaId><![CDATA[<{MediaId}>]]></MediaId>
                <Title><![CDATA[<{Title}>]]></Title>
                <Description><![CDATA[<{Description}>]]></Description>
            </Video> 
EOF;
    }
    
    private static function _getPassivityMsgMusicXML() {
        return <<<EOF
            <Music>
                <Title><![CDATA[<{Title}>]]></Title>
                <Description><![CDATA[<{Description}>]]></Description>
                <MusicUrl><![CDATA[<{MusicUrl}>]]></MusicUrl>
                <HQMusicUrl><![CDATA[<{HQMusicUrl}>]]></HQMusicUrl>
                <ThumbMediaId><![CDATA[<{ThumbMediaId}>]]></ThumbMediaId>
            </Music>
EOF;
    }
    
    private static function _getPssivityMsgNewsXML() {
    	return <<<EOF
            <ArticleCount><{ArticleCount}></ArticleCount>
            <Articles>
    	        <<{{beginLoop}}>>
                <item>
                    <Title><![CDATA[<{Title}>]]></Title> 
                    <Description><![CDATA[<{Description}>]]></Description>
                    <PicUrl><![CDATA[<{PicUrl}>]]></PicUrl>
                    <Url><![CDATA[<{Url}>]]></Url>
                </item>
    	        <<{{endLoop}}>>
            </Articles>
EOF;
    }
    
    public static function getPassivityMsgXML($type, $params) {
        $methodName = '_getPssivityMsg' . ucfirst($type) . 'XML';
    	if (method_exists(__CLASS__, $methodName)) {
    		
    	}
    }
}