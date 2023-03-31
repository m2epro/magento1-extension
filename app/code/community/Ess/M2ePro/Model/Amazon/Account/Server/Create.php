<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Account_Server_Create
{
    /**
     * @param string $oAuthCode
     * @param string $merchantId
     * @param int    $marketplaceId
     *
     * @return Ess_M2ePro_Model_Amazon_Account_Server_Create_Result
     */
    public function process($oAuthCode, $merchantId, $marketplaceId)
    {
        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
            'Marketplace',
            $marketplaceId
        );

        /** @var Ess_M2ePro_Model_Amazon_Connector_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
        /** @var Ess_M2ePro_Model_Amazon_Connector_Account_Add_EntityRequester $connectorObj */
        $connectorObj = $dispatcher->getConnector(
            'account',
            'add',
            'entityRequester',
            array(
                'marketplace_id' => $marketplace->getNativeId(),
                'merchant_id' => $merchantId,
                'oauth_code' => $oAuthCode,
            )
        );

        $dispatcher->process($connectorObj);

        $responseData = $connectorObj->getResponseData();

        return new Ess_M2ePro_Model_Amazon_Account_Server_Create_Result(
            $responseData['hash'],
            $responseData['info']
        );
    }
}
