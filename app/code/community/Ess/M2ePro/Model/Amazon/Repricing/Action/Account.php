<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Repricing_Action_Account extends Ess_M2ePro_Model_Amazon_Repricing_Abstract
{
    //########################################

    public function sendLinkActionData($backUrl)
    {
        $accountData = array(
            'merchant_id'      => $this->getAmazonAccount()->getMerchantId(),
            'marketplace_code' => $this->getAmazonAccount()->getMarketplace()->getCode(),
            'additional_data'  => Mage::helper('M2ePro/Module_License')->getUserInfo(),
        );

        return $this->sendData(
            Ess_M2ePro_Helper_Component_Amazon_Repricing::COMMAND_ACCOUNT_LINK,
            array('account' => $accountData),
            $backUrl
        );
    }

    public function sendUnlinkActionData($backUrl)
    {
        $skus = Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->getAllSkus($this->getAccount());

        $offers  = array();
        foreach ($skus as $sku) {
            $offers[] = array('sku' => $sku);
        }

        return $this->sendData(
            Ess_M2ePro_Helper_Component_Amazon_Repricing::COMMAND_ACCOUNT_UNLINK,
            array('offers' => $offers),
            $backUrl
        );
    }

    //########################################

    private function sendData($command, array $data, $backUrl)
    {
        $requestData = array(
            'request' => array(
                'back_url' => array(
                    'url'    => $backUrl,
                    'params' => array()
                )
            ),
            'data' => json_encode($data),
        );

        if ($this->getAmazonAccount()->isRepricing()) {
            $requestData['request']['auth'] = array(
                'account_token' => $this->getAmazonAccountRepricing()->getToken()
            );
        }

        try {
            $result = $this->getHelper()->sendRequest($command, $requestData);
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return false;
        }

        $response = json_decode($result['response'], true);

        return !empty($response['request_token']) ? $response['request_token'] : false;
    }

    //########################################
}