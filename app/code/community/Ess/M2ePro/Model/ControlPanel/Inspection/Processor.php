<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Processor
{
    public function process(Ess_M2ePro_Model_ControlPanel_Inspection_Definition $definition)
    {
        /** @var Ess_M2ePro_Model_ControlPanel_Inspection_Result_Factory $resultFactory */
        $resultFactory = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory');

        /** @var Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface $handler */
        $handler = Mage::getModel($definition->getHandler());

        try {
            $issues = $handler->process();
            $result = $resultFactory->createSuccess($issues);
        } catch (Exception $e) {
            $result = $resultFactory->createFailed($e->getMessage());
        }

        return $result;
    }
}
