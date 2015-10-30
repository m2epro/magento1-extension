<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module_Support_Uservoice extends Mage_Core_Helper_Abstract
{
    //########################################

    public function search($query)
    {
        if (empty($query)) {
            return array();
        }

        $params = array(
            'client' => $this->getApiClientKey(),
            'query' => strip_tags($query),
            'page' => 1,
            'per_page' => 10
        );

        $articles = array();
        $articlesAction = 'articles/search.json';
        $articlesResponse = $this->sendRequestAsGet($this->getApiUrl(), $articlesAction, $params);
        if ($articlesResponse !== false) {
            $articles = json_decode($articlesResponse, true);
        }

        $suggestions = array();
        $suggestionsAction = 'suggestions/search.json';
        $suggestionsResponse = $this->sendRequestAsGet($this->getApiUrl(), $suggestionsAction, $params);
        if ($suggestionsResponse !== false) {
            $suggestions = json_decode($suggestionsResponse, true);
        }

        return array_merge($articles, $suggestions);
    }

    //########################################

    private function getApiUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/uservoice/', 'api_url');
    }

    private function getApiClientKey()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/uservoice/', 'api_client_key');
    }

    // ---------------------------------------

    private function sendRequestAsGet($baseUrl, $action, $params)
    {
        $curlObject = curl_init();

        // set the server we are using
        curl_setopt($curlObject, CURLOPT_URL, $baseUrl . $action . '?'.http_build_query($params,'','&'));

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