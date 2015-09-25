<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Wizard extends Ess_M2ePro_Model_Abstract
{
    protected $steps = array();

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Wizard');
    }

    // ########################################

    public function isActive()
    {
        return true;
    }

    public function getNick()
    {
        return NULL;
    }

    // ########################################

    public function getSteps()
    {
        return $this->steps;
    }

    public function getFirstStep()
    {
        return reset($this->steps);
    }

    // ----------------------------------------

    public function getPrevStep()
    {
        $currentStep = Mage::helper('M2ePro/Module_Wizard')->getStep($this->getNick());
        $prevStepIndex = array_search($currentStep, $this->steps) - 1;
        return isset($this->steps[$prevStepIndex]) ? $this->steps[$prevStepIndex] : false;
    }

    public function getNextStep()
    {
        $currentStep = Mage::helper('M2ePro/Module_Wizard')->getStep($this->getNick());
        $nextStepIndex = array_search($currentStep, $this->steps) + 1;
        return isset($this->steps[$nextStepIndex]) ? $this->steps[$nextStepIndex] : false;
    }

    // ########################################
}