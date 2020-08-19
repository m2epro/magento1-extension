<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Listing_Log as Log;

class Ess_M2ePro_Block_Adminhtml_Amazon_Grid_Column_Renderer_ViewLogIcon_Listing
    extends Ess_M2ePro_Block_Adminhtml_Grid_Column_Renderer_ViewLogIcon_Listing
{
    //########################################

    protected function getAvailableActions()
    {
        return parent::getAvailableActions() +
            array(
                Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT => $this->__('Remove from Channel'),
                Log::ACTION_DELETE_AND_REMOVE_PRODUCT     => $this->__('Remove from Channel & Listing'),
                Log::ACTION_SWITCH_TO_AFN_ON_COMPONENT    => $this->__('Switch to AFN'),
                Log::ACTION_SWITCH_TO_MFN_ON_COMPONENT    => $this->__('Switch to MFN')
            );
    }

    //########################################

    protected function getLastActions($listingProductId, $logs)
    {
        $lastActions = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_log_grid_lastActions')
            ->setData(
                array(
                    'entity_id'           => $listingProductId,
                    'logs'                => $logs,
                    'available_actions'   => $this->getAvailableActions(),
                    'is_variation_parent' => $this->isVariationParent(),
                    'view_help_handler'   => "{$this->getJsHandler()}.viewItemHelp",
                    'hide_help_handler'   => "{$this->getJsHandler()}.hideItemHelp"
                )
            );

        return $lastActions->toHtml();
    }

    //########################################
}
