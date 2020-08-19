<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Listing_Log as Log;

class Ess_M2ePro_Block_Adminhtml_Grid_Column_Renderer_ViewLogIcon_Listing
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    //########################################

    protected function getAvailableActions()
    {
        return array(
            Log::ACTION_LIST_PRODUCT_ON_COMPONENT   => $this->__('List'),
            Log::ACTION_RELIST_PRODUCT_ON_COMPONENT => $this->__('Relist'),
            Log::ACTION_REVISE_PRODUCT_ON_COMPONENT => $this->__('Revise'),
            Log::ACTION_STOP_PRODUCT_ON_COMPONENT   => $this->__('Stop'),
            Log::ACTION_STOP_AND_REMOVE_PRODUCT     => $this->__('Stop on Channel / Remove from Listing'),
            Log::ACTION_CHANNEL_CHANGE              => $this->__('Channel Change')
        );
    }

    //########################################

    public function render(Varien_Object $row)
    {
        $listingProductId  = (int)$row->getData('id');
        $availableActionsId = array_keys($this->getAvailableActions());

        $dbSelect = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(
                Mage::getResourceModel('M2ePro/Listing_Log')->getMainTable(),
                array('action_id', 'action', 'type', 'description', 'create_date', 'initiator', 'listing_product_id')
            )
            ->where('`action_id` IS NOT NULL')
            ->where('`action` IN (?)', $availableActionsId)
            ->order(array('id DESC'))
            ->limit(Ess_M2ePro_Block_Adminhtml_Log_Grid_LastActions::PRODUCTS_LIMIT);

        if ($this->isVariationParent()) {
            $dbSelect->where('`listing_product_id` = ? OR `parent_listing_product_id` = ?', $listingProductId);
        } else {
            $dbSelect->where('`listing_product_id` = ?', $listingProductId);
        }

        $logs = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($dbSelect);

        if (empty($logs)) {
            return '';
        }

        return $this->getLastActions($listingProductId, $logs);
    }

    //########################################

    protected function getLastActions($listingProductId, $logs)
    {
        $lastActions = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_log_grid_lastActions')
            ->setData(
                array(
                    'entity_id'           => $listingProductId,
                    'logs'                => $logs,
                    'available_actions'   => $this->getAvailableActions(),
                    'view_help_handler'   => "{$this->getJsHandler()}.viewItemHelp",
                    'hide_help_handler'   => "{$this->getJsHandler()}.hideItemHelp"
                )
            );

        return $lastActions->toHtml();
    }

    //########################################

    protected function getJsHandler()
    {
        if ($this->hasData('jsHandler')) {
            return $this->getData('jsHandler');
        }

        return 'ListingGridObj';
    }

    protected function isVariationParent()
    {
        if ($this->hasData('is_variation_parent')) {
            return $this->getData('is_variation_parent');
        }

        return false;
    }

    //########################################
}
