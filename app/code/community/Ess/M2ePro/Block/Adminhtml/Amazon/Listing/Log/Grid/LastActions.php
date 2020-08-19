<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Log_Grid_LastActions extends
    Ess_M2ePro_Block_Adminhtml_Listing_Log_Grid_LastActions
{
    //########################################

    protected function getGroupedActions(array $logs)
    {
        $actions = parent::getGroupedActions($logs);

        if (!$this->isVariationParent()) {
            return $actions;
        }

        foreach ($actions as &$actionsRow) {
            if (empty($actionsRow['items'])) {
                continue;
            }

            $firstItem = reset($actionsRow['items']);

            if ($firstItem['listing_product_id'] == $this->getEntityId()) {
                continue;
            }

            $actionsRow['action_in_progress'] = $this->isActionInProgress($firstItem['action_id']);

            $descArr = array();
            foreach ($actionsRow['items'] as $key => &$item) {
                if (array_key_exists((string)$item['description'], $descArr)) {
                    $descArr[(string)$item['description']]['count']++;
                    unset($actionsRow['items'][$key]);
                    continue;
                }

                $item['count'] = 1;
                $descArr[(string)$item['description']] = $item;
            }

            $actionsRow['items'] = array_values($descArr);
        }

        return $actions;
    }

    protected function isVariationParent()
    {
        if (!$this->hasData('is_variation_parent')) {
            return false;
        }

        return $this->getData('is_variation_parent');
    }

    protected function isActionInProgress($actionId)
    {
        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Processing')->getCollection();
        $collection->addFieldToFilter('params', array('regexp' => "\"logs_action_key\":{$actionId}"));

        return $collection->getSize() > 0;
    }

    //########################################
}
