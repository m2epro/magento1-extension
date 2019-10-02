<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Wizard_InstallationWalmartController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_WizardController
{
    //########################################

    protected function _initAction()
    {
        parent::_initAction();

        $this->getLayout()->getBlock('head')
             ->addCss('M2ePro/css/Plugin/ProgressBar.css')
             ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
             ->addJs('M2ePro/Plugin/ProgressBar.js')
             ->addJs('M2ePro/Plugin/AreaWrapper.js')
             ->addJs('M2ePro/SynchProgressHandler.js')
             ->addJs('M2ePro/Walmart/Marketplace/SynchProgressHandler.js')
             ->addJs('M2ePro/MarketplaceHandler.js')
             ->addJs('M2ePro/Wizard/InstallationWalmart.js')
             ->addJs('M2ePro/Wizard/InstallationWalmart/MarketplaceHandler.js')
             ->addJs('M2ePro/Wizard/InstallationWalmart/CustomHandler.js')
             ->addJs('M2ePro/Configuration/ComponentsHandler.js')
             ->addJs('M2ePro/Walmart/Configuration/GeneralHandler.js');

        return $this;
    }

    //########################################

    protected function getNick()
    {
        return Ess_M2ePro_Helper_View_Walmart::WIZARD_INSTALLATION_NICK;
    }

    //########################################

    public function indexAction()
    {
        parent::indexAction();
    }

    //########################################

    public function createLicenseAction()
    {
        $requiredKeys = array(
            'email',
            'firstname',
            'lastname',
            'phone',
            'country',
            'city',
            'postal_code',
        );

        $licenseData = array();
        foreach ($requiredKeys as $key) {
            if ($tempValue = $this->getRequest()->getParam($key)) {
                $licenseData[$key] = $tempValue;
                continue;
            }

            $response = array(
                'result'  => false,
                'message' => Mage::helper('M2ePro')->__('You should fill all required fields.')
            );
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($response));
        }

        $registry = Mage::getModel('M2ePro/Registry')->load('/wizard/license_form_data/', 'key');
        $registry->setData('key', '/wizard/license_form_data/');
        $registry->setData('value', Mage::helper('M2ePro')->jsonEncode($licenseData));
        $registry->save();

        if (Mage::helper('M2ePro/Module_License')->getKey()) {
            Mage::helper('M2ePro/Module_License')->updateLicenseUserInfo(
                $licenseData['email'],
                $licenseData['firstname'], $licenseData['lastname'],
                $licenseData['country'], $licenseData['city'],
                $licenseData['postal_code'], $licenseData['phone']
            );

            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => true)));
        }

        $licenseResult = Mage::helper('M2ePro/Module_License')->obtainRecord(
            $licenseData['email'],
            $licenseData['firstname'], $licenseData['lastname'],
            $licenseData['country'], $licenseData['city'],
            $licenseData['postal_code'], $licenseData['phone']
        );

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => $licenseResult)));
    }

    //########################################

    public function updateLicenseUserInfoAction()
    {

        $requiredKeys = array(
            'email',
            'firstname',
            'lastname',
            'phone',
            'country',
            'city',
            'postal_code',
        );

        $licenseData = array();
        foreach ($requiredKeys as $key) {
            if ($tempValue = $this->getRequest()->getParam($key)) {
                $licenseData[$key] = $tempValue;
                continue;
            }

            $response = array(
                'result'  => false,
                'message' => Mage::helper('M2ePro')->__('You should fill all required fields.')
            );
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($response));
        }

        $result = true;

        if (Mage::helper('M2ePro/Module_License')->getKey()) {
            $result = Mage::helper('M2ePro/Module_License')->updateLicenseUserInfo(
                $licenseData['email'],
                $licenseData['firstname'], $licenseData['lastname'],
                $licenseData['country'], $licenseData['city'],
                $licenseData['postal_code'], $licenseData['phone']
            );

            if ($result) {
                $registry = Mage::getModel('M2ePro/Registry')->load('/wizard/license_form_data/', 'key');
                $registry->setData('key', '/wizard/license_form_data/');
                $registry->setData('value', Mage::helper('M2ePro')->jsonEncode($licenseData));
                $registry->save();
            }
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => $result)));
    }

    //########################################
}