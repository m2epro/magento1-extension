<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_View_ControlPanel extends Mage_Core_Helper_Abstract
{
    const NICK = 'controlPanel';
    const MENU_ROOT_NODE_NICK = 'm2epro/help';

    const TAB_SUMMARY     = 'summary';
    const TAB_ABOUT       = 'about';
    const TAB_INSPECTION  = 'inspection';
    const TAB_DATABASE    = 'database';
    const TAB_TOOLS       = 'tools';
    const TAB_MODULE      = 'module';
    const TAB_CRON        = 'cron';
    const TAB_DEBUG       = 'debug';

    //########################################

    public function getTitle()
    {
        return Mage::helper('M2ePro')->__('Control Panel (M2E Pro)');
    }

    //########################################

    public function getPageUrl(array $params = array())
    {
        return Mage::helper('adminhtml')->getUrl($this->getPageRoute(), $params);
    }

    public function getPageRoute()
    {
        return 'M2ePro/adminhtml_controlPanel/index';
    }

    //########################################

    public function getPageAboutTabUrl(array $params = array())
    {
        return $this->getPageUrl(array_merge($params, array('tab' => self::TAB_ABOUT)));
    }

    public function getPageInspectionTabUrl(array $params = array())
    {
        return $this->getPageUrl(array_merge($params, array('tab' => self::TAB_INSPECTION)));
    }

    public function getPageDatabaseTabUrl(array $params = array())
    {
        return $this->getPageUrl(array_merge($params, array('tab' => self::TAB_DATABASE)));
    }

    public function getPageToolsTabUrl(array $params = array())
    {
        return $this->getPageUrl(array_merge($params, array('tab' => self::TAB_TOOLS)));
    }

    public function getPageModuleTabUrl(array $params = array())
    {
        return $this->getPageUrl(array_merge($params, array('tab' => self::TAB_MODULE)));
    }

    public function getPageCronTabUrl(array $params = array())
    {
        return $this->getPageUrl(array_merge($params, array('tab' => self::TAB_CRON)));
    }

    public function getPageDebugTabUrl(array $params = array())
    {
        return $this->getPageUrl(array_merge($params, array('tab' => self::TAB_DEBUG)));
    }

    //########################################
}
