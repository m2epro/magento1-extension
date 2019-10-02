<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Instruction_SynchronizationTemplate_Handler
    implements Ess_M2ePro_Model_Listing_Product_Instruction_Handler_Interface
{
    //########################################

    public function process(Ess_M2ePro_Model_Listing_Product_Instruction_Handler_Input $input)
    {
        $scheduledActionCollection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $scheduledActionCollection->addFieldToFilter('listing_product_id', $input->getListingProduct()->getId());

        /** @var Ess_M2ePro_Model_Listing_Product_ScheduledAction $scheduledAction */
        $scheduledAction = $scheduledActionCollection->getFirstItem();

        $checkerInput = Mage::getModel('M2ePro/Listing_Product_Instruction_SynchronizationTemplate_Checker_Input');
        $checkerInput->setListingProduct($input->getListingProduct());
        $checkerInput->setInstructions($input->getInstructions());

        if ($scheduledAction->getId()) {
            $checkerInput->setScheduledAction($scheduledAction);
        }

        $params = array(
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_SYNCH
        );

        foreach ($this->getAllCheckers() as $checker) {
            $checkerModel = $this->getCheckerModel($checker);
            $checkerModel->setInput($checkerInput);

            if (!$checkerModel->isAllowed()) {
                continue;
            }

            $checkerModel->process($params);
        }
    }

    //########################################

    protected function getAllCheckers()
    {
        return array(
            'NotListed',
            'Active',
            'Inactive',
        );
    }

    /**
     * @param $checkerNick
     * @return Ess_M2ePro_Model_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getCheckerModel($checkerNick)
    {
        $checkerModelName = 'M2ePro/Ebay_Listing_Product_Instruction_SynchronizationTemplate_Checker_'.$checkerNick;

        if (!class_exists(Mage::getConfig()->getModelClassName($checkerModelName))) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                sprintf('Checker model "%s" does not exist.', $checkerModelName)
            );
        }

        $checkerModel = Mage::getModel($checkerModelName);

        if (!($checkerModel instanceof
                Ess_M2ePro_Model_Ebay_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract)) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                sprintf(
                    'Checker model "%s" does not extends
                    "Ess_M2ePro_Model_Ebay_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract"
                    class',
                    $checkerModelName
                )
            );
        }

        return $checkerModel;
    }

    //########################################
}
