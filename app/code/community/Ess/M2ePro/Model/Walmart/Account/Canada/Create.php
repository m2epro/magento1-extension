<?php

class Ess_M2ePro_Model_Walmart_Account_Canada_Create
{
    /**
     * @param $marketplaceId
     * @param $consumerId
     * @param $privateKey
     * @param $title
     * @return Ess_M2ePro_Model_Account
     * @throws Throwable
     */
    public function createAccount(
        $marketplaceId,
        $consumerId,
        $privateKey,
        $title
    ) {
        /** @var $account Ess_M2ePro_Model_Account */
        $account = Mage::helper('M2ePro/Component_Walmart')->getModel('Account');
        $accountData = Mage::getModel('M2ePro/Walmart_Account_Builder')->getDefaultData();

        $accountData[Ess_M2ePro_Model_Resource_Walmart_Account::COLUMN_MARKETPLACE_ID] = $marketplaceId;
        $accountData['title'] = $title;

        Mage::getModel('M2ePro/Walmart_Account_Builder')->build($account, $accountData);

        try {
            $responseData = $this->createOnServer(
                $consumerId,
                $privateKey,
                $marketplaceId,
                $account
            );

            if ($this->isAccountExistsByIdentifier($responseData['identifier'])) {
                throw new Ess_M2ePro_Model_Exception(
                    'An account with the same details has already been added. Please make sure you provide unique information.'
                );
            }

            $account->getChildObject()->addData(
                array(
                    Ess_M2ePro_Model_Resource_Walmart_Account::COLUMN_SERVER_HASH => $responseData['hash'],
                    Ess_M2ePro_Model_Resource_Walmart_Account::COLUMN_IDENTIFIER => $responseData['identifier'],
                    Ess_M2ePro_Model_Resource_Walmart_Account::COLUMN_INFO => Mage::helper('M2ePro')->jsonEncode($responseData['info'])
                )
            );

            $account->getChildObject()->save();
        } catch (Exception $exception) {
            $account->delete();

            throw $exception;
        }

        return $account;
    }

    /**
     * @param $consumerId
     * @param $privateKey
     * @param $marketplaceId
     * @param Ess_M2ePro_Model_Account $account
     * @return array
     */
    private function createOnServer(
        $consumerId,
        $privateKey,
        $marketplaceId,
        $account
    ) {
        $params = array();
        $params['consumer_id'] = $consumerId;
        $params['private_key'] = $privateKey;
        $params['marketplace_id'] = $marketplaceId;

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