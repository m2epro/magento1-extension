<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_Template_ShippingController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    public function updateDiscountProfilesAction()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');

        /** @var $account Ess_M2ePro_Model_Ebay_Account */
        $account = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Account', $accountId)->getChildObject();
        $account->updateShippingDiscountProfiles($marketplaceId);

        $accountProfiles = json_decode($account->getData('ebay_shipping_discount_profiles'), true);

        $profiles = array();
        if (is_array($accountProfiles) && isset($accountProfiles[$marketplaceId]['profiles'])) {
            foreach ($accountProfiles[$marketplaceId]['profiles'] as $profile) {
                $profiles[] = array(
                    'type' => Mage::helper('M2ePro')->escapeHtml($profile['type']),
                    'profile_id' => Mage::helper('M2ePro')->escapeHtml($profile['profile_id']),
                    'profile_name' => Mage::helper('M2ePro')->escapeHtml($profile['profile_name'])
                );
            }
        }

        $this->loadLayout();
        $this->getResponse()->setBody(json_encode($profiles));
    }

    //########################################
}