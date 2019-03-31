<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module_Support_Search extends Mage_Core_Helper_Abstract
{
    //########################################

    public function process($query)
    {
        if (empty($query)) {
            return array();
        }

        $params = array(
            'query' => strip_tags($query),
            'count' => 10
        );

        $results = array();
        $response = $this->sendRequestAsGet($params);

        if ($response !== false) {
            $results = (array)Mage::helper('M2ePro')->jsonDecode($response);
        }

        return $results;
    }

    //########################################

    private function sendRequestAsGet($params)
    {
        $curlObject = curl_init();

        $url = Mage::helper('M2ePro/Module_Support')->getMainSupportUrl('extension/search');
        $url = $url . '?'.http_build_query($params,'','&');
        curl_setopt($curlObject, CURLOPT_URL, $url);

        curl_setopt($curlObject, CURLOPT_FOLLOWLOCATION, true);

        // stop CURL from verifying the peer's certificate
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);

        // disable http headers
        curl_setopt($curlObject, CURLOPT_HEADER, false);
        curl_setopt($curlObject, CURLOPT_POST, false);

        // set it to return the transfer as a string from curl_exec
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObject, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curlObject, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($curlObject);
        curl_close($curlObject);

        return $response;
    }

    //########################################
}