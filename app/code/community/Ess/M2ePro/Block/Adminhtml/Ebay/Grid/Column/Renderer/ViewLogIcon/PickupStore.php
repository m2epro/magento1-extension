<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Renderer_ViewLogIcon_PickupStore
    extends Ess_M2ePro_Block_Adminhtml_Grid_Column_Renderer_ViewLogIcon_Listing
{
    //########################################

    protected function getAvailableActions()
    {
        return array(
            Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_UNKNOWN        => $this->__('Unknown'),
            Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_ADD_PRODUCT    => $this->__('Add'),
            Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_UPDATE_QTY     => $this->__('Update'),
            Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_DELETE_PRODUCT => $this->__('Delete'),
        );
    }

    //########################################

    public function render(Varien_Object $row)
    {
        $stateId = (int)$row->getData('state_id');
        $columnId = (int)$row->getData('id');
        $availableActionsId = array_keys($this->getAvailableActions());

        $dbSelect = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(
                Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore_Log')->getResource()->getMainTable(),
                array('id', 'action_id', 'action', 'type', 'description', 'create_date')
            )
            ->where('`account_pickup_store_state_id` = ?', $stateId)
            ->where('`action_id` IS NOT NULL')
            ->where('`action` IN (?)', $availableActionsId)
            ->order(array('id DESC'))
            ->limit(Ess_M2ePro_Block_Adminhtml_Log_Grid_LastActions::PRODUCTS_LIMIT);

        $logs = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($dbSelect);

        if (empty($logs)) {
            return '';
        }

        foreach ($logs as &$log) {
            $log['initiator'] = Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION;
        }

        $summary = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_log_grid_lastActions')
            ->setData(
                array(
                        'entity_id' => (int)$columnId,
                        'logs' => $logs,
                        'available_actions' => $this->getAvailableActions(),
                        'view_help_handler' => 'EbayListingPickupStoreGridObj.viewItemHelp',
                        'hide_help_handler' => 'EbayListingPickupStoreGridObj.hideItemHelp'
                    )
            );

        $pickupStoreState = Mage::getModel('M2ePro/Ebay_Account_PickupStore_State')->load($stateId);

        Mage::helper('M2ePro/View')->getJsTranslatorRenderer()->addTranslations(
            array(
                'Log For Sku' => Mage::helper('M2ePro')->__('Log For Sku (%s%)', $pickupStoreState->getSku())
            )
        );

        return $summary->toHtml();
    }

    //########################################
}
