<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Log_Abstract extends Ess_M2ePro_Model_Abstract
{
    const TYPE_NOTICE   = 1;
    const TYPE_SUCCESS  = 2;
    const TYPE_WARNING  = 3;
    const TYPE_ERROR    = 4;

    const PRIORITY_HIGH    = 1;
    const PRIORITY_MEDIUM  = 2;
    const PRIORITY_LOW     = 3;

    protected $componentMode = NULL;

    //########################################

    public function setComponentMode($mode)
    {
        $mode = strtolower((string)$mode);
        $mode && $this->componentMode = $mode;
        return $this;
    }

    public function getComponentMode()
    {
        return $this->componentMode;
    }

    //########################################

    public function getNextActionId()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_config');
        $groupConfig = '/logs/'.$this->getLastActionIdConfigKey().'/';

        $lastActionId = (int)$connRead->select()
            ->from($table,'value')
            ->where('`group` = ?',$groupConfig)
            ->where('`key` = ?','last_action_id')
            ->query()->fetchColumn();

        $nextActionId = $lastActionId + 1;

        $connWrite->update(
            $table,
            array('value' => $nextActionId),
            array('`group` = ?' => $groupConfig, '`key` = ?' => 'last_action_id')
        );

        return $nextActionId;
    }

    /**
     * @return string
     */
    public function getLastActionIdConfigKey()
    {
        return 'general';
    }

    // ---------------------------------------

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

        return json_encode($descriptionData);
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

        $descriptionData = json_decode($string,true);
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
                $tempValueArray = json_decode($value, true);
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

    protected function getActionTitleByClass($class, $type)
    {
        $reflectionClass = new ReflectionClass ($class);
        $tempConstants = $reflectionClass->getConstants();

        foreach ($tempConstants as $key => $value) {
            if ($key == '_'.$type) {
                return Mage::helper('M2ePro')->__($key);
            }
        }

        return '';
    }

    protected function getActionsTitlesByClass($class, $prefix)
    {
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

    // ---------------------------------------

    protected function clearMessagesByTable($tableNameOrModelName, $columnName = NULL, $columnId = NULL)
    {
        $logsTable  = Mage::getSingleton('core/resource')->getTableName($tableNameOrModelName);

        $where = array();
        if (!is_null($columnId)) {
            $where[$columnName.' = ?'] = $columnId;
        }

        if (!is_null($this->componentMode)) {
            $where['component_mode = ?'] = $this->componentMode;
        }

        Mage::getSingleton('core/resource')->getConnection('core_write')->delete($logsTable,$where);
    }

    //########################################
}