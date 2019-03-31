<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module_Log extends Mage_Core_Helper_Abstract
{
    const TYPE_NOTICE   = 1;
    const TYPE_SUCCESS  = 2;
    const TYPE_WARNING  = 3;
    const TYPE_ERROR    = 4;

    //########################################

    /**
     * @param string $string
     * @param array $params
     * @param array $links
     * @return string
     */
    public function encodeDescription($string, array $params = array(), array $links = array())
    {
        if (count($params) <= 0 && count($links) <= 0) {
            return $string;
        }

        $descriptionData = array(
            'string' => $string,
            'params' => $params,
            'links'  => $links
        );

        return Mage::helper('M2ePro')->jsonEncode($descriptionData);
    }

    /**
     * @param string $string
     * @return string
     */
    public function decodeDescription($string)
    {
        if (!is_string($string) || $string == '') {
            return '';
        }

        if ($string{0} != '{') {
            return Mage::helper('M2ePro')->__($string);
        }

        $descriptionData = Mage::helper('M2ePro')->jsonDecode($string);
        $string = Mage::helper('M2ePro')->__($descriptionData['string']);

        if (!empty($descriptionData['params'])) {
            $string = $this->addPlaceholdersToMessage($string, $descriptionData['params']);
        }

        if (!empty($descriptionData['links'])) {
            $string = $this->addLinksToMessage($string, $descriptionData['links']);
        }

        return $string;
    }

    // ---------------------------------------

    protected function addPlaceholdersToMessage($string, $params)
    {
        foreach ($params as $key=>$value) {

            if (isset($value{0}) && $value{0} == '{') {
                $tempValueArray = Mage::helper('M2ePro')->jsonDecode($value);
                is_array($tempValueArray) && $value = $this->decodeDescription($value);
            }

            if ($key{0} == '!') {
                $key = substr($key,1);
            } else {
                $value = Mage::helper('M2ePro')->__($value);
            }

            $string = str_replace('%'.$key.'%',$value,$string);
        }

        return $string;
    }

    protected function addLinksToMessage($string, $links)
    {
        $readMoreLinks = array();
        $resultString = $string;

        foreach ($links as $link) {
            preg_match('/!\w*_start!/', $resultString, $foundedStartMatches);

            if (count($foundedStartMatches) == 0) {
                $readMoreLinks[] = $link;
                continue;
            } else {

                $startPart = $foundedStartMatches[0];
                $endPart = str_replace('start', 'end', $startPart);

                $wasFoundEndMatches = strpos($resultString, $endPart);

                if ($wasFoundEndMatches !== false) {

                    $openLinkTag = '<a href="' . $link . '" target="_blank">';
                    $closeLinkTag = '</a>';

                    $resultString = str_replace($startPart, $openLinkTag, $resultString);
                    $resultString = str_replace($endPart, $closeLinkTag, $resultString);

                } else {
                    $readMoreLinks[] = $link;
                }
            }
        }

        if (count($readMoreLinks) > 0) {

            foreach ($readMoreLinks as &$link) {
                $link = '<a href="' . $link . '" target="_blank">' . Mage::helper('M2ePro')->__('here') . '</a>';
            }

            $delimiter = Mage::helper('M2ePro')->__('or');
            $readMoreString = Mage::helper('M2ePro')->__('Details').' '.implode(' '.$delimiter.' ', $readMoreLinks).'.';

            $resultString .= ' ' . $readMoreString;
        }

        return $resultString;
    }

    //########################################

    public function getActionTitleByClass($class, $type)
    {
        $class = Mage::getConfig()->getModelClassName($class);
        $reflectionClass = new ReflectionClass ($class);
        $tempConstants = $reflectionClass->getConstants();

        foreach ($tempConstants as $key => $value) {
            if ($key == '_'.$type) {
                return Mage::helper('M2ePro')->__($key);
            }
        }

        return '';
    }

    public function getActionsTitlesByClass($class)
    {
        switch ($class) {

            case 'Listing_Log':
            case 'Listing_Other_Log':
            case 'Ebay_Account_PickupStore_Log':
                $prefix = 'ACTION_';
                break;

            case 'Synchronization_Log':
                $prefix = 'TASK_';
                break;
        }

        $class = Mage::getConfig()->getModelClassName('M2ePro/'.$class);
        $reflectionClass = new ReflectionClass ($class);
        $tempConstants = $reflectionClass->getConstants();

        $actionsNames = array();
        foreach ($tempConstants as $key => $value) {
            if (substr($key,0,strlen($prefix)) == $prefix) {
                $actionsNames[$key] = $value;
            }
        }

        $actionsValues = array();
        foreach ($actionsNames as $action => $valueAction) {
            foreach ($tempConstants as $key => $valueConstant) {
                if ($key == '_'.$action) {
                    $actionsValues[$valueAction] = Mage::helper('M2ePro')->__($valueConstant);
                }
            }
        }

        return $actionsValues;
    }

    public function getStatusByResultType($resultType)
    {
        $typesStatusesMap = array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE  => Ess_M2ePro_Helper_Data::STATUS_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => Ess_M2ePro_Helper_Data::STATUS_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => Ess_M2ePro_Helper_Data::STATUS_WARNING,
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR   => Ess_M2ePro_Helper_Data::STATUS_ERROR,
        );

        return $typesStatusesMap[$resultType];
    }

    //########################################
}