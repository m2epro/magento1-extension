<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Resource_Component_Parent_Abstract
    extends Ess_M2ePro_Model_Resource_Component_Abstract
{
    protected $_childMode = null;

    //########################################

    public function __construct($params)
    {
        if (isset($params['child_mode'])) {
            $this->setChildMode($params['child_mode']);
        }

        parent::__construct();
    }

    //########################################

    public function setChildMode($mode)
    {
        $mode = strtolower((string)$mode);
        $mode && $this->_childMode = $mode;
        return $this;
    }

    public function getChildMode()
    {
        return $this->_childMode;
    }

    // ---------------------------------------

    public function getChildModel()
    {
        if ($this->_childMode === null) {
            return null;
        }

        return 'M2ePro/'.ucwords($this->_childMode) . '_' . $this->_mainTable;
    }

    public function getChildTable()
    {
        if ($this->_childMode === null) {
            return null;
        }

        return str_replace('m2epro_', 'm2epro_'.$this->_childMode . '_', $this->getMainTable());
    }

    public function getChildPrimary()
    {
        if ($this->_childMode === null) {
            return null;
        }

        $secondTable = $this->getChildTable();

        $primaryName = substr($secondTable, strpos($secondTable, 'm2epro_'.$this->_childMode . '_'));
        return substr($primaryName, strlen('m2epro_'.$this->_childMode . '_')) . '_id';
    }

    //########################################

    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($this->_childMode === null) {
            return $select;
        }

        $childTable = $this->getChildTable();
        $select->join(
            $childTable,
            "`{$childTable}`.`".$this->getChildPrimary()."` = `".$this->getMainTable().'`.`id`'
        );

        return $select;
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $result = parent::_afterLoad($object);

        if ($this->_childMode === null) {
            return $result;
        }

        $object->setChildMode($this->_childMode);

        $modelName = ucwords($this->_childMode) . '_' . $this->_mainTable;
        $childObject = Mage::helper('M2ePro')->getModel($modelName);

        $childObject->addData($object->getData());
        $childObject->setOrigData();
        $childObject->setParentObject($object);

        $object->setChildObject($childObject);

        return $result;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $result = parent::_afterSave($object);

        if ($this->_childMode === null) {
            return $result;
        }

        $childData = $object->getData();
        $childData[$this->getChildPrimary()] = (int)$object->getData('id');
        unset($childData['id'], $childData['create_date'], $childData['update_date']);

        $childObject = Mage::getModel($this->getChildModel());
        $childObject->setParentObject($object);
        $childObject->addData($childData)
                    ->save();

        return $result;
    }

    //########################################
}
