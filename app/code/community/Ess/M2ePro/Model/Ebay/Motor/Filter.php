<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Motor_Filter extends Ess_M2ePro_Model_Component_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Motor_Filter');
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

        $connWrite->delete($filterGroupRelation, array('filter_id = ?' => $this->getId()));

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

    public function getConditions($asObject = true)
    {
        if ($asObject) {
            return $this->getSettings('conditions');
        }
        return $this->getData('conditions');
    }

    //########################################

    public function getNote()
    {
        return $this->getData('note');
    }

    //########################################
}
