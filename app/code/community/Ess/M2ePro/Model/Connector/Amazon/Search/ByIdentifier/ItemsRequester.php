<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Amazon_Search_ByIdentifier_ItemsRequester
    extends Ess_M2ePro_Model_Connector_Amazon_Requester
{
    //########################################

    /**
     * @return array
     */
    public function getCommand()
    {
        return array('product','search','byIdentifier');
    }

    //########################################

    abstract protected function getQuery();

    abstract protected function getQueryType();

    abstract protected function getVariationBadParentModifyChildToSimple();

    //########################################

    protected function getRequestData()
    {
        return array(
            'item' => $this->getQuery(),
            'id_type' => $this->getQueryType(),
            'variation_bad_parent_modify_child_to_simple' => $this->getVariationBadParentModifyChildToSimple()
        );
    }

    //########################################
}