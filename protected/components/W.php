<?php
class W {
    public static $_G = array();
    
    const ENV_LOCAL = 'local';
    const ENV_TEST = 'test';
    const ENV_PRODUCT = 'product';
    
    const ENV_TEST_DOMAIN_PREFIX = 'new.';
    const ENV_MOBILE_DOMAIN_PREFIX = 'm.';
    const ENV_LOCAL_DOMAIN = 'wqh';
    const ENV_PRODUCT_DOMAIN = 'weiqinghe';
    const ENV_DOMAIN_SUFFIX = '.com';
    
    const PLATFORM_MOBILE = 'm';
    const PLATFORM_PC = 'p';
    
    const DEFAULT_HOST_HEADER = 'www';
    
    public static function isLocalEnv() {
        $tmp = self::ENV_LOCAL_DOMAIN . self::ENV_DOMAIN_SUFFIX;
        return preg_match('/' . str_replace('.', '\.', $tmp) . '$/', W_HOST);
    }
    
    public static function isTestEnv() {
        return preg_match('/^' . str_replace('.', '\.', self::ENV_TEST_DOMAIN_PREFIX) . '/', W_HOST);
    }
    
    public static function isProductEnv() {
        return !self::isLocalEnv() && !self::isTestEnv();
    }
    
    public static function isMobilePlatform() {
        $tmp = str_replace('.', '\.', self::ENV_MOBILE_DOMAIN_PREFIX);
        return preg_match("/(^|\.){$tmp}/", W_HOST);
    }
    
    public static function isPcPlatform() {
        return !self::isMobilePlatform();
    }
    
    public static function getEnvStr() {
        return self::isLocalEnv() ? self::ENV_LOCAL : (self::isTestEnv() ? self::ENV_TEST : self::ENV_PRODUCT);
    }
    
    public static function getPlatformStr() {
        return self::isMobilePlatform() ? self::PLATFORM_MOBILE : self::PLATFORM_PC;
    }
    
    public static function getHostHeader() {
        $host = W_HOST;
        if (self::isTestEnv()) {
            $host = substr($host, strlen(self::ENV_TEST_DOMAIN_PREFIX));
        }
        
        if (self::isMobilePlatform()) {
            $host = substr($host, strlen(self::ENV_MOBILE_DOMAIN_PREFIX));
        }
        
        $res = preg_match('/(.*?)\.?(' . self::ENV_LOCAL_DOMAIN . '|' . self::ENV_PRODUCT_DOMAIN . ')\.com/', $host, $match);
        if (!$res || !$match[1]) {
            $match = array(1 => self::DEFAULT_HOST_HEADER);
        }
        
        return $match[1];
    }
    
    public static function getConfig() {
        $env = self::getEnvStr();
        $platform = self::getPlatformStr();
        $hostHeader = self::getHostHeader();
        
        $configPath = W_ROOT_PATH . '/protected/config/';
        $configFile = implode('.', array($env, $hostHeader, $platform)) . '.php';
        if (file_exists($configPath . $configFile)) {
            return require($configPath . $configFile);
        }
        
        $config = require($configPath . 'base.php');
        foreach (array($env, $hostHeader, $platform) as $k => $file) {
            if (file_exists($configPath . $file . '.php') || ($k == 1 && file_exists($configPath . self::DEFAULT_HOST_HEADER . '.php') && ($file = self::DEFAULT_HOST_HEADER))) {
                $config = CMap::mergeArray($config, require($configPath . $file . '.php'));
            }
        }

        return $config;
    }
    
    public static $curls = array();
    public static function getCurl($key = 'Qmy', $headerConfig = array(), $curlConfig = array()) {
        if (!isset(self::$curls[$key])) {
            Yii::import('ext.Curl');
            self::$curls[$key] = new Curl($key, $headerConfig, $curlConfig);
        }
        
        return self::$curls[$key];
    }
    
    public static $cacheKManagers = array();
    public static function getCacheKeyManager($prefix, $m = CacheKManager::M_GLOBAL) {
        $k = $m . '_' . $prefix;
        if (!isset(self::$cacheKManagers[$k])) {
            self::$cacheKManagers[$k] = new CacheKeys($prefix, $m);
        }
        
        return self::$cacheKManagers[$k];
    }
    
    public static $return = array(
        'rc' => Rc::RC_SUCCESS,
        'errMsg' => ''
    );
    
    public static function corReturn($data = '') {
        $return = self::$return;
        $return['data'] = $data;
        
        return $return;
    }
    
    public static function errReturn($rc, $errMsg = '') {
        $return = self::$return;
        $return['rc'] = $rc;
        $return['errMsg'] = $errMsg;
        
        return $return;
    }
    
    public static function isCorrect($res) {
        return $res['rc'] == Rc::RC_SUCCESS;
    }
    
    public static function getArrayByKeys($keys, $params) {
        $rtn = array();
        foreach ($keys as $key => $v) {
            if ((is_array($params) && isset($params[$key])) || (is_object($params) && isset($params->$key))) {
                $value = is_array($params) ? $params[$key] : $params->$key;
                $rtn[$key] = is_array($v) ? self::getArrayByKeys($v, $value) : $value;
            } else {
                $rtn[$key] = Null;
            }
        }
        
        return $rtn;
    }
    
    public static function getArrayChangeKeys($params, $k2k) {
        $rtn = array();
        foreach ($k2k as $k => $v) {
            if (is_array($k2k)) {
                $rtn[$v] = isset($params[$k]) ? $params[$k] : Null;
            } else {
                $rtn[$v] = isset($params->$k) ? $params->$k : Null;
            }
            
        }
        return $rtn;
    }
    
    public static function staticEvaluateExpression($_expression_, $_data_=array()) {
        extract($_data_);
        return eval('return ' . $_expression_ . ';');
    }
    
    public static function checkParamCall($params, $k, $format) {
        return is_string($format) ? self::staticEvaluateExpression($format . '($params[$k])', array('params' => $params, 'k' => $k)) : call_user_func($format, $params[$k]);
    }
    
    public static function checkParam($params, $k, $format) {
        if ($isNot = !strncmp($format, '!', 1)) {
            $format = substr($format, 1);
        }
        
        if (!($isCanRef = in_array(substr($format, -5), array('isset', 'empty'))) && !isset($params[$k])) {
            return $isNot ^ False;
        }
        
        if ($isArr = substr($format, 0, 6) == 'array(') {
            $format = self::staticEvaluateExpression($format);
        }
        
        if (is_callable($format) || $isCanRef) {//isset, empty等不能用is_callable
            return $isNot ^ self::checkParamCall($params, $k, $format);
        } elseif (!strncmp($format, '/', 1) && !strncmp($format{strlen($format) - 1}, '/', 1)) {
            return $isNot ^ preg_match($format, $params[$k]);
        } elseif ($params[$k] !== $format) {
            return $isNot ^ False;
        }

        return $isNot ^ False;
    }
    
    public static function checkParams($params, $formats, $isRtnFormatKeys = True) {
        $rtn = $isRtnFormatKeys ? array() : $params;
        foreach ($formats as $k => $format) {
            $isOr = $isAssign = False;
            if (strstr($format, '&&')) {
                $formatArr = explode('&&', $format);
            } elseif (strstr($format, '||')) {
                $formatArr = explode('||', $format);
                $isOr = True;
            } else if (strstr($format, '--')) {
                $formatArr = explode('--', $format);
                $isAssign = True;
            } else {
                $formatArr = array($format);
            }
            
            $indexMax = count($formatArr) - 1;
            foreach ($formatArr as $index => $subFormat) {
                if (self::checkParam($params, $k, $subFormat)) {
                    if ($isAssign) {//是则赋值, 否则表示值正确
                        $rtn[$k] = $formatArr[$index + 1];
                        break;
                    } else {
                        $rtn[$k] = $params[$k];
                        if ($isOr) {
                            break;
                        }
                    }
                } else {
                    if ($isOr && $index < $indexMax) {
                        continue;
                    } elseif ($isAssign) {
                        $rtn[$k] = $params[$k];
                        break;
                    } elseif (!$isAssign) {
                        self::log('Qmy::checkParams Error', $k.'_'.$format.'__'.$params[$k]);
                        //echo $k, '<br />', $format, '<br />', $params[$k];exit;
                        return False;
                    }
                }
            }
        }
        
        return $rtn;
    }
    
    public static function mergeArrayWithoutInt() {
        $args = func_get_args();
        $res = array_shift($args);
        while (!empty($args)) {
            $next = array_shift($args);
            foreach($next as $k => $v) {
                if(is_array($v) && isset($res[$k]) && is_array($res[$k]))
                    $res[$k]=self::mergeArrayWithoutInt($res[$k],$v);
                else
                    $res[$k]=$v;
            }
        }
        return $res;
    }
    
    public static function log($message = '', $cat = 'backend', $request = False,  $level = CLogger::LEVEL_ERROR){
        if(!is_string($message)) $message = json_encode($message);
        $backtrace = debug_backtrace();
        $request = $request ? ' Request: ' . $_SERVER['REQUEST_URI'] . '|' . json_encode($_POST) . '|' . json_encode($_FILES) : '';
        $request .= "[{$backtrace[1]['file']}]-[{$backtrace[1]['line']}]";
        Yii::log($message . ' @' . QmyFunc::getClientIP() . $request, CLogger::LEVEL_ERROR, $cat, 0);
    }
}