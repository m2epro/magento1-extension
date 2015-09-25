<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Listing_Auto_Category_Group
    extends Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Listing_Auto_Category_Group', 'id');
    }

    // ########################################

    public function getCategoriesFromOtherGroups($listingId, $groupId = NULL)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Auto_Category_Group_Collection $groupCollection */
        $groupCollection = Mage::getModel('M2ePro/Listing_Auto_Category_Group')->getCollection();
        $groupCollection->addFieldToFilter('main_table.listing_id', (int)$listingId);

        if ($groupId) {
            $groupCollection->addFieldToFilter('main_table.id', array('neq' => (int)$groupId));
        }

        $groupIds = $groupCollection->getAllIds();
        if(count($groupIds) == 0) {
            return array();
        }

        $collection = Mage::getModel('M2ePro/Listing_Auto_Category')->getCollection();
        $collection->getSelect()->joinInner(
            array('melacg' => $this->getMainTable()),
            'main_table.group_id = melacg.id',
            array('group_title' => 'title')
        );
        $collection->getSelect()->where('main_table.group_id IN ('.implode(',',$groupIds).')');

        $data = array();

        foreach ($collection as $item) {
            $data[$item->getData('category_id')] = array(
                'id' => $item->getData('group_id'),
                'title' => $item->getData('group_title')
            );
        }

        return $data;
    }

    // ########################################

    public function isEmpty($groupId)
    {
        $select = $this->_getReadAdapter()
            ->select()
            ->from(
                array('mlac' => Mage::getResourceModel('M2ePro/Listing_Auto_Category')->getMainTable())
            )
            ->where('mlac.group_id = ?', $groupId);
        $result = $this->_getReadAdapter()->fetchAll($select);

        return count($result) === 0;
    }

    // ########################################
}