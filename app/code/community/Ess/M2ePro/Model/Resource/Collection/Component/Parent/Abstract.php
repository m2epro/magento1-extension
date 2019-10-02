<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Resource_Collection_Component_Parent_Abstract
    extends Ess_M2ePro_Model_Resource_Collection_Component_Abstract
{
    protected $_childMode = null;

    //########################################

    public function __construct($resource = null)
    {
        if (is_object($resource) && ($resource instanceof Ess_M2ePro_Model_Resource_Component_Parent_Abstract)) {
            /** @var $resource Ess_M2ePro_Model_Resource_Component_Parent_Abstract */
            $this->setChildMode($resource->getChildMode());
        }

        parent::__construct($resource);
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

    //########################################

    protected function _initSelect()
    {
        $temp = parent::_initSelect();

        if ($this->_childMode === null) {
            return $temp;
        }

        /** @var $resource Ess_M2ePro_Model_Resource_Component_Parent_Abstract */
        $resource = $this->getResource();

        $componentTable = $resource->getChildTable();
        $componentPk = $resource->getChildPrimary();

        $this->getSelect()->join(
            array('second_table'=>$componentTable),
            "`second_table`.`".$componentPk."` = `main_table`.`id`"
        );
        $this->getSelect()->where("`main_table`.`component_mode` = '".$this->_childMode . "'");

        return $temp;
    }

    public function getFirstItem()
    {
        /** @var $item Ess_M2ePro_Model_Component_Parent_Abstract */
        $item = parent::getFirstItem();

        if ($this->_childMode === null) {
            return $item;
        }

        if ($item->getId() === null) {
            $item->setChildMode($this->_childMode);
        }

        return $item;
    }

    public function getLastItem()
    {
        /** @var $item Ess_M2ePro_Model_Component_Parent_Abstract */
        $item = parent::getLastItem();

        if ($this->_childMode === null) {
            return $item;
        }

        if ($item->getId() === null) {
            $item->setChildMode($this->_childMode);
        }

        return $item;
    }

    public function addItem(Varien_Object $item)
    {
        if ($this->_childMode === null) {
            return parent::addItem($item);
        }

        $item->setChildMode($this->_childMode);

        if ($item->getId() === null) {
            return parent::addItem($item);
        }

        $modelName = str_replace('M2ePro/', ucwords($this->_childMode) . '_', $this->_resourceModel);
        $childObject = Mage::helper('M2ePro')->getModel($modelName);

        $childObject->addData($item->getData());
        $childObject->setOrigData();
        $childObject->setParentObject($item);

        $item->setChildObject($childObject);

        return parent::addItem($item);
    }

    //########################################
}
