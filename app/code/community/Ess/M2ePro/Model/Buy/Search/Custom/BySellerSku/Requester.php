<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Search_Custom_BySellerSku_Requester
    extends Ess_M2ePro_Model_Connector_Buy_Search_BySellerSku_ItemsRequester
{
    // ########################################

    protected function getQuery()
    {
        return $this->params['query'];
    }

    // ########################################
}