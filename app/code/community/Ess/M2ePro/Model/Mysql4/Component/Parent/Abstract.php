<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract
    extends Ess_M2ePro_Model_Mysql4_Component_Abstract
{
    protected $childMode = NULL;

    // ########################################

    public function __construct($params)
    {
        if (isset($params['child_mode'])) {
            $this->setChildMode($params['child_mode']);
        }

        parent::__construct($params);
    }

    // ########################################

    public function setChildMode($mode)
    {
        $mode = strtolower((string)$mode);
        $mode && $this->childMode = $mode;
        return $this;
    }

    public function getChildMode()
    {
        return $this->childMode;
    }

    //-----------------------------------------

    public function getChildModel()
    {
        if (is_null($this->childMode)) {
            return NULL;
        }

        return 'M2ePro/'.ucwords($this->childMode).'_'.$this->_mainTable;
    }

    public function getChildTable()
    {
        if (is_null($this->childMode)) {
            return NULL;
        }

        return str_replace('m2epro_','m2epro_'.$this->childMode.'_',$this->getMainTable());
    }

    public function getChildPrimary()
    {
        if (is_null($this->childMode)) {
            return NULL;
        }

        $secondTable = $this->getChildTable();

        $primaryName = substr($secondTable,strpos($secondTable,'m2epro_'.$this->childMode.'_'));
        return substr($primaryName,strlen('m2epro_'.$this->childMode.'_')).'_id';
    }

    // ########################################

    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if (is_null($this->childMode)) {
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

        if (is_null($this->childMode)) {
            return $result;
        }

        $object->setChildMode($this->childMode);

        $modelName = ucwords($this->childMode).'_'.$this->_mainTable;
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

        if (is_null($this->childMode)) {
            return $result;
        }

        $childData = $object->getData();
        $childData[$this->getChildPrimary()] = (int)$object->getData('id');
        unset($childData['id'],$childData['create_date'],$childData['update_date']);

        Mage::getModel($this->getChildModel())
                ->addData($childData)
                ->save();

        return $result;
    }

    // ########################################
}