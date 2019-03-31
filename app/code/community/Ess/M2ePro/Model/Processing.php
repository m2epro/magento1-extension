<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Processing extends Ess_M2ePro_Model_Abstract
{
    const TYPE_SINGLE = 1;
    const TYPE_PARTIAL = 2;

    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Processing');
    }

    //####################################

    public function getModel()
    {
        return $this->getData('model');
    }

    public function getParams()
    {
        return $this->getSettings('params');
    }

    //------------------------------------

    public function getType()
    {
        return $this->getData('type');
    }

    public function isTypeSingle()
    {
        return $this->getType() == self::TYPE_SINGLE;
    }

    public function isTypePartial()
    {
        return $this->getType() == self::TYPE_PARTIAL;
    }

    //------------------------------------

    public function getResultData()
    {
        return $this->getSettings('result_data');
    }

    public function getResultMessages()
    {
        return $this->getSettings('result_messages');
    }

    public function isCompleted()
    {
        return (bool)$this->getData('is_completed');
    }

    //####################################

    public function forceRemove()
    {
        $table = Mage::getResourceModel('M2ePro/Processing_Lock')->getMainTable();
        Mage::getSingleton('core/resource')->getConnection('core_write')->delete(
            $table, array('`processing_id` = ?' => (int)$this->getId())
        );

        $table = Mage::getResourceModel('M2ePro/Processing')->getMainTable();
        Mage::getSingleton('core/resource')->getConnection('core_write')->delete(
            $table, array('`id` = ?' => (int)$this->getId())
        );
    }

    //####################################
}