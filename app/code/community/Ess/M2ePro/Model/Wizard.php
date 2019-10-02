<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Wizard extends Ess_M2ePro_Model_Abstract
{
    protected $_steps = array();

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Wizard');
    }

    //########################################

    /**
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     * @return null
     */
    public function getNick()
    {
        return NULL;
    }

    //########################################

    /**
     * @return array
     */
    public function getSteps()
    {
        return $this->_steps;
    }

    public function getFirstStep()
    {
        return reset($this->_steps);
    }

    // ---------------------------------------

    public function getPrevStep()
    {
        $currentStep = Mage::helper('M2ePro/Module_Wizard')->getStep($this->getNick());
        $prevStepIndex = array_search($currentStep, $this->_steps) - 1;
        return isset($this->_steps[$prevStepIndex]) ? $this->_steps[$prevStepIndex] : false;
    }

    public function getNextStep()
    {
        $currentStep = Mage::helper('M2ePro/Module_Wizard')->getStep($this->getNick());
        $nextStepIndex = array_search($currentStep, $this->_steps) + 1;
        return isset($this->_steps[$nextStepIndex]) ? $this->_steps[$nextStepIndex] : false;
    }

    //########################################
}
