<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Grid_Column_Renderer_ViewLogIcon_Listing
    extends Ess_M2ePro_Block_Adminhtml_Grid_Column_Renderer_ViewLogIcon_Listing
{
    //########################################

    protected function getAvailableActions()
    {
        return parent::getAvailableActions() +
            array(
                Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT,
                Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_AND_REMOVE_PRODUCT,
                Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_LISTING,
                Ess_M2ePro_Model_Listing_Log::ACTION_RESET_BLOCKED_PRODUCT
            );
    }

    //########################################

    protected function getLastActions($listingProductId, $logs)
    {
        $lastActions = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_log_grid_lastActions')
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
