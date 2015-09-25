<?php

    /*
    * @copyright  Copyright (c) 2013 by  ESS-UA.
    */

class Ess_M2ePro_Adminhtml_Wizard_InstallationCommonController
    extends Ess_M2ePro_Controller_Adminhtml_Common_WizardController
{
    //#############################################

    protected function _initAction()
    {
        $result = parent::_initAction();

        $this->getLayout()->getBlock('head')
                          ->addJs('M2ePro/Wizard/InstallationCommon.js')
                          ->addJs('M2ePro/Configuration/ComponentsHandler.js');

        return $result;
    }

    protected function getNick()
    {
        return Ess_M2ePro_Helper_View_Common::WIZARD_INSTALLATION_NICK;
    }

    //#############################################

    public function congratulationAction()
    {
        if (!$this->isFinished()) {
            return $this->_redirect('*/*/index');
        }

        Mage::helper('M2ePro/Magento')->clearMenuCache();

        if ($nextWizard = $this->getWizardHelper()->getActiveWizard($this->getCustomViewNick())) {
            return $this->_redirect('*/adminhtml_wizard_'.$this->getWizardHelper()->getNick($nextWizard));
        }

        $this->_initAction();
        $this->_addContent($this->getWizardHelper()->createBlock('congratulation',$this->getNick()));
        $this->renderLayout();
    }

    //#############################################

    public function createLicenseAction()
    {
        $requiredKeys = array(
            'email',
            'firstname',
            'lastname',
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
            return $this->getResponse()->setBody(json_encode($response));
        }

        $registry = Mage::getModel('M2ePro/Registry')->load('/wizard/license_form_data/', 'key');
        $registry->setData('key', '/wizard/license_form_data/');
        $registry->setData('value', json_encode($licenseData));
        $registry->save();

        if (Mage::helper('M2ePro/Module_License')->getKey()) {
            return $this->getResponse()->setBody(json_encode(array('result' => true)));
        }

        $licenseResult = Mage::helper('M2ePro/Module_License')->obtainRecord(
            $licenseData['email'],
            $licenseData['firstname'], $licenseData['lastname'],
            $licenseData['country'], $licenseData['city'], $licenseData['postal_code']
        );

        return $this->getResponse()->setBody(json_encode(array('result' => $licenseResult)));
    }

    //#############################################
}