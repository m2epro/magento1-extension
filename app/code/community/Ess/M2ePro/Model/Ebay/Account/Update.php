<?php

use Ess_M2ePro_Model_Ebay_Account as EbayAccount;

class Ess_M2ePro_Model_Ebay_Account_Update
{
    public function updateCredentials($account, $authCode, $mode)
    {
        $responseData = $this->updateOnServer($account, $authCode, $mode);

        $dataForUpdate = array(
            'info' => Mage::helper('M2ePro')->jsonEncode($responseData['info']),
            'sell_api_token_expired_date' => $responseData['token_expired_date'],
            'user_id' => $responseData['user_id'],
            'is_token_exist' => 1
        );

        $account->getChildObject()->addData($dataForUpdate);
        $account->getChildObject()->save();

        Mage::getModel('M2ePro/Ebay_Account_Store_Category_Update')->process($account->getChildObject());

        $account->getChildObject()->updateUserPreferences();

        return $account;
    }

    private function updateOnServer($account, $authCode, $mode)
    {
        $params = array(
            'mode' => $mode == EbayAccount::MODE_PRODUCTION ? 'production' : 'sandbox',
            'auth_code' => $authCode
        );

        $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');

        $connectorObj = $dispatcherObject->getVirtualConnector(
            'account',
            'update',
            'entity',
            $params,
            null,
            null,
            $account
        );

        $dispatcherObject->process($connectorObj);

        return $connectorObj->getResponseData();
    }
}