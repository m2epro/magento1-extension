<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Switcher extends Mage_Adminhtml_Block_Template
{
    protected $_template = 'M2ePro/switcher.phtml';

    protected $_items;

    protected $_itemsIds = array();

    protected $_paramName = '';

    protected $_hasDefaultOption = true;

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate($this->_template);
    }

    //########################################

    abstract public function getLabel();

    abstract protected function loadItems();

    //########################################

    public function getItems()
    {
        if ($this->_items === null) {
            $this->loadItems();
        }

        return $this->_items;
    }

    public function isEmpty()
    {
        $items = $this->getItems();
        return empty($items);
    }

    //########################################

    public function getSwitchUrl()
    {
        $controllerName = $this->getData('controller_name') ? $this->getData('controller_name') : '*';
        $actionName     = $this->getData('action_name') ? $this->getData('action_name') : '*';

        $params = $this->getData('action_params') ? $this->getData('action_params') : array();

        $params['_current'] = true;
        $params[$this->getParamName()] = $this->getParamPlaceHolder();

        return $this->getUrl("*/{$controllerName}/{$actionName}", $params);
    }

    public function getSwitchCallback()
    {
        $callback = 'switch';
        $callback .= ucfirst($this->_paramName);

        return $callback;
    }

    public function getConfirmMessage()
    {
        return '';
    }

    //########################################

    public function getParamName()
    {
        return $this->_paramName;
    }

    public function getParamPlaceHolder()
    {
        return '%' . $this->getParamName() . '%';
    }

    public function getSelectedParam()
    {
        return $this->getRequest()->getParam($this->getParamName());
    }

    //########################################

    public function hasDefaultOption($hasDefaultOption = null)
    {
        if (null !== $hasDefaultOption) {
            $this->_hasDefaultOption = $hasDefaultOption;
        }

        return (bool)$this->_hasDefaultOption;
    }

    public function getDefaultOptionName()
    {
        return $this->__('All');
    }

    public function getDefaultOptionValue()
    {
        return 'all';
    }

    //########################################
}
