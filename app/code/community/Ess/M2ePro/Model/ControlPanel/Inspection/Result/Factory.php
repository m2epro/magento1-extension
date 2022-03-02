<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */


class Ess_M2ePro_Model_ControlPanel_Inspection_Result_Factory
{
    /**
     * @param bool $status
     * @param string|null $errorMessage
     * @param Ess_M2ePro_Model_ControlPanel_Inspection_Issue[]|null $issues
     *
     * @return Ess_M2ePro_Model_ControlPanel_Inspection_Result
     */
    private function create($status, $errorMessage, $issues = array())
    {
        return Mage::getModel(
            'M2ePro/ControlPanel_Inspection_Result',
            array(
                'status'       => $status,
                'errorMessage' => $errorMessage,
                'issues'       => $issues,
            )
        );
    }

    /**
     * @param Ess_M2ePro_Model_ControlPanel_Inspection_Issue[] $issues
     *
     * @return Ess_M2ePro_Model_ControlPanel_Inspection_Result
     */
    public function createSuccess($issues)
    {
        return $this->create(true, null, $issues);
    }

    /**
     * @param string $errorMessage
     *
     * @return Ess_M2ePro_Model_ControlPanel_Inspection_Result
     */
    public function createFailed($errorMessage)
    {
        return $this->create(false, $errorMessage);
    }
}
