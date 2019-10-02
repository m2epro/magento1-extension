<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
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

        $filterGroupRelation = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_motor_filter_to_group');

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
        return in_array(
            $this->getType(), array(
            Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID_MOTOR,
            Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID_UK,
            Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID_DE,
            )
        );
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
