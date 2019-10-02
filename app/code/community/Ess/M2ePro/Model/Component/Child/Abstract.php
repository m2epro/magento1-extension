<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Component_Child_Abstract extends Ess_M2ePro_Model_Component_Abstract
{
    protected $_parentObject;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Component_Parent_Abstract $object
     */
    public function setParentObject(Ess_M2ePro_Model_Component_Parent_Abstract $object)
    {
        if ($object->getId() === null) {
            return;
        }

        $this->_parentObject = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Component_Parent_Abstract
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getParentObject()
    {
        if ($this->getId() === null) {
             throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        if ($this->_parentObject !== null) {
            return $this->_parentObject;
        }

        $tempMode = $this->getComponentMode();

        if ($tempMode === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Set Component Mode first');
        }

        $modelName           = str_replace('M2ePro/'.ucwords($tempMode).'_', '', $this->_resourceName);
        $this->_parentObject = Mage::helper('M2ePro')->getModel($modelName);

        $this->_parentObject->setChildMode($tempMode);
        $this->_parentObject->loadInstance($this->getId());
        $this->_parentObject->setChildObject($this);

        return $this->_parentObject;
    }

    //########################################

    /**
     * @param string $modelName
     * @param string $fieldName
     * @param bool $asObjects
     * @param array $filters
     * @param array $sort
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getRelatedComponentItems(
        $modelName,
        $fieldName,
        $asObjects = false,
        array $filters = array(),
        array $sort = array()
    ) {
        if ($this->getId() === null) {
             throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        $tempMode = $this->getComponentMode();

        if ($tempMode === null) {
             throw new Ess_M2ePro_Model_Exception_Logic('Set Component Mode first');
        }

        $tempModel = Mage::helper('M2ePro/Component')->getComponentModel($tempMode, $modelName);

        if ($tempModel === null || !($tempModel instanceof Ess_M2ePro_Model_Abstract)) {
            return array();
        }

        return $this->getRelatedItems($tempModel, $fieldName, $asObjects, $filters, $sort);
    }

    //########################################

    protected function getComponentMode()
    {
        return null;
    }

    //########################################
}
