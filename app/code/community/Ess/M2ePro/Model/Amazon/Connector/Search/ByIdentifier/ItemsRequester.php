<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Connector_Search_ByIdentifier_ItemsRequester
    extends Ess_M2ePro_Model_Amazon_Connector_Command_Pending_Requester
{
    //########################################

    public function getCommand()
    {
        return array('product','search','byIdentifier');
    }

    //########################################

    abstract protected function getQuery();

    abstract protected function getQueryType();

    abstract protected function getVariationBadParentModifyChildToSimple();

    //########################################

    public function getRequestData()
    {
        return array(
            'item' => $this->getQuery(),
            'id_type' => $this->getQueryType(),
            'variation_bad_parent_modify_child_to_simple' => $this->getVariationBadParentModifyChildToSimple()
        );
    }

    //########################################
}
