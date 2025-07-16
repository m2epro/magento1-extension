<?php

class Ess_M2ePro_Model_Walmart_Account_Canada_Update
{
    /**
     * @param $consumerId
     * @param $privateKey
     * @param $accountId
     * @return Ess_M2ePro_Model_Account
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function updateAccount(
        $consumerId,
        $privateKey,
        $accountId
    ) {
        /** @var $account Ess_M2ePro_Model_Account */
        $account = Mage::getModel('M2ePro/Walmart_Account_Repository')->get($accountId);

        try {
            $responseData = $this->updateOnServer(
                $consumerId,
                $privateKey,
                $account
            );

            if ($account->getChildObject()->getIdentifier() === '') {
                $account->getChildObject()->addData(
                    array(
                        Ess_M2ePro_Model_Resource_Walmart_Account::COLUMN_IDENTIFIER => $responseData['identifier']
                    )
                );
            }

            $account->getChildObject()->addData(
                array(
                    Ess_M2ePro_Model_Resource_Walmart_Account::COLUMN_INFO => Mage::helper('M2ePro')->jsonEncode($responseData['info'])
                )
            );

            $account->getChildObject()->save();
        } catch (Exception $exception) {
            throw $exception;
        }

        return $account;
    }

    /**
     * @param $consumerId
     * @param $privateKey
     * @param Ess_M2ePro_Model_Account $account
     * @return array
     */
    private function updateOnServer(
        $consumerId,
        $privateKey,
        $account
    ) {
        $params = array();
        $params['consumer_id'] = $consumerId;
        $params['private_key'] = $privateKey;
        $params['marketplace_id'] = $account->getChildObject()->getMarketplaceId();

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