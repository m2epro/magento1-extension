<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Component_Parent_Abstract extends Ess_M2ePro_Model_Component_Abstract
{
    protected $_childMode   = null;
    protected $_childObject = null;

    //########################################

    public function __construct()
    {
        $args = func_get_args();
        empty($args[0]) && $args[0] = array();
        $params = $args[0];

        if (isset($params['child_mode'])) {
            $this->setChildMode($params['child_mode']);
        }

        parent::__construct($params);
    }

    //########################################

    /**
     * @param string $mode
     * @return $this
     */
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

    public function setChildObject(Ess_M2ePro_Model_Component_Child_Abstract $object)
    {
        if ($object->getId() === null) {
            return;
        }

        $this->_childObject = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Component_Child_Abstract
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getChildObject()
    {
        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        if ($this->_childObject !== null) {
            return $this->_childObject;
        }

        $tempMode = null;

        if ($this->_childMode !== null) {
            $tempMode = $this->_childMode;
        } else {
            $tempMode = $this->getComponentMode();
        }

        if (!$tempMode) {
            throw new Ess_M2ePro_Model_Exception_Logic('Component Mode is not defined.');
        }

        $modelName          = str_replace('M2ePro/', ucwords($tempMode).'_', $this->_resourceName);
        $this->_childObject = Mage::helper('M2ePro')->getModel($modelName);

        $this->_childObject->loadInstance($this->getId());
        $this->_childObject->addData($this->getData());
        $this->_childObject->setParentObject($this);

        return $this->_childObject;
    }

    //########################################

    public function getComponentMode()
    {
        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        return $this->getData('component_mode');
    }

    // ---------------------------------------

    public function isComponentModeEbay()
    {
        return $this->getComponentMode() == Ess_M2ePro_Helper_Component_Ebay::NICK;
    }

    public function isComponentModeAmazon()
    {
        return $this->getComponentMode() == Ess_M2ePro_Helper_Component_Amazon::NICK;
    }

    public function isComponentModeWalmart()
    {
        return $this->getComponentMode() == Ess_M2ePro_Helper_Component_Walmart::NICK;
    }

    // ---------------------------------------

    public function getComponentTitle()
    {
        if ($this->isComponentModeEbay()) {
            return Mage::helper('M2ePro/Component_Ebay')->getTitle();
        }

        if ($this->isComponentModeAmazon()) {
            return Mage::helper('M2ePro/Component_Amazon')->getTitle();
        }

        return '';
    }

    //########################################

    public function isLocked()
    {
        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        if (parent::isLocked()) {
            return true;
        }

        $childObject = $this->getChildObject();

        if ($childObject === null) {
            return false;
        }

        if ($childObject->isLocked()) {
            return true;
        }

        return false;
    }

    public function deleteInstance()
    {
        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        if ($this->isLocked()) {
            return false;
        }

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    // ---------------------------------------

    protected function deleteChildInstance()
    {
        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        $childObject = $this->getChildObject();

        if ($childObject === null || !($childObject instanceof Ess_M2ePro_Model_Abstract)) {
            return;
        }

        $childObject->deleteInstance();
    }

    //########################################

    public function save()
    {
        if ($this->_childMode !== null && $this->getData('component_mode') === null) {
            $this->setData('component_mode', $this->_childMode);
        }

        $temp               = parent::save();
        $this->_childObject = null;
        return $temp;
    }

    public function delete()
    {
        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        $temp               = parent::delete();
        $this->_childMode   = null;
        $this->_childObject = null;
        return $temp;
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

        $tempMode = null;

        if ($this->_childMode !== null) {
            $tempMode = $this->_childMode;
        } else {
            $tempMode = $this->getComponentMode();
        }

        $tempModel = Mage::helper('M2ePro/Component')->getComponentModel($tempMode, $modelName);

        if ($tempModel === null || !($tempModel instanceof Ess_M2ePro_Model_Abstract)) {
            return array();
        }

        return $this->getRelatedItems($tempModel, $fieldName, $asObjects, $filters, $sort);
    }

    //########################################

    protected function _getResource()
    {
        if ($this->_childMode === null) {
            return parent::_getResource();
        }

        if (empty($this->_resourceName)) {
            Mage::throwException(Mage::helper('core')->__('Resource is not set.'));
        }

        $arguments = array(
            'child_mode' => $this->_childMode
        );

        return Mage::getResourceModel($this->_resourceName, $arguments);
    }

    //########################################
}
