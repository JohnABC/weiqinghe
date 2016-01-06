<?php
class Curl {
	const RC_SUC = 0;
	const RC_FAILED = 1;
	
	public $handler = Null;
	public $header = array();//只有Set-Cookie和Cookie的值是数组'Cookie' => array('name' => 'username', 'value' => 'xxx')
	
	public $isBuildInQuery = True;
	public $autoSetCookie = True;
	
	public function __construct($headerConfig = array(), $curlConfig = array()) {
		$this->handler = curl_init();
		
		$headerConfig = self::arrayMerge(self::getDefaultHeaderInit(), $headerConfig);
		$curlConfig = self::arrayMerge(self::getDefaultHandlerInit(), $curlConfig);
		
		$this->setHeader($headerConfig);
		$this->setOpt($curlConfig);
	}
	
	public function __call($funcName, $args) {
		$funcName = 'curl_' . strtolower($funcName);
		array_unshift($args, $this->handler);
		return call_user_func_array($funcName, $args);
	}
	
	public function __set($key, $value) {
		$key = strtoupper($key);
		$this->setOpt($key, $value);
	}
	
    public function setOpt($name, $value = Null) {
        if (is_array($name)) {
            curl_setopt_array($this->handler, $name);
        } else {
            curl_setopt($this->handler, $name, $value);
        }
        
        return $this;
    }
    
    //获取默认的请求头信息，便于维护，自己组装使用一个HTTP HEADER头
    public static function getDefaultHeaderInit() {
    	return array(
    			'Accept' => '*/*',
    			//'Accept-Encoding' => 'gzip, deflate',
    			'Accept-Language' => 'zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3',
    			'Cache-Control' => 'no-cache',
    			'Connection' => 'keep-alive',
    			'If-Modified-Since' => '0',
    			'User-Agent' => self::getUserAgent(),
    			'X-Requested-With' => 'XMLHttpRequest',
    			//'X-Forwarded-For' => $_SERVER['SERVER_ADDR'] //mt_rand(58, 61) . '.' . mt_rand(10, 200) . '.' . mt_rand(10, 200) . '.' . mt_rand(10, 200)
    	);
    }
    
