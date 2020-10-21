<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Configuration_SettingsController
    extends Ess_M2ePro_Controller_Adminhtml_Configuration_MainController
{
    //########################################

    public function saveAction()
    {
        Mage::helper('M2ePro/Module_Configuration')->setConfigValues($this->getRequest()->getPost());

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Global Settings was saved.'));
        $this->_redirectUrl($this->_getRefererUrl());
    }

    //########################################

    public function restoreBlockNoticesAction()
    {
        foreach (Mage::getModel('M2ePro/Listing')->getCollection() as $listing) {
            /** @var $listing Ess_M2ePro_Model_Listing */

            $additionalData = $listing->getSettings('additional_data');

            if ($listing->isComponentModeEbay()) {
                unset($additionalData['mode_same_category_data']);
            }

            if ($listing->isComponentModeAmazon()) {
                unset($additionalData['show_new_asin_step']);
            }

            $listing->setSettings('additional_data', $additionalData);
            $listing->save();
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('success' => true)));
    }

    //########################################
}
