<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Listing_Auto_Actions_Listing as Actions_Listing;

class Ess_M2ePro_Model_Walmart_Listing_Product_Instruction_AutoActions_Handler
    implements Ess_M2ePro_Model_Listing_Product_Instruction_Handler_Interface
{
    //########################################

    private function getAffectedInstructionTypes()
    {
        return array(
            Actions_Listing::INSTRUCTION_TYPE_STOP,
            Actions_Listing::INSTRUCTION_TYPE_STOP_AND_REMOVE,
        );
    }

    //########################################

    public function process(Ess_M2ePro_Model_Listing_Product_Instruction_Handler_Input $input)
    {
        if (!$input->hasInstructionWithTypes($this->getAffectedInstructionTypes())) {
            return;
        }

        $listingProduct = $input->getListingProduct();

        $scheduledActionCollection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $scheduledActionCollection->addFieldToFilter('listing_product_id', $listingProduct->getId());

        /** @var Ess_M2ePro_Model_Listing_Product_ScheduledAction $scheduledAction */
        $scheduledAction = $scheduledActionCollection->getFirstItem();

        $params = array();

        if ($input->hasInstructionWithType(Actions_Listing::INSTRUCTION_TYPE_STOP_AND_REMOVE)) {

            if (!$input->getListingProduct()->isStoppable()) {
                $removeHandler = Mage::getModel(
                    'M2ePro/Walmart_Listing_Product_RemoveHandler',
                    array('listing_product' => $input->getListingProduct())
                );
                $removeHandler->process();

                return;
            }

            $params['remove'] = true;
        }

        $scheduledActionData = array(
            'listing_product_id' => $listingProduct->getId(),
            'component'          => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'action_type'        => Ess_M2ePro_Model_Listing_Product::ACTION_STOP,
            'is_force'           => true,
            'additional_data'    => Mage::helper('M2ePro')->jsonEncode(array('params' => $params)),
        );

        $scheduledAction->addData($scheduledActionData);

        $scheduledActionManager = Mage::getModel('M2ePro/Listing_Product_ScheduledAction_Manager');

        if ($scheduledAction->getId()) {
            $scheduledActionManager->updateAction($scheduledAction);
        } else {
            $scheduledActionManager->addAction($scheduledAction);
        }
    }

    //########################################
}