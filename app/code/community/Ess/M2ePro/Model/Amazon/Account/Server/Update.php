<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Account_Server_Update
{
    /**
     * @param Ess_M2ePro_Model_Amazon_Account $account
     * @param string $oAuthCode
     * @param string $merchantId
     * @return void
     */
    public function process(Ess_M2ePro_Model_Amazon_Account $account, $oAuthCode, $merchantId)
    {
        /** @var Ess_M2ePro_Model_Amazon_Connector_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');

        /** @var Ess_M2ePro_Model_Amazon_Connector_Account_Update_EntityRequester $connectorObj */
        $connectorObj = $dispatcher->getConnector(
            'account',
            'update',
            'entityRequester',
            array(
                'account_server_hash' => $account->getServerHash(),
                'oauth_code' => $oAuthCode,
                'marketplace_id' => $account->getMarketplace()->getNativeId(),
                'merchant_id' => $merchantId,
            )
        );

        $dispatcher->process($connectorObj);

        $responseData = $connectorObj->getResponseData();

        $newInfo = $responseData['info'];
        if ($this->isNewInfoObtain($newInfo, $account->getDecodedInfo())) {
            $account->setInfo($newInfo);
            $account->save();
        }
    }

    // ----------------------------------------

    /**
     * @param array $newInfo
     * @param array $oldInfo
     *
     * @return bool
     */
    private function isNewInfoObtain($newInfo, $oldInfo)
    {
        return $newInfo !== $oldInfo;
    }
}
