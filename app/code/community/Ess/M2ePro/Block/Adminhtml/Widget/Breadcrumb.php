<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Widget_Breadcrumb extends Mage_Adminhtml_Block_Widget
{
    protected $_containerData = array();
    protected $_steps         = array();
    protected $_selectedStep  = null;

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setId('widgetBreadcrumb');
        $this->setTemplate('M2ePro/widget/breadcrumb.phtml');
    }

    //########################################

    public function setContainerData(array $data)
    {
        $this->_containerData = $data;
        return $this;
    }

    public function getContainerData($key)
    {
        return isset($this->_containerData[$key]) ? $this->_containerData[$key] : '';
    }

    public function getSteps()
    {
        return $this->_steps;
    }

    public function setSteps(array $steps)
    {
        $this->_steps = $steps;
        return $this;
    }

    public function getSelectedStep()
    {
        return $this->_selectedStep;
    }

    public function setSelectedStep($stepId)
    {
        $this->_selectedStep = $stepId;
        return $this;
    }

    //########################################
}
