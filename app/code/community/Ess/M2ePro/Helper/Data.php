<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Data extends Mage_Core_Helper_Abstract
{
    const STATUS_ERROR      = 1;
    const STATUS_WARNING    = 2;
    const STATUS_SUCCESS    = 3;

    const INITIATOR_UNKNOWN   = 0;
    const INITIATOR_USER      = 1;
    const INITIATOR_EXTENSION = 2;
    const INITIATOR_DEVELOPER = 3;

    const CUSTOM_IDENTIFIER = 'm2epro_extension';

    //########################################

    public function __()
    {
        $args = func_get_args();
        return Mage::helper('M2ePro/Module_Translation')->translate($args);
    }

    //########################################

    /**
     * @param $modelName
     * @param array $params
     * @return Ess_M2ePro_Model_Abstract
     */
    public function getModel($modelName, $params = array())
    {
        return Mage::getModel('M2ePro/'.$modelName,$params);
    }

    public function getHelper($helperName = NULL)
    {
        is_string($helperName) && $helperName = '/'.$helperName;
        return Mage::helper('M2ePro'.(string)$helperName);
    }

    // ---------------------------------------

    /**
     * @param string $modelName
     * @param mixed $value
     * @param null|string $field
     * @return Ess_M2ePro_Model_Abstract
     */
    public function getObject($modelName, $value, $field = NULL)
    {
        return $this->getModel($modelName)->loadInstance($value, $field);
    }

    /**
     * @param string $modelName
     * @param mixed $value
     * @param null|string $field
     * @param array $tags
     * @return Ess_M2ePro_Model_Abstract
     */
    public function getCachedObject($modelName, $value, $field = NULL, array $tags = array())
    {
        if (Mage::helper('M2ePro/Module')->isDevelopmentEnvironment()) {
            return $this->getObject($modelName,$value,$field);
        }

        $cacheKey = strtoupper($modelName.'_data_'.$field.'_'.$value);
        $cacheData = Mage::helper('M2ePro/Data_Cache_Permanent')->getValue($cacheKey);

        if ($cacheData !== false) {
            return $cacheData;
        }

        $tags[] = $modelName;

        if (strpos($modelName,'_') !== false) {

            $allComponents = Mage::helper('M2ePro/Component')->getComponents();
            $modelNameComponent = substr($modelName,0,strpos($modelName,'_'));

            if (in_array(strtolower($modelNameComponent),array_map('strtolower',$allComponents))) {
                $modelNameOnlyModel = substr($modelName,strpos($modelName,'_')+1);
                $tags[] = $modelNameComponent;
                $tags[] = $modelNameOnlyModel;
            }
        }

        $tags = array_unique($tags);
        $tags = array_map('strtolower',$tags);

        $cacheData = $this->getObject($modelName,$value,$field);

        if (!empty($cacheData)) {
            Mage::helper('M2ePro/Data_Cache_Permanent')->setValue($cacheKey,$cacheData,$tags,60*60*24);
        }

        return $cacheData;
    }

    //########################################

    public function getCurrentGmtDate($returnTimestamp = false, $format = NULL)
    {
        if ($returnTimestamp) {
            return (int)Mage::getModel('core/date')->gmtTimestamp();
        }
        return Mage::getModel('core/date')->gmtDate($format);
    }

    public function getCurrentTimezoneDate($returnTimestamp = false, $format = NULL)
    {
        if ($returnTimestamp) {
            return (int)Mage::getModel('core/date')->timestamp();
        }
        return Mage::getModel('core/date')->date($format);
    }

    // ---------------------------------------

    public function getDate($date, $returnTimestamp = false, $format = NULL)
    {
        if (is_numeric($date)) {
            $result = (int)$date;
        } else {
            $result = strtotime($date);
        }

        if (is_null($format)) {
            $format = 'Y-m-d H:i:s';
        }

        $result = date($format, $result);

        if ($returnTimestamp) {
            return strtotime($result);
        }

        return $result;
    }

    // ---------------------------------------

    public function gmtDateToTimezone($dateGmt, $returnTimestamp = false, $format = NULL)
    {
        if ($returnTimestamp) {
            return (int)Mage::getModel('core/date')->timestamp($dateGmt);
        }
        return Mage::getModel('core/date')->date($format,$dateGmt);
    }

    public function timezoneDateToGmt($dateTimezone, $returnTimestamp = false, $format = NULL)
    {
        if ($returnTimestamp) {
            return (int)Mage::getModel('core/date')->gmtTimestamp($dateTimezone);
        }
        return Mage::getModel('core/date')->gmtDate($format,$dateTimezone);
    }

    //########################################

    public function escapeJs($string)
    {
        return str_replace(array("\\"  , "\n"  , "\r" , "\""  , "'"),
                           array("\\\\", "\\n" , "\\r", "\\\"", "\\'"),
                           $string);
    }

    public function escapeHtml($data, $allowedTags = null, $flags = ENT_COMPAT)
    {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $item) {
                $result[] = $this->escapeHtml($item, $allowedTags, $flags);
            }
        } else {
            // process single item
            if (strlen($data)) {
                if (is_array($allowedTags) && !empty($allowedTags)) {
                    $allowed = implode('|', $allowedTags);

                    $pattern = '/<([\/\s\r\n]*)(' . $allowed . ')'.
                        '((\s+\w+="[\w\s\%\?=&#\/\.;:_\-\(\)]*")*[\/\s\r\n]*)>/si';
                    $result = preg_replace($pattern, '##$1$2$3##', $data);

                    $result = htmlspecialchars($result, $flags);

                    $pattern = '/##([\/\s\r\n]*)(' . $allowed . ')'.
                        '((\s+\w+="[\w\s\%\?=&#\/\.;:_\-\(\)]*")*[\/\s\r\n]*)##/si';
                    $result = preg_replace($pattern, '<$1$2$3>', $result);
                } else {
                    $result = htmlspecialchars($data, $flags);
                }
            } else {
                $result = $data;
            }
        }
        return $result;
    }

    //########################################

    public function reduceWordsInString($string, $neededLength, $longWord = 6, $minWordLen = 2, $atEndOfWord = '.')
    {
        if (strlen($string) <= $neededLength) {
            return $string;
        }

        $longWords = array();
        foreach (explode(' ', $string) as $word) {
            if (strlen($word) >= $longWord && !preg_match('/[0-9]/', $word)) {
                $longWords[$word] = strlen($word) - $minWordLen;
            }
        }

        $canBeReduced = 0;
        foreach ($longWords as $canBeReducedForWord) {
            $canBeReduced += $canBeReducedForWord;
        }

        $needToBeReduced = strlen($string) - $neededLength + (count($longWords) * strlen($atEndOfWord));

        if ($canBeReduced < $needToBeReduced) {
            return $string;
        }

        $weightOfOneLetter = $needToBeReduced / $canBeReduced;
        foreach ($longWords as $word => $canBeReducedForWord) {

            $willReduced = ceil($weightOfOneLetter * $canBeReducedForWord);
            $reducedWord = substr($word, 0, strlen($word) - $willReduced) . $atEndOfWord;

            $string = str_replace($word, $reducedWord, $string);

            if (strlen($string) <= $neededLength) {
                break;
            }
        }

        return $string;
    }

    public function convertStringToSku($title)
    {
        $skuVal = strtolower($title);
        $skuVal = str_replace(array(" ", ":", ",", ".", "?", "*", "+", "(", ")", "&", "%", "$", "#", "@",
                                    "!", '"', "'", ";", "\\", "|", "/", "<", ">"), "-", $skuVal);

        return $skuVal;
    }

    public function stripInvisibleTags($text)
    {
        $text = preg_replace(
            array(
                // Remove invisible content
                '/<head[^>]*?>.*?<\/head>/siu',
                '/<style[^>]*?>.*?<\/style>/siu',
                '/<script[^>]*?.*?<\/script>/siu',
                '/<object[^>]*?.*?<\/object>/siu',
                '/<embed[^>]*?.*?<\/embed>/siu',
                '/<applet[^>]*?.*?<\/applet>/siu',
                '/<noframes[^>]*?.*?<\/noframes>/siu',
                '/<noscript[^>]*?.*?<\/noscript>/siu',
                '/<noembed[^>]*?.*?<\/noembed>/siu',

                // Add line breaks before & after blocks
                '/<((br)|(hr))/iu',
                '/<\/?((address)|(blockquote)|(center)|(del))/iu',
                '/<\/?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))/iu',
                '/<\/?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))/iu',
                '/<\/?((table)|(th)|(td)|(caption))/iu',
                '/<\/?((form)|(button)|(fieldset)|(legend)|(input))/iu',
                '/<\/?((label)|(select)|(optgroup)|(option)|(textarea))/iu',
                '/<\/?((frameset)|(frame)|(iframe))/iu',
            ),
            array(
                ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
                "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
                "\n\$0", "\n\$0",
            ),
            $text);

        return $text;
    }

    public function arrayReplaceRecursive($base, $replacements)
    {
        $args = func_get_args();
        foreach (array_slice($args, 1) as $replacements) {

            $bref_stack = array(&$base);
            $head_stack = array($replacements);

            do {
                end($bref_stack);

                $bref = &$bref_stack[key($bref_stack)];
                $head = array_pop($head_stack);

                unset($bref_stack[key($bref_stack)]);

                foreach (array_keys($head) as $key) {

                    if (isset($key, $bref, $bref[$key]) && is_array($bref[$key]) && is_array($head[$key])) {
                        $bref_stack[] = &$bref[$key];
                        $head_stack[] = $head[$key];
                    } else {
                        $bref[$key] = $head[$key];
                    }
                }
            } while (count($head_stack));
        }

        return $base;
    }

    //########################################

    public function makeBackUrlParam($backIdOrRoute, array $backParams = array())
    {
        $paramsString = count($backParams) > 0 ? '|'.http_build_query($backParams,'','&') : '';
        return base64_encode($backIdOrRoute.$paramsString);
    }

    public function getBackUrlParam($defaultBackIdOrRoute = 'index',
                                    array $defaultBackParams = array())
    {
        $requestParams = Mage::app()->getRequest()->getParams();
        return isset($requestParams['back'])
            ? $requestParams['back'] : $this->makeBackUrlParam($defaultBackIdOrRoute,$defaultBackParams);
    }

    // ---------------------------------------

    public function getBackUrl($defaultBackIdOrRoute = 'index',
                               array $defaultBackParams = array(),
                               array $extendedRoutersParams = array())
    {
        $back = base64_decode($this->getBackUrlParam($defaultBackIdOrRoute,$defaultBackParams));

        $params = array();

        if (strpos($back,'|') !== false) {
            $route = substr($back,0,strpos($back,'|'));
            parse_str(substr($back,strpos($back,'|')+1),$params);
        } else {
            $route = $back;
        }

        $extendedRoutersParamsTemp = array();
        foreach ($extendedRoutersParams as $extRouteName => $extParams) {
            if ($route == $extRouteName) {
                $params = array_merge($params,$extParams);
            } else {
                $extendedRoutersParamsTemp[$route] = $params;
            }
        }
        $extendedRoutersParams = $extendedRoutersParamsTemp;

        $route == 'index' && $route = '*/*/index';
        $route == 'list' && $route = '*/*/index';
        $route == 'edit' && $route = '*/*/edit';
        $route == 'view' && $route = '*/*/view';

        foreach ($extendedRoutersParams as $extRouteName => $extParams) {
            if ($route == $extRouteName) {
                $params = array_merge($params,$extParams);
            }
        }

        return Mage::helper('adminhtml')->getUrl($route,$params);
    }

    //########################################

    public function getClassConstantAsJson($class)
    {
        if (stripos($class,'Ess_M2ePro_') === false) {
            throw new Ess_M2ePro_Model_Exception('Class name must begin with "Ess_M2ePro"');
        }

        $reflectionClass = new ReflectionClass($class);
        $tempConstants = $reflectionClass->getConstants();

        $constants = array();
        foreach ($tempConstants as $key => $value) {
            $constants[] = array(strtoupper($key), $value);
        }

        return json_encode($constants);
    }

    public function getControllerActions($controllerClass, array $params = array())
    {
        $controllerClass = Mage::helper('M2ePro/View_Development_Controller')->loadControllerAndGetClassName(
            $controllerClass
        );

        $route = str_replace('Ess_M2ePro_','',$controllerClass);
        $route = preg_replace('/Controller$/','',$route);
        $route = explode('_',$route);

        foreach ($route as &$part) {
            $part{0} = strtolower($part{0});
        }
        unset($part);

        $route = implode('_',$route) . '/';

        $reflectionClass = new ReflectionClass($controllerClass);

        $actions = array();
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {

            if (!preg_match('/Action$/',$method->name)) {
                continue;
            }

            $methodName = preg_replace('/Action$/','',$method->name);

            $actions[$route . $methodName] = Mage::helper('adminhtml')->getUrl('M2ePro/'.$route.$methodName, $params);
        }

        return $actions;
    }

    //########################################

    public function generateUniqueHash($strParam = NULL, $maxLength = NULL)
    {
        $hash = sha1(rand(1,1000000).microtime(true).(string)$strParam);
        (int)$maxLength > 0 && $hash = substr($hash,0,(int)$maxLength);
        return $hash;
    }

    public function theSameItemsInData($data, $keysToCheck)
    {
        if (count($data) > 200) {
            return false;
        }

        $preparedData = array();

        foreach ($keysToCheck as $key) {
            $preparedData[$key] = array();
        }

        foreach ($data as $item) {
            foreach ($keysToCheck as $key) {
                $preparedData[$key][] = $item[$key];
            }
        }

        foreach ($keysToCheck as $key) {
            $preparedData[$key] = array_unique($preparedData[$key]);
            if (count($preparedData[$key]) > 1) {
                return false;
            }
        }

        return true;
    }

    public function getMainStatus($statuses)
    {
        foreach (array(self::STATUS_ERROR, self::STATUS_WARNING) as $status) {
            if (in_array($status, $statuses)) {
                return $status;
            }
        }

        return self::STATUS_SUCCESS;
    }

    //########################################

    public function isISBN($string)
    {
        return $this->isISBN10($string) || $this->isISBN13($string);
    }

    // ---------------------------------------

    public function isISBN10($string)
    {
        if (strlen($string) != 10) {
            return false;
        }

        $a = 0;
        for ($i = 0; $i < 10; $i++) {
            if ($string[$i] == "X" || $string[$i] == "x") {
                $a += 10 * intval(10 - $i);
            } else if (is_numeric($string[$i])) {
                $a += intval($string[$i]) * intval(10 - $i);
            } else {
                return false;
            }
        }
        return ($a % 11 == 0);
    }

    public function isISBN13($string)
    {
        if (strlen($string) != 13) {
            return false;
        }

        if (substr($string,0,3) != '978') {
            return false;
        }

        $check = 0;
        for ($i = 0; $i < 13; $i += 2) $check += (int)substr($string, $i, 1);
        for ($i = 1; $i < 12; $i += 2) $check += 3 * substr($string, $i, 1);

        return $check % 10 == 0;
    }

    //########################################

    public function isUPC($upc)
    {
        return $this->isWorldWideId($upc,'UPC');
    }

    public function isEAN($ean)
    {
        return $this->isWorldWideId($ean,'EAN');
    }

    // ---------------------------------------

    private function isWorldWideId($worldWideId,$type)
    {
        $adapters = array(
            'UPC' => array(
                '12' => 'Upca'
            ),
            'EAN' => array(
                '13' => 'Ean13'
            )
        );

        $length = strlen($worldWideId);

        if (!isset($adapters[$type],$adapters[$type][$length])) {
            return false;
        }

        try {
            $validator = new Zend_Validate_Barcode($adapters[$type][$length]);
            $result = $validator->isValid($worldWideId);
        } catch (Zend_Validate_Exception $e) {
            return false;
        }

        return $result;
    }

    //########################################
}