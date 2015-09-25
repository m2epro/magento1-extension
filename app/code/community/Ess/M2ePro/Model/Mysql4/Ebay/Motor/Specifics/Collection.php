<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Motor_Specifics_Collection
    extends Varien_Data_Collection_Db
{
    // ########################################

    public function __construct($idFieldName = NULL)
    {
        $connRead = Mage::getResourceModel('core/config')->getReadConnection();

        parent::__construct($connRead);

        if (!is_null($idFieldName)) {
            $this->_idFieldName = $idFieldName;
        }

        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_motor_specific');

        $this->getSelect()->reset()->from(
            array('main_table' => $table)
        );
    }

    // ########################################

    public function getAllIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);

        $idsSelect->columns($this->_idFieldName, 'main_table');
        $idsSelect->limit(1000);

        return $this->getConnection()->fetchCol($idsSelect);
    }

    // ########################################
}