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
        return array_merge(
            parent::getAvailableActions(),
            array(
                Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT,
                Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_AND_REMOVE_PRODUCT,
                Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_LISTING,
                Ess_M2ePro_Model_Listing_Log::ACTION_RESET_BLOCKED_PRODUCT
            )
        );
    }

    //########################################

    protected function getGroupedLogRecords(Varien_Object $row)
    {
        $actionsRows = parent::getGroupedLogRecords($row);

        if (!(bool)(int)$row->getData('is_variation_parent')) {
            return $actionsRows;
        }

        foreach ($actionsRows as &$actionsRow) {
            if (!empty($actionsRow['items']) &&
                $actionsRow['items'][0]['listing_product_id'] == (int)$row->getData('id')) {
                continue;
            }

            $actionsRow['action_in_progress'] = $this->isActionInProgress($actionsRow['action_id']);

            $descArr = array();
            foreach ($actionsRow['items'] as $key => &$item) {
                if (array_key_exists($item['description'], $descArr)) {
                    $descArr[$item['description']]['count']++;
                    unset($actionsRow['items'][$key]);
                    continue;
                }

                $item['count'] = 1;
                $descArr[$item['description']] = $item;
            }

            $actionsRow['items'] = array_values($descArr);
        }

        return $actionsRows;
    }

    //########################################

    public function isActionInProgress($actionId)
    {
        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Processing')->getCollection();
        $collection->addFieldToFilter('params', array('regexp' => "\"logs_action_key\":{$actionId}"));

        return $collection->getSize() > 0;
    }

    //########################################
}
