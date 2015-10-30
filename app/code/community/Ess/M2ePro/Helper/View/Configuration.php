<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_View_Configuration extends Mage_Core_Helper_Abstract
{
    // M2ePro_TRANSLATIONS
    // Configuration

    const NICK  = 'configuration';

    const CONFIG_SECTION_COMPONENTS     = 'm2epro_components';
    const CONFIG_SECTION_SETTINGS       = 'm2epro_settings';
    const CONFIG_SECTION_LOGS_CLEARING  = 'm2epro_logs_clearing';
    const CONFIG_SECTION_LICENSE        = 'm2epro_license';

    //########################################

    public function getTitle()
    {
        return Mage::helper('M2ePro')->__('Configuration');
    }

    //########################################

    public function getComponentsUrl(array $params = array())
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit',array_merge(array(
            'section' => self::CONFIG_SECTION_COMPONENTS
        ), $params));
    }

    public function getSettingsUrl(array $params = array())
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit',array_merge(array(
            'section' => self::CONFIG_SECTION_SETTINGS
        ), $params));
    }

    public function getLogsClearingUrl(array $params = array())
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit',array_merge(array(
            'section' => self::CONFIG_SECTION_LOGS_CLEARING
        ), $params));
    }

    public function getLicenseUrl(array $params = array())
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit',array_merge(array(
            'section' => self::CONFIG_SECTION_LICENSE
        ), $params));
    }

    //########################################
}