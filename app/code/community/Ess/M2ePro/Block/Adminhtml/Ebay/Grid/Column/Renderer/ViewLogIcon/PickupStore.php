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
            Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_UNKNOWN,
            Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_ADD_PRODUCT,
            Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_UPDATE_QTY,
            Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_DELETE_PRODUCT
        );
    }

    //########################################

    public function render(Varien_Object $row)
    {
        if (!parent::render($row)) {
            return parent::render($row);
        }

        $pickupStoreState = Mage::getModel('M2ePro/Ebay_Account_PickupStore_State')
            ->load($row->getData('state_id'));
        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
                'Log For Sku' => Mage::helper('M2ePro')->__('Log For Sku (%s%)', $pickupStoreState->getSku())
            )
        );

        $html = "<script>M2ePro.translator.add({$translations});</script>";

        return $html . parent::render($row);
    }

    //########################################

    protected function getLastMessages(Varien_Object $row)
    {
        $dbSelect = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(
                Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore_Log')->getMainTable(),
                array('id', 'action_id','action','type','description','create_date')
            )
            ->where('`account_pickup_store_state_id` = ?', (int)$row->getData('state_id'))
            ->where('`action_id` IS NOT NULL')
            ->where('`action` IN (?)', $this->getAvailableActions())
            ->order(array('id DESC'))
            ->limit(30);

        return Mage::getSingleton('core/resource')->getConnection('core_read')
            ->fetchAll($dbSelect);
    }

    //########################################

    protected function getInitiatorForAction($actionRows)
    {
        return Mage::helper('M2ePro')->__('Automatic');
    }

    //########################################
}
