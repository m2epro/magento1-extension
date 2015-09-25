<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_Custom_ByQuery_Requester
    extends Ess_M2ePro_Model_Connector_Amazon_Search_ByQuery_ItemsRequester
{
    // ########################################

    protected function getQuery()
    {
        return $this->params['query'];
    }

    protected function getVariationBadParentModifyChildToSimple()
    {
        return $this->params['variation_bad_parent_modify_child_to_simple'];
    }

    // ########################################

    protected function getRequestData()
    {
        return array_merge(
            parent::getRequestData(),
            array('only_realtime' => true)
        );
    }

    // ########################################
}