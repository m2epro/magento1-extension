<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_Magento_Configuration extends Ess_M2ePro_Model_Observer_Abstract
{
    //########################################

    public function process()
    {
        $request = Mage::app()->getRequest();

        if ($request->getParam('M2ePro_already_forwarded')) {
            return;
        }

        switch (Mage::app()->getRequest()->getParam('section')) {

            case Ess_M2ePro_Helper_View_Configuration::CONFIG_SECTION_COMPONENTS;
                $controllerName = 'adminhtml_configuration_components';
                $action = 'save';
                break;

            case Ess_M2ePro_Helper_View_Configuration::CONFIG_SECTION_SETTINGS;
                $controllerName = 'adminhtml_configuration_settings';
                $action = 'save';
                break;

            case Ess_M2ePro_Helper_View_Configuration::CONFIG_SECTION_LOGS_CLEARING;
                $controllerName = 'adminhtml_configuration_logsClearing';
                $action = 'save';
                break;

            case Ess_M2ePro_Helper_View_Configuration::CONFIG_SECTION_LICENSE;
                $controllerName = 'adminhtml_configuration_license';
                $action = 'confirmKey';
                break;

            default:
                return;
        }

        $request->initForward()
                ->setParam('M2ePro_already_forwarded', true)
                ->setModuleName('M2ePro')
                ->setControllerName($controllerName)
                ->setActionName($action)
                ->setDispatched(false);
    }

    //########################################
}