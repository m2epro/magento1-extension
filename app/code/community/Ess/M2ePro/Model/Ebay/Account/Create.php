<?php

use Ess_M2ePro_Model_Ebay_Account as EbayAccount;

class Ess_M2ePro_Model_Ebay_Account_Create
{
    public function create($authCode, $mode)
    {
        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Ebay')->getModel('Account');
        $responseData = $this->createOnServer($authCode, $mode);

        $data = Mage::getModel('M2ePro/Ebay_Account_Builder')->getDefaultData();

        if ($this->isAccountExists($responseData['user_id'], $account->getId())) {
            throw new Ess_M2ePro_Model_Exception('An account with the same eBay User ID already exists.');
        }

        $data['mode'] = $mode;
        $data['is_token_exist'] = 1;
        $data['info'] = Mage::helper('M2ePro')->jsonEncode($responseData['info']);
        $data['sell_api_token_expired_date'] = $responseData['token_expired_date'];
        $data['server_hash'] = $responseData['hash'];
        $data['user_id'] = $responseData['user_id'];
        $data['title'] = $responseData['user_id'];

        Mage::getModel('M2ePro/Ebay_Account_Builder')->build($account, $data);

        Mage::getModel('M2ePro/Ebay_Account_Store_Category_Update')->process($account->getChildObject());

        $account->getChildObject()->updateUserPreferences();

        return $account;
    }

    private function createOnServer($authCode, $mode)
    {
        $params = array(
            'mode' => $mode == EbayAccount::MODE_PRODUCTION ? 'production' : 'sandbox',
            'auth_code' => $authCode
        );

        $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');

        $connectorObj = $dispatcherObject->getVirtualConnector(
            'account',
            'add',
            'entity',
            $params,
            null,
            null,
            null
        );

        $dispatcherObject->process($connectorObj);

        return $connectorObj->getResponseData();
    }

    private function isAccountExists($userId, $newAccountId)
    {
        /** @var Ess_M2ePro_Model_Resource_Account_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account')
            ->addFieldToSelect('title')
            ->addFieldToFilter('user_id', $userId)
            ->addFieldToFilter('id', array('neq' => $newAccountId));

        return $collection->getSize();
    }
}