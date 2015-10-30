<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Component_Parent_Abstract extends Ess_M2ePro_Model_Component_Abstract
{
    protected $childMode = NULL;
    protected $childObject = NULL;

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
        $mode && $this->childMode = $mode;
        return $this;
    }

    public function getChildMode()
    {
        return $this->childMode;
    }

    // ---------------------------------------

    public function setChildObject(Ess_M2ePro_Model_Component_Child_Abstract $object)
    {
        if (is_null($object->getId())) {
            return;
        }

        $this->childObject = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Component_Child_Abstract
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getChildObject()
    {
        if (is_null($this->getId())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        if (!is_null($this->childObject)) {
            return $this->childObject;
        }

        $tempMode = NULL;

        if (!is_null($this->childMode)) {
            $tempMode = $this->childMode;
        } else {
            $tempMode = $this->getComponentMode();
        }

        if (!$tempMode) {
            throw new Ess_M2ePro_Model_Exception_Logic('Component Mode is not defined.');
        }

        $modelName = str_replace('M2ePro/',ucwords($tempMode).'_',$this->_resourceName);
        $this->childObject = Mage::helper('M2ePro')->getModel($modelName);

        $this->childObject->loadInstance($this->getId());
        $this->childObject->addData($this->getData());
        $this->childObject->setParentObject($this);

        return $this->childObject;
    }

    //########################################

    public function getComponentMode()
    {
        if (is_null($this->getId())) {
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

    public function isComponentModeBuy()
    {
        return $this->getComponentMode() == Ess_M2ePro_Helper_Component_Buy::NICK;
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

        if ($this->isComponentModeBuy()) {
            return Mage::helper('M2ePro/Component_Buy')->getTitle();
        }

        return '';
    }

    //########################################

    public function isLocked()
    {
        if (is_null($this->getId())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        if (parent::isLocked()) {
            return true;
        }

        $childObject = $this->getChildObject();

        if (is_null($childObject)) {
            return false;
        }

        if ($childObject->isLocked()) {
            return true;
        }

        return false;
    }

    public function deleteInstance()
    {
        if (is_null($this->getId())) {
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
        if (is_null($this->getId())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        $childObject = $this->getChildObject();

        if (is_null($childObject) || !($childObject instanceof Ess_M2ePro_Model_Abstract)) {
            return;
        }

        $childObject->deleteInstance();
    }

    //########################################

    public function save()
    {
        if (!is_null($this->childMode) && is_null($this->getData('component_mode'))) {
            $this->setData('component_mode',$this->childMode);
        }

        $temp = parent::save();
        $this->childObject = NULL;
        return $temp;
    }

    public function delete()
    {
        if (is_null($this->getId())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        $temp = parent::delete();
        $this->childMode = NULL;
        $this->childObject = NULL;
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
    protected function getRelatedComponentItems($modelName, $fieldName, $asObjects = false,
                                                array $filters = array(), array $sort = array())
    {
        if (is_null($this->getId())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        $tempMode = NULL;

        if (!is_null($this->childMode)) {
            $tempMode = $this->childMode;
        } else {
            $tempMode = $this->getComponentMode();
        }

        $tempModel = Mage::helper('M2ePro/Component')->getComponentModel($tempMode,$modelName);

        if (is_null($tempModel) || !($tempModel instanceof Ess_M2ePro_Model_Abstract)) {
            return array();
        }

        return $this->getRelatedItems($tempModel,$fieldName,$asObjects,$filters,$sort);
    }

    //########################################

    protected function _getResource()
    {
        if (is_null($this->childMode)) {
            return parent::_getResource();
        }

        if (empty($this->_resourceName)) {
            Mage::throwException(Mage::helper('core')->__('Resource is not set.'));
        }

        $arguments = array(
            'child_mode' => $this->childMode
        );

        return Mage::getResourceModel($this->_resourceName,$arguments);
    }

    //########################################
}