<?php
class WFunc {
    private static function _getParam($key, $default = Null, $isGet = True) {
        $func = $isGet ? 'getQuery' : 'getPost';
        if (is_array($key)) {
            $rtn = array();
            foreach ($key as $k => $v) {
                if (is_int($k)) {
                    $rtn[$v] = Yii::app()->request->$func($v, $default);
                } else {
                    $rtn[$k] = Yii::app()->request->$func($k, $v);
                }
            }
        } else {
            $rtn = Yii::app()->request->$func($k, $default);
        }
        
        return $rtn;
    }
    
    public static function getQuery($key, $default = Null) {
        return self::_getParam($key, $default, True);
    }
    
    public static function getPost($key, $default = Null) {
        return self::_getParam($key, $default, False);
    }
    
    public static function arrayGetByKeys($params, $keys) {
        if (is_int(key($keys))) {
            $keys = array_fill_keys($keys, True);
        }
        
        $rtn = array();
        foreach ($keys as $key => $v) {
            if ((is_array($params) && isset($params[$key])) || (is_object($params) && isset($params->$key))) {
                $value = is_array($params) ? $params[$key] : $params->$key;
                $rtn[$key] = is_array($v) ? self::arrayGetByKeys($value, $v) : $value;
            } else {
                $rtn[$key] = Null;
            }
        }
    
        return $rtn;
    }
    
    public static function arrayGetField($lines, $field, $isUnique = False) {
        $rtn = array();
        foreach ($lines as $line) {
            if (is_object($line)) {
                $v = isset($line->$field) ? $line->$field : Null;
            } else {
                $v = isset($line[$field]) ? $line[$field] : Null;
            }
            if ($isUnique) {
                $rtn[$v] = 1;
            } else {
                $rtn[] = $v;
            }
        }
    
        return $isUnique ? array_keys($rtn) : $rtn;
    }
    
    public static function arrayAddFiled($lines, $field) {
        $rtn = array();
        foreach ($lines as $v) {
            $rtn[$v[$field]] = $v;
        }
        
        return $rtn;
    }
    
    public static function arrayChangeKeys($params, $k2k, $isFilter = True) {
        $rtn = array();
        foreach ($params as $k => $v) {
            if (is_array($params)) {
                $v = isset($params[$k]) ? $params[$k] : Null;
            } else {
                $v = isset($params->$k) ? $params->$k : Null;
            }
            
            if (isset($k2k[$k])) {
                $rtn[$k2k[$k]] = $v;
            } elseif (!$isFilter) {
                $rtn[$k] = $v;
            }
        }
        
        return $rtn;
    }
    
    public static function evaluateExpression($_expression_, $_data_=array()) {
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
    
    public static function isTime($str, $format = 'H:i:s') {
        return self::isDateTime($str, $format);
    }
    
    public static function isDate($str, $format = 'Y-m-d') {
        return self::isDateTime($str, $format);
    }
    
    public static function isDateHM($str, $format = 'Y-m-d H:i') {
        return self::isDateTime($str, $format);
    }
    
    public static function isDateTime($str, $format = 'Y-m-d H:i:s'){
        $timestamp = strtotime($str);
        return date($format, $timestamp) == $str;
    }
    
    public static  function getClientIP() {
        static $ip = Null;
        if ($ip) {
            return $ip;
        }
    
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if (False !== ($pos = array_search('unknown', $arr))) {
                unset($arr[$pos]);
            }
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    
        return False !== ip2long($ip) ? $ip : '0.0.0.0';
    }
    
    public static function unicodeDecode($data) {
        function replaceUnicodeEscapeSequence($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
        }
    
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replaceUnicodeEscapeSequence', $data);
    }
    
    public static function chunkSplit($str, $len, $end = '\n', $encode = 'UTF-8') {
        $rtn = array();
        $strLength = strlen($str);
        $i = 0;
        while ($i < $strLength) {
            $rtn[] = mb_substr($str, $i, $len, $encode);
            $i += $len;
        }
    
        return implode($end, $rtn);
    }
}