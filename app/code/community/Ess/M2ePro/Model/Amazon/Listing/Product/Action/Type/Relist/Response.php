<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Relist_Response
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Response
{
    const INSTRUCTION_TYPE_CHECK_QTY            = 'success_relist_check_qty';
    const INSTRUCTION_TYPE_CHECK_PRICE_REGULAR  = 'success_relist_check_price_regular';
    const INSTRUCTION_TYPE_CHECK_PRICE_BUSINESS = 'success_relist_check_price_business';
    const INSTRUCTION_TYPE_CHECK_DETAILS        = 'success_relist_check_details';
    const INSTRUCTION_TYPE_CHECK_IMAGES         = 'success_relist_check_images';

    //########################################

    /**
     * @param array $params
     */
    public function processSuccess($params = array())
    {
        $data = array();

        if ($this->getConfigurator()->isDetailsAllowed() || $this->getConfigurator()->isImagesAllowed()) {
            $data['defected_messages'] = null;
        }

        $data = $this->appendStatusChangerValue($data);
        $data = $this->appendQtyValues($data);
        $data = $this->appendRegularPriceValues($data);
        $data = $this->appendBusinessPriceValues($data);

        $data = $this->processRecheckInstructions($data);
        $data = $this->appendIsStoppedManually($data, false);

        if (isset($data['additional_data'])) {
            $data['additional_data'] = Mage::helper('M2ePro')->jsonEncode($data['additional_data']);
        }

        $this->getListingProduct()->addData($data);

        $this->setLastSynchronizationDates();

        $this->getListingProduct()->save();
    }

    //########################################

    protected function processRecheckInstructions(array $data)
    {
        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        if (empty($data['additional_data']['recheck_properties'])) {
            return $data;
        }

        $instructionsData = array();

        foreach ($data['additional_data']['recheck_properties'] as $property) {
            $instructionType     = null;
            $instructionPriority = 0;

            switch ($property) {
                case 'qty':
                    $instructionType     = self::INSTRUCTION_TYPE_CHECK_QTY;
                    $instructionPriority = 80;
                    break;

                case 'price_regular':
                    $instructionType     = self::INSTRUCTION_TYPE_CHECK_PRICE_REGULAR;
                    $instructionPriority = 60;
                    break;

                case 'price_business':
                    $instructionType     = self::INSTRUCTION_TYPE_CHECK_PRICE_BUSINESS;
                    $instructionPriority = 60;
                    break;

                case 'details':
                    $instructionType     = self::INSTRUCTION_TYPE_CHECK_DETAILS;
                    $instructionPriority = 30;
                    break;

                case 'images':
                    $instructionType     = self::INSTRUCTION_TYPE_CHECK_IMAGES;
                    $instructionPriority = 30;
                    break;
            }

            if ($instructionType === null) {
                continue;
            }

            $instructionsData[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => $instructionType,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => $instructionPriority,
            );
        }

        Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add($instructionsData);

        unset($data['additional_data']['recheck_properties']);

        return $data;
    }

    //########################################
}
