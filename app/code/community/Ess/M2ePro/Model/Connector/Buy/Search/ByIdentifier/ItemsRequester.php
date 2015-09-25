<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Buy_Search_ByIdentifier_ItemsRequester
    extends Ess_M2ePro_Model_Connector_Buy_Requester
{
    const SEARCH_TYPE_UPC        = 'UPC';
    const SEARCH_TYPE_GENERAL_ID = 'SKU';

    // ########################################

    public function getCommand()
    {
        return array('product','search','byIdentifier');
    }

    // ########################################

    abstract protected function getQuery();

    abstract protected function getSearchType();

    // ########################################

    protected function getRequestData()
    {
        return array(
            'query' => $this->getQuery(),
            'search_type' => $this->getSearchType(),
        );
    }

    // ########################################
}