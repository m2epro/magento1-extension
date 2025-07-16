<?php

class Ess_M2ePro_Model_Walmart_Account_UnitedStates_Create
{
    /**
     * @param $authCode
     * @param $marketplaceId
     * @param $sellerId
     * @param $clientId
     * @return Ess_M2ePro_Model_Account
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function createAccount(
        $authCode,
        $marketplaceId,
        $sellerId,
        $clientId = null
    ) {
        /** @var $account Ess_M2ePro_Model_Account */
        $account = Mage::helper('M2ePro/Component_Walmart')->getModel('Account');

        $responseData = $this->createOnServer(
            $authCode,
            $marketplaceId,
            $sellerId,
            $clientId,
            $account
        );

        if ($this->isAccountExistsByIdentifier($responseData['identifier'])) {
            throw new Ess_M2ePro_Model_Exception(
                'An account with the same details has already been added. Please make sure you provide unique information.'
            );
        }

        $accountData = Mage::getModel('M2ePro/Walmart_Account_Builder')->getDefaultData();
        $accountData[Ess_M2ePro_Model_Resource_Walmart_Account::COLUMN_MARKETPLACE_ID] = $marketplaceId;
        $accountData['title'] = $responseData['identifier'];

        Mage::getModel('M2ePro/Walmart_Account_Builder')->build($account, $accountData);

        $account->getChildObject()->addData(
            array(
                Ess_M2ePro_Model_Resource_Walmart_Account::COLUMN_SERVER_HASH => $responseData['hash'],
                Ess_M2ePro_Model_Resource_Walmart_Account::COLUMN_IDENTIFIER => $responseData['identifier']
            )
        );

        $account->getChildObject()->save();

        return $account;
    }

    /**
     * @param $authCode
     * @param $marketplaceId
     * @param $sellerId
     * @param $clientId
     * @param Ess_M2ePro_Model_Account $account
     * @return array
     */
    private function createOnServer(
        $authCode,
        $marketplaceId,
        $sellerId,
        $clientId,
        $account
    ) {
        $params = array();
        $params['marketplace_id'] = $marketplaceId;
        $params['seller_id'] = $sellerId;
        $params['auth_code'] = $authCode;
        $params['client_id'] = $clientId;

        /** @var $dispatcher Ess_M2ePro_Model_Walmart_Connector_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');

        /** @var Ess_M2ePro_Model_Walmart_Connector_Account_Add_EntityRequester $connectorObj */
        $connectorObj = $dispatcher->getConnectorByClass(
            'Ess_M2ePro_Model_Walmart_Connector_Account_Add_EntityRequester',
            $params,
            $account
        );

        $dispatcher->process($connectorObj);

        return $connectorObj->getResponseData();
    }

    /**
     * @param $identifier
     * @return bool
     */
    private function isAccountExistsByIdentifier($identifier)
    {
        return Mage::getModel('M2ePro/Walmart_Account_Repository')->isAccountExistsByIdentifier($identifier);
    }
}