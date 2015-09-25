<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Mysql4_Collection_Component_Parent_Abstract
    extends Ess_M2ePro_Model_Mysql4_Collection_Component_Abstract
{
    protected $childMode = NULL;

    // ########################################

    public function __construct($resource = NULL)
    {
        if (is_object($resource) && ($resource instanceof Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract)) {
            /** @var $resource Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract */
            $this->setChildMode($resource->getChildMode());
        }

        parent::__construct($resource);
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

    // ########################################

    protected function _initSelect()
    {
        $temp = parent::_initSelect();

        if (is_null($this->childMode)) {
            return $temp;
        }

        /** @var $resource Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract */
        $resource = $this->getResource();

        $componentTable = $resource->getChildTable();
        $componentPk = $resource->getChildPrimary();

        $this->getSelect()->join(
            array('second_table'=>$componentTable),
            "`second_table`.`".$componentPk."` = `main_table`.`id`"
        );
        $this->getSelect()->where("`main_table`.`component_mode` = '".$this->childMode."'");

        return $temp;
    }

    public function getFirstItem()
    {
        /** @var $item Ess_M2ePro_Model_Component_Parent_Abstract */
        $item = parent::getFirstItem();

        if (is_null($this->childMode)) {
            return $item;
        }

        if (is_null($item->getId())) {
            $item->setChildMode($this->childMode);
        }

        return $item;
    }

    public function getLastItem()
    {
        /** @var $item Ess_M2ePro_Model_Component_Parent_Abstract */
        $item = parent::getLastItem();

        if (is_null($this->childMode)) {
            return $item;
        }

        if (is_null($item->getId())) {
            $item->setChildMode($this->childMode);
        }

        return $item;
    }

    public function addItem(Varien_Object $item)
    {
        if (is_null($this->childMode)) {
            return parent::addItem($item);
        }

        $item->setChildMode($this->childMode);

        if (is_null($item->getId())) {
            return parent::addItem($item);
        }

        $modelName = str_replace('M2ePro/',ucwords($this->childMode).'_',$this->_resourceModel);
        $childObject = Mage::helper('M2ePro')->getModel($modelName);

        $childObject->addData($item->getData());
        $childObject->setOrigData();
        $childObject->setParentObject($item);

        $item->setChildObject($childObject);

        return parent::addItem($item);
    }

    // ########################################
}