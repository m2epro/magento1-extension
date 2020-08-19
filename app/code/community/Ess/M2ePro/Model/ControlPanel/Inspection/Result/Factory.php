<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection as Inspection;
use Ess_M2ePro_Model_ControlPanel_Inspection_Result as Result;

class Ess_M2ePro_Model_ControlPanel_Inspection_Result_Factory
{
    //########################################

    public function create($inspector, $state, $message, $metadata)
    {
        return Mage::getModel(
            'M2ePro/ControlPanel_Inspection_Result',
            array($inspector, $state, $message, $metadata)
        );
    }

    //########################################

    public function createSuccess(Inspection $inspector, $message = null, $metadata = null)
    {
        return $this->create($inspector, Result::STATE_SUCCESS, $message, $metadata);
    }

    public function createNotice(Inspection $inspector, $message, $metadata = null)
    {
        return $this->create($inspector, Result::STATE_NOTICE, $message, $metadata);
    }

    public function createWarning(Inspection $inspector, $message, $metadata = null)
    {
        return $this->create($inspector, Result::STATE_WARNING, $message, $metadata);
    }

    public function createError(Inspection $inspector, $message, $metadata = null)
    {
        return $this->create($inspector, Result::STATE_ERROR, $message, $metadata);
    }

    //########################################
}
