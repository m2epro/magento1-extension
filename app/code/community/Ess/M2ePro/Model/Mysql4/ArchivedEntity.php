<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_ArchivedEntity extends Ess_M2ePro_Model_Mysql4_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/ArchivedEntity', 'id');
    }

    //########################################

    public function retrieve($name, $originId)
    {
        /** @var Mage_Core_Model_Mysql4_Collection_Abstract $collection */
        $collection = Mage::getModel('M2ePro/ArchivedEntity')->getCollection();
        $collection->addFieldToFilter('name', $name)
                   ->addFieldToFilter('origin_id', (int)$originId)
                   ->setOrder($collection->getResource()->getIdFieldName(), Varien_Data_Collection::SORT_ORDER_DESC);

        $collection->getSelect()->limit(1);
        $entity = $collection->getFirstItem();

        return $entity->getId() ? $entity : null;
    }

    //########################################
}