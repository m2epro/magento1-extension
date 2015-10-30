<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Motor_Group extends Ess_M2ePro_Model_Component_Abstract
{
    const MODE_ITEM     = 1;
    const MODE_FILTER   = 2;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Motor_Group');
    }

    //########################################

    public function deleteInstance()
    {
        if (!parent::deleteInstance()) {
            return false;
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $coreResourceModel = Mage::getSingleton('core/resource');

        $filterGroupRelation = $coreResourceModel->getTableName('m2epro_ebay_motor_filter_to_group');

        $connWrite->delete($filterGroupRelation, array('group_id = ?' => $this->getId()));

        return true;
    }

    //########################################

    /**
     * @return int
     */
    public function getTitle()
    {
        return (int)$this->getData('title');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getMode()
    {
        return (int)$this->getData('mode');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isModeItem()
    {
        return $this->getMode() == self::MODE_ITEM;
    }

    /**
     * @return bool
     */
    public function isModeFilter()
    {
        return $this->getMode() == self::MODE_FILTER;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getType()
    {
        return (int)$this->getData('type');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isTypeEpid()
    {
        return $this->getType() == Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID;
    }

    /**
     * @return bool
     */
    public function isTypeKtype()
    {
        return $this->getType() == Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_KTYPE;
    }

    //########################################

    public function getItemsData()
    {
        return $this->getData('items_data');
    }

    //########################################

    public function getItems()
    {
        $data = Mage::helper('M2ePro/Component_Ebay_Motors')->parseAttributeValue($this->getItemsData());

        return $data['items'];
    }

    public function getFiltersIds()
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_motor_filter_to_group');

        $select = $connRead->select();
        $select->from(array('emftg' => $table), array('filter_id'))
            ->where('group_id IN (?)', $this->getId());

        return Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($select);
    }

    //########################################

    public function getNote()
    {
        return $this->getData('note');
    }

    //########################################
}
