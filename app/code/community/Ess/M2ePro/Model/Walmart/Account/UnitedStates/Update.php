<?php

class Ess_M2ePro_Model_Walmart_Account_UnitedStates_Update
{
    /**
     * @param $authCode
     * @param $sellerId
     * @param $accountId
     * @param $clientId
     * @return Ess_M2ePro_Model_Account
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function updateAccount(
        $authCode,
        $sellerId,
        $accountId,
        $clientId = null
    ) {
        /** @var $account Ess_M2ePro_Model_Account */
        $account = Mage::getModel('M2ePro/Walmart_Account_Repository')->get($accountId);

        try {
            $responseData = $this->updateOnServer(
                $authCode,
                $sellerId,
                $account,
                $clientId
            );

            if ($account->getChildObject()->getIdentifier() === '') {
                $account->getChildObject()->addData(
                    array(
                        Ess_M2ePro_Model_Resource_Walmart_Account::COLUMN_IDENTIFIER => $responseData['identifier']
                    )
                );
            }

            $account->getChildObject()->save();

        } catch (Exception $exception) {
            throw $exception;
        }

        return $account;
    }

    /**
     * @param $authCode
     * @param $sellerId
     * @param $clientId
     * @param Ess_M2ePro_Model_Account $account
     * @return array
     */
    private function updateOnServer(
        $authCode,
        $sellerId,
        $account,
        $clientId = null
    ) {
        $params = array();
        $params['marketplace_id'] = $account->getChildObject()->getMarketplaceId();
        $params['seller_id'] = $sellerId;
        $params['auth_code'] = $authCode;

        if ($clientId !== null) {
            $params['client_id'] = $clientId;
        }

        /** @var $dispatcher Ess_M2ePro_Model_Walmart_Connector_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');

        /** @var Ess_M2ePro_Model_Walmart_Connector_Account_Update_EntityRequester $connectorObj */
        $connectorObj = $dispatcher->getConnectorByClass(
            'Ess_M2ePro_Model_Walmart_Connector_Account_Update_EntityRequester',
            $params,
            $account
        );

        $dispatcher->process($connectorObj);

        return $connectorObj->getResponseData();
    }
}