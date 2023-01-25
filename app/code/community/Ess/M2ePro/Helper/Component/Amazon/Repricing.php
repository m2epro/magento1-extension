<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Amazon_Repricing extends Mage_Core_Helper_Abstract
{
    const COMMAND_ACCOUNT_LINK             = 'account/link';
    const COMMAND_ACCOUNT_UNLINK           = 'account/unlink';
    const COMMAND_SYNCHRONIZE              = 'synchronize';
    const COMMAND_SYNCHRONIZE_USER_CHANGES = 'synchronize/userChanges';
    const COMMAND_GOTO_SERVICE             = 'goto_service';

    const REQUEST_TIMEOUT = 300;

    public function sendRequest($command, array $postData)
    {
        $curlObject = curl_init();

        $url = $this->getBaseUrl().$command;

        //set the url
        curl_setopt($curlObject, CURLOPT_URL, $url);

        $sslVerifyPeer = true;
        $sslVerifyHost = 2;

        if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $url)) {
            $sslVerifyPeer = false;
            $sslVerifyHost = false;
        }

        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, $sslVerifyPeer);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, $sslVerifyHost);

        // set the data body of the request
        curl_setopt($curlObject, CURLOPT_POST, true);
        curl_setopt($curlObject, CURLOPT_POSTFIELDS, http_build_query($postData, '', '&'));

        // set it to return the transfer as a string from curl_exec
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObject, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curlObject, CURLOPT_TIMEOUT, self::REQUEST_TIMEOUT);

        curl_setopt($curlObject, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlObject, CURLOPT_POSTREDIR, 1);

        $response = curl_exec($curlObject);

        $curlInfo    = curl_getinfo($curlObject);
        $errorNumber = curl_errno($curlObject);

        curl_close($curlObject);

        if ($response === false) {
            throw new Ess_M2ePro_Model_Exception_Connection(
                Mage::helper('M2ePro')->__(
                    'M2E Pro Server connection failed. Find the solution <a target="_blank" href="%url%">here</a>',
                    Mage::helper('M2ePro/Module_Support')->getSupportUrl('/support/solutions/articles/9000200887')
                ),
                array(
                    'curl_error_number' => $errorNumber,
                    'curl_info'         => $curlInfo
                )
            );
        }

        $responseDecoded = Mage::helper('M2ePro')->jsonDecode($response);
        if (!$responseDecoded || !is_array($responseDecoded)) {
            throw new Ess_M2ePro_Model_Exception_Connection(
                'The Action was not completed because server responded with an incorrect response.',
                array(
                    'raw_response' => $response,
                    'curl_info'    => $curlInfo
                )
            );
        }

        return array(
            'curl_error_number' => $errorNumber,
            'curl_info'         => $curlInfo,
            'response'          => $responseDecoded
        );
    }

    public function getBaseUrl()
    {
        $baseUrl = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/amazon/repricing/', 'base_url');
        return rtrim($baseUrl, '/').'/';
    }

    public function prepareActionUrl($command, $serverRequestToken)
    {
        return $this->getBaseUrl().$command.'?'.http_build_query(array('request_token' => $serverRequestToken));
    }

    public function getManagementUrl(Ess_M2ePro_Model_Account $account)
    {
        /** @var Ess_M2ePro_Model_Amazon_Account $amazonAccount */
        $amazonAccount = $account->getChildObject();
        if (!$amazonAccount->isRepricing()) {
            return false;
        }

        return $this->getBaseUrl().self::COMMAND_GOTO_SERVICE.'?'.http_build_query(
                array(
                    'account_token' => $amazonAccount->getRepricing()->getToken()
                )
            );
    }
}
