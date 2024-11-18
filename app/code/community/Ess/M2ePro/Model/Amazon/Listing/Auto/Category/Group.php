<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing_Auto_Category_Group getParentObject()
 */
class Ess_M2ePro_Model_Amazon_Listing_Auto_Category_Group extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Listing_Auto_Category_Group');
    }

    //########################################
    public function getAddingProductTypeTemplateId()
    {
        return $this->getData('adding_product_type_template_id');
    }

    //########################################
}