    //获取浏览器User-Agent
    public static function getUserAgent($type = '') {
    	$config = array(
    			'Safari_MAC' => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50',
    			'IE7' => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)',
    			'IE8' => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)',
    			'IE9' => 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0;',
    			'IE10' => 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)',
    			'Firefox4.0.1' => 'Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1',
    			'Firefox5.0.1' => 'Mozilla/5.0 (Windows NT 6.1; rv:34.0) Gecko/20100101 Firefox/34.0',
    			'Opera11.11_MAC' => 'Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; en) Presto/2.8.131 Version/11.11',
    			'Opera11.11_WIN' => 'Opera/9.80 (Windows NT 6.1; U; en) Presto/2.8.131 Version/11.11'
    	);
    	$type = !empty($type) && isset($config[$type]) ? $type : array_rand($config);
    	return $config[$type];
    }
    
    //获取默认的curl配置信息
    public static function getDefaultHandlerInit() {
    	return array(
    			CURLINFO_HEADER_OUT => True,
    			CURLOPT_SSL_VERIFYPEER => False,
    			CURLOPT_HEADER => 1,
    			CURLOPT_RETURNTRANSFER => 1,
    			CURLOPT_TIMEOUT => 3
    	);
    }
	
	//配置请求的header头信息
	public function setHeader($headerConfig, $isMerge = True) {
		if ($isMerge) {
			$this->header = self::arrayMerge($this->header, $headerConfig);
		} else {
			foreach ($headerConfig as $k => $v) {
				$this->header[$k] = $v;
			}
		}
		
		return $this;
	}
	
	//把响应头中的set-cookie字符串(aaa=xxx;path=xxx;) 每个set-cookie需要转一次
	public static function headerSetCookieStr2Arr($header) {
		$cookie = explode(';', $header);
		$kv = explode('=', trim($cookie[0]));
		return array(
			'name' => trim($kv[0]),
			'value' => trim($kv[1])
		);
	}
	
	//把请求头中的cookie字符串(aaaa=xxxx;bbbb=yyyy;...)转为通用header中的cookie数组
	public static function headerCookieStr2Arr($header) {
		$rtn = array();
		
		$cookies = explode(';', $header);
		foreach ($cookies as $cookie) {
			$tmp = explode('=', $cookie);
			$rtn[trim($tmp[0])] = array(
				'name' => trim($tmp[0]),
				'value' => trim($tmp[1])
			);
		}
		
		return $rtn;
	}
	
	//把通用header中的cookie数组转为请求头中的cookie字符串(aaaa=xxxx;bbbb=yyyy;...)
	public static function headerCookieArr2Str($cookies) {
		$rtn = array();
		foreach ($cookies as $cookie) {
			$rtn[] = "{$cookie['name']}={$cookie['value']}";
		}
		return implode(';', $rtn);
	}
	
	//把请求或相应的header头转换为数组(本curl类的header头通用数组), 其中header头的Set-Cookie可能重复多次
	public function headerStr2Arr($content) {
		$rtn = array();
		$headers = explode("\r\n", $content);
		foreach ($headers as $header) {
			$header = explode(':', $header, 2);
			if (count($header) < 2) {
				continue;
			}
	
			if ($header[0] == 'Set-Cookie') {
				$tmp = self::headerSetCookieStr2Arr($header[1]);
				$rtn[$header[0]][$tmp['name']] = $tmp;
			} elseif ($header[0] == 'Cookie') {
				$rtn[$header[0]] = self::headerCookieStr2Arr($header[1]);
			} else {
				$rtn[trim($header[0])] = trim($header[1]);
			}
		}
		return $rtn;
	}
	
	//获取curl_setopt设置header时的值
	public function getCurlHeader() {
		$rtn = array();
		foreach ($this->header as $k => $v) {
			if ($k == 'Cookie') {
				$v = self::headerCookieArr2Str($v);
			}
			$rtn[] = $k . ':' . $v;
		}
		return $rtn;
	}
	
	//设置是否自动更新cookie
	public function setAutoSetCookie($status) {
		$this->autoSetCookie = $status;
	}
	
	//设置是否使用内置http_build_query格式化postData
	public function setIsBuildInQuery($status) {
	    $this->isBuildInQuery = $status;
	}
	
	//发送HTTP请求及处理
	private function _request($urlType, $urlData = array()) {
		$rtn = array('rc' => self::RC_SUC, 'info' => array(), 'qheader' => array(), 'pheader' => array(), 'data' => '', 'urlType' => $urlType);
		
		if (is_array($urlType)) {
			$urlConfig = $urlType;
		} elseif (ctype_alnum($urlType)) {
			//现阶段不支持URL配置
			//$urlConfig = self::getUrlConfig($urlType);
		} else {
			$urlConfig = array('reqUrl' => $urlType);
		}

		$urlConfig['reqUrl'] = preg_replace('/(%[2-5])/', '%$1', $urlConfig['reqUrl']);
		
		$urlConfig = self::arrayMerge(array('reqLogUrl' => False, 'reqLogPData' => False, 'reqLogQData' => False), $urlConfig);
		
		array_unshift($urlData, $urlConfig['reqUrl']);
		$url = call_user_func_array('sprintf', $urlData);
		$this->setOpt(array(
			CURLOPT_URL => $url,
			CURLOPT_HTTPHEADER => $this->getCurlHeader()
		));

		$content = curl_exec($this->handler);
		$rtn['info'] = curl_getinfo($this->handler);
		if (empty($rtn['info']['request_header'])) {
			$rtn['info']['request_header'] = '';
		}
		$rtn['qheader'] = $this->headerStr2Arr($rtn['info']['request_header']);

		$urlConfig['reqLogUrl'] && YiiLog(self::getLog(array('请求地址:%s', $url)));
		$urlConfig['reqLogUrl'] && !empty($rtn['qheader']['Cookie']) && YiiLog(self::getLog(array('请求Cookie:%s', $rtn['qheader']['Cookie'])));
		
		if ($rtn['info']['http_code'] != '200') {
			$rtn['rc'] = $rtn['info']['http_code'];
			$rtn['info']['error_no'] = curl_errno($this->handler);
			$rtn['info']['error_msg'] = curl_error($this->handler);
			return $rtn;
		}
		
		$content = explode("\r\n\r\n", $content, 2);
		if (count($content) < 2) {
			$rtn['data'] = $content[0];
		} else {
			//在uuwise中的上传验证码步骤 返回两个头 其中第一个为 HTTP/1.1 100 Continue
			$tmp = explode("\r\n", $content[1]);
			if (preg_match('/\s*?HTTP\/1.[01]\s+?\d{3}\s+?\w+?\s*?/i', $tmp[0])) {
				$content = explode("\r\n\r\n", $content[1], 2);
			}
			$rtn['pheader'] = $this->headerStr2Arr($content[0]);
			$rtn['data'] = count($content) < 2 ? $content[0] : $content[1];
			
			if ($this->autoSetCookie && !empty($rtn['pheader']['Set-Cookie'])) {
				$this->setHeader(array('Cookie' => $rtn['pheader']['Set-Cookie']));
			}
		}
		
		$urlConfig['reqLogPData'] && YiiLog(self::getLog(array('返回数据:%s', $rtn['data'])));
		$urlConfig['reqLogUrl'] && !empty($rtn['pheader']['Set-Cookie']) && YiiLog(self::getLog(array('响应Cookie:%s', $rtn['pheader']['Set-Cookie'])));
		
		return $rtn;
	}
	
	public static function buildQuery($data) {
	    $rtn = array();
	    foreach ($data as $k => $v) {
	        $rtn[] = "{$k}={$v}";
	    }
	    return implode('&', $rtn);
	}
	
	public function get($urlType, $urlData = array()) {
		$this->setOpt(CURLOPT_POST, 0);
		return $this->_request($urlType, $urlData);
	}
	
	public function post($urlType, $postData = array(), $urlData = array()) {
		$ifHaveFile = False;
		foreach ($postData as $k => $v) {
			if (is_string($v) && isset($v{0}) && $v{0} == '@' && file_exists(substr($v, 1))) {
				$ifHaveFile = True;
				break;
			}
		}
		$this->setOpt(array(
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $ifHaveFile ? $postData : ($this->isBuildInQuery ? http_build_query($postData) : self::buildQuery($postData))
		));
		return $this->_request($urlType, $urlData);
	}
	
	public function getC($urlType, $urlData = array()) {
		return $this->get($urlType, $urlData);
	}
	
	public function getJ($urlType, $urlData = array()) {
		$rtn = $this->get($urlType, $urlData);
		if ($rtn['rc'] == self::RC_SUC) {
			$rtn['data'] = json_decode($rtn['data'], True);
		}
		return $rtn;
	}
	
	public function postC($urlType, $postData = array(), $urlData = array()) {
		return $this->post($urlType, $postData, $urlData);
	}
	
	public function postJ($urlType, $postData = array(), $urlData = array()) {
		$rtn = $this->post($urlType, $postData, $urlData);
		if ($rtn['rc'] == self::RC_SUC) {
			$rtn['data'] = json_decode($rtn['data'], True);
		}
		return $rtn;
	}
	
	public static function getLog($msg) {
		if (is_array($msg)) {
			if (isset($msg[0]) && is_string($msg[0]) && strpos($msg[0], '%') === False) {
				$msg = json_encode($msg);
			} else {
				$msg = array_map(function($v) {
					if (is_object($v)) {
						return json_encode(get_object_vars($v));
					} elseif (is_array($v)) {
						return json_encode($v);
					} else {
						return $v;
					}
				}, $msg);
				$msg = call_user_func_array('sprintf', $msg);
			}
		}
		
		return $msg;
	}

	public static function arrayMerge($a, $b) {
		$rtn = $a;
		foreach ($b as $k => $v) {
			if (!isset($rtn[$k])) {
				$rtn[$k] = $v;
			} elseif (is_array($v)) {
				if (!is_array($rtn[$k])) {
					$rtn[$k] = array();
				}
				$rtn[$k] = self::arrayMerge($rtn[$k], $b[$k]);
			} else {
				$rtn[$k] = $v;
			}
		}
		return $rtn;
	}
}