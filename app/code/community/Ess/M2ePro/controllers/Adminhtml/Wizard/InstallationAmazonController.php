<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Wizard_InstallationAmazonController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_WizardController
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
             ->addJs('M2ePro/Amazon/Marketplace/SynchProgressHandler.js')
             ->addJs('M2ePro/MarketplaceHandler.js')
             ->addJs('M2ePro/Wizard/InstallationAmazon.js')
             ->addJs('M2ePro/Wizard/InstallationAmazon/MarketplaceHandler.js')
             ->addJs('M2ePro/Wizard/InstallationAmazon/CustomHandler.js');

        return $this;
    }

    //########################################

    protected function getNick()
    {
        return Ess_M2ePro_Helper_View_Amazon::WIZARD_INSTALLATION_NICK;
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
                $licenseData[$key] = Mage::helper('M2ePro')->escapeJs(
                    Mage::helper('M2ePro')->escapeHtml($tempValue)
                );
                continue;
            }

            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result'  => false,
                        'message' => Mage::helper('M2ePro')->__('You should fill all required fields.')
                    )
                )
            );
        }

        $registry = Mage::getModel('M2ePro/Registry')->load('/wizard/license_form_data/', 'key');
        $registry->setData('key', '/wizard/license_form_data/');
        $registry->setData('value', Mage::helper('M2ePro')->jsonEncode($licenseData));
        $registry->save();

        $message = null;

        if (Mage::helper('M2ePro/Module_License')->getKey()) {
            try {
                $result = Mage::helper('M2ePro/Module_License')->updateLicenseUserInfo(
                    $licenseData['email'],
                    $licenseData['firstname'], $licenseData['lastname'],
                    $licenseData['country'], $licenseData['city'],
                    $licenseData['postal_code'], $licenseData['phone']
                );
            } catch (Exception $e) {
                Mage::helper('M2ePro/Module_Exception')->process($e);
                $result = false;
                $message = Mage::helper('M2ePro')->__($e->getMessage());
            }

            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result'  => $result,
                        'message' => $message
                    )
                )
            );
        }

        try {
            $result = Mage::helper('M2ePro/Module_License')->obtainRecord(
                $licenseData['email'],
                $licenseData['firstname'], $licenseData['lastname'],
                $licenseData['country'], $licenseData['city'],
                $licenseData['postal_code'], $licenseData['phone']
            );
        } catch (Exception $e) {
            Mage::helper('M2ePro/Module_Exception')->process($e);
            $result = false;
            $message = Mage::helper('M2ePro')->__($e->getMessage());
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                    'result'  => $result,
                    'message' => $message
                )
            )
        );
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
                $licenseData[$key] = Mage::helper('M2ePro')->escapeJs(
                    Mage::helper('M2ePro')->escapeHtml($tempValue)
                );
                continue;
            }

            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result'  => false,
                        'message' => Mage::helper('M2ePro')->__('You should fill all required fields.')
                    )
                )
            );
        }

        $result = true;
        $message = null;

        if (Mage::helper('M2ePro/Module_License')->getKey()) {
            try {
                $result = Mage::helper('M2ePro/Module_License')->updateLicenseUserInfo(
                    $licenseData['email'],
                    $licenseData['firstname'], $licenseData['lastname'],
                    $licenseData['country'], $licenseData['city'],
                    $licenseData['postal_code'], $licenseData['phone']
                );

                $registry = Mage::getModel('M2ePro/Registry')->load('/wizard/license_form_data/', 'key');
                $registry->setData('key', '/wizard/license_form_data/');
                $registry->setData('value', Mage::helper('M2ePro')->jsonEncode($licenseData));
                $registry->save();
            } catch (Exception $e) {
                Mage::helper('M2ePro/Module_Exception')->process($e);
                $result = false;
                $message = Mage::helper('M2ePro')->__($e->getMessage());
            }
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                    'result'  => $result,
                    'message' => $message
                )
            )
        );
    }

    //########################################
}
