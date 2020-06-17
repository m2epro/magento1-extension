<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Category_AffectedListingsProducts
    extends Ess_M2ePro_Model_Template_AffectedListingsProductsAbstract
{
    //########################################

    public function loadCollection(array $filters = array())
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->getSelect()->where(
            'template_category_id = ? OR template_category_secondary_id = ?',
            $this->_model->getId()
        );

        return $collection;
    }

    //########################################
}
