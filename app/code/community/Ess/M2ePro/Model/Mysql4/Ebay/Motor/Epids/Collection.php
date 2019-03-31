<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Motor_Epids_Collection
    extends Varien_Data_Collection_Db
{
    protected $scope;

    //########################################

    public function __construct($idFieldName = NULL, $scope)
    {
        $connRead = Mage::getResourceModel('core/config')->getReadConnection();

        parent::__construct($connRead);

        if (!is_null($idFieldName)) {
            $this->_idFieldName = $idFieldName;
        }
        $this->scope = $scope;

        $table = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_dictionary_motor_epid');

        $this->getSelect()->reset()->from(
            array('main_table' => $table)
        );
        $this->getSelect()->where('scope = ?', $scope);
    }

    //########################################

    public function getAllIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);

        $idsSelect->columns($this->_idFieldName, 'main_table');
        $idsSelect->limit(Ess_M2ePro_Helper_Component_Ebay_Motors::MAX_ITEMS_COUNT_FOR_ATTRIBUTE);

        return $this->getConnection()->fetchCol($idsSelect);
    }

    //########################################
}