<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */


class Ess_M2ePro_Model_ControlPanel_Inspection_Issue_Factory
{
    /**
     * @param string $message
     * @param string $metadata
     *
     * @return Ess_M2ePro_Model_ControlPanel_Inspection_Issue
     */
    public function createIssue($message, $metadata = null)
    {
        return Mage::getModel('M2ePro/ControlPanel_Inspection_Issue',
            array(
                'message' => $message,
                'metadata' => $metadata
            )
        );
    }
}
