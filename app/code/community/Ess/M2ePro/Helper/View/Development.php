<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_View_Development extends Mage_Core_Helper_Abstract
{
    // M2ePro_TRANSLATIONS
    // Control Panel (M2E Pro)

    const NICK            = 'development';

    const TAB_SUMMARY     = 'summary';
    const TAB_ABOUT       = 'about';
    const TAB_INSPECTION  = 'inspection';
    const TAB_DATABASE    = 'database';
    const TAB_TOOLS       = 'tools';
    const TAB_MODULE      = 'module';
    const TAB_DEBUG       = 'debug';
    const TAB_BUILD       = 'build';

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
        return 'M2ePro/adminhtml_development/index';
    }

    //########################################

    public function getPageAboutTabUrl(array $params = array())
    {
        return $this->getPageUrl(array_merge($params,array('tab' => self::TAB_ABOUT)));
    }

    public function getPageInspectionTabUrl(array $params = array())
    {
        return $this->getPageUrl(array_merge($params,array('tab' => self::TAB_INSPECTION)));
    }

    public function getPageDatabaseTabUrl(array $params = array())
    {
        return $this->getPageUrl(array_merge($params,array('tab' => self::TAB_DATABASE)));
    }

    public function getPageToolsTabUrl(array $params = array())
    {
        return $this->getPageUrl(array_merge($params,array('tab' => self::TAB_TOOLS)));
    }

    public function getPageModuleTabUrl(array $params = array())
    {
        return $this->getPageUrl(array_merge($params,array('tab' => self::TAB_MODULE)));
    }

    public function getPageDebugTabUrl(array $params = array())
    {
        return $this->getPageUrl(array_merge($params,array('tab' => self::TAB_DEBUG)));
    }

    public function getPageBuildTabUrl(array $params = array())
    {
        return $this->getPageUrl(array_merge($params,array('tab' => self::TAB_BUILD)));
    }

    //########################################
}