<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Grid as ListingGrid;

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Grid_Column_Renderer_Action
    extends Ess_M2ePro_Block_Adminhtml_Grid_Column_Renderer_Action
{
    //########################################

    protected function _toOptionHtml($action, Varien_Object $row)
    {
        $marketplace = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Marketplace', $row->getData('marketplace_id')
        );

        if (!$marketplace->getChildObject()->isMultiMotorsEnabled() &&
            isset($action['action_id']) &&
            $action['action_id'] == ListingGrid::MASS_ACTION_ID_EDIT_PARTS_COMPATIBILITY)
        {
            return '';
        }

        return parent::_toOptionHtml($action, $row);
    }

    //########################################
}