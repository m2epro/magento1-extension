<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Switcher extends Mage_Adminhtml_Block_Template
{
    protected $template = 'M2ePro/switcher.phtml';

    protected $itemsIds = array();

    protected $paramName = '';

    protected $hasDefaultOption = true;

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate($this->template);
    }

    //########################################

    abstract public function getLabel();

    abstract public function getItems();

    public function getSwitchUrl()
    {
        $controllerName = $this->getData('controller_name') ? $this->getData('controller_name') : '*';
        return $this->getUrl(
            "*/{$controllerName}/*",
            array('_current' => true, $this->getParamName() => $this->getParamPlaceHolder())
        );
    }

    public function getSwitchCallback()
    {
        $callback = 'switch';
        $callback .= ucfirst($this->paramName);

        return $callback;
    }

    public function getConfirmMessage()
    {
        return '';
    }

    //########################################

    public function getParamName()
    {
        return $this->paramName;
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

    public function hasDefaultOption()
    {
        return (bool)$this->hasDefaultOption;
    }

    abstract public function getDefaultOptionName();

    public function getDefaultOptionValue()
    {
        return 'all';
    }

    //########################################
}