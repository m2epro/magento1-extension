<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Amazon_Search_ByIdentifier_ItemsRequester
    extends Ess_M2ePro_Model_Connector_Amazon_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('product','search','byIdentifier');
    }

    // ########################################

    abstract protected function getQuery();

    abstract protected function getQueryType();

    abstract protected function getVariationBadParentModifyChildToSimple();

    // ########################################

    protected function getRequestData()
    {
        return array(
            'item' => $this->getQuery(),
            'id_type' => $this->getQueryType(),
            'variation_bad_parent_modify_child_to_simple' => $this->getVariationBadParentModifyChildToSimple()
        );
    }

    // ########################################
}