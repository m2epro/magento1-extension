<?php

class Ess_M2ePro_Model_Walmart_Connector_Account_GetGrantAccessUrl_Response
{
    private $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }
}