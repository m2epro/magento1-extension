<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
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

        $accountProfiles = Mage::helper('M2ePro')->jsonDecode($account->getData('ebay_shipping_discount_profiles'));

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
        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($profiles));
    }

    //########################################

    public function getRateTableDataAction()
    {
        $accountId     = $this->getRequest()->getParam('account_id', false);
        $marketplaceId = $this->getRequest()->getParam('marketplace_id', false);
        $type          = $this->getRequest()->getParam('type', false);

        if (!$accountId || !$marketplaceId || !$type) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array(
                'error' => Mage::helper('M2ePro')->__('Wrong parameters.')
            )));
        }

        $account = Mage::helper('M2ePro/Component_Ebay')->getModel('Account')->load($accountId);
        /** @var Ess_M2ePro_Model_Ebay_Account $ebayAccount */
        $ebayAccount = $account->getChildObject();

        if (!$ebayAccount->getSellApiTokenSession()) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array(
                'sell_api_disabled' => true,
                'error' => Mage::helper('M2ePro')->__('Sell Api token is missing.')
            )));
        }

        try {
            $ebayAccount->updateRateTables();
        } catch (Exception $exception) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array(
                'error' => $exception->getMessage()
            )));
        }

        $rateTables = $ebayAccount->getRateTables();

        $marketplace = Mage::helper('M2ePro/Component_Ebay')->getModel('Marketplace')->load($marketplaceId);
        $countryCode = $marketplace->getChildObject()->getOriginCountry();
        $type = $type == 'local' ? 'domestic' : 'international';

        $rateTablesData = array();
        foreach ($rateTables as $rateTable) {
            if (empty($rateTable['countryCode']) ||
                strtolower($rateTable['countryCode']) != $countryCode ||
                strtolower($rateTable['locality']) != $type) {

                continue;
            }

            if (empty($rateTable['rateTableId'])) {
                continue;
            }

            $rateTablesData[$rateTable['rateTableId']] = isset($rateTable['name']) ? $rateTable['name'] :
                $rateTable['rateTableId'];
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array(
            'data' => $rateTablesData
        )));
    }

    //########################################
}