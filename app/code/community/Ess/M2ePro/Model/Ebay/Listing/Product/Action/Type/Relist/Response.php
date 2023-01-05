<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Relist_Response
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
{
    const INSTRUCTION_TYPE_CHECK_QTY         = 'success_relist_check_qty';
    const INSTRUCTION_TYPE_CHECK_PRICE       = 'success_relist_check_price';
    const INSTRUCTION_TYPE_CHECK_TITLE       = 'success_relist_check_title';
    const INSTRUCTION_TYPE_CHECK_SUBTITLE    = 'success_relist_check_subtitle';
    const INSTRUCTION_TYPE_CHECK_DESCRIPTION = 'success_relist_check_description';
    const INSTRUCTION_TYPE_CHECK_IMAGES      = 'success_relist_check_images';
    const INSTRUCTION_TYPE_CHECK_CATEGORIES  = 'success_relist_check_categories';
    const INSTRUCTION_TYPE_CHECK_PARTS       = 'success_relist_check_parts';
    const INSTRUCTION_TYPE_CHECK_PAYMENT     = 'success_relist_check_payment';
    const INSTRUCTION_TYPE_CHECK_SHIPPING    = 'success_relist_check_shipping';
    const INSTRUCTION_TYPE_CHECK_RETURN      = 'success_relist_check_return';
    const INSTRUCTION_TYPE_CHECK_OTHER       = 'success_relist_check_other';

    //########################################

    public function processSuccess(array $response, array $responseParams = array())
    {
        $this->prepareMetadata();

        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
            'ebay_item_id' => $this->createEbayItem($response['ebay_item_id'])->getId()
        );

        $data = $this->appendStatusHiddenValue($data);
        $data = $this->appendStatusChangerValue($data, $responseParams);

        $data = $this->appendOnlineBidsValue($data);
        $data = $this->appendOnlineQtyValues($data);
        $data = $this->appendOnlinePriceValues($data);
        $data = $this->appendOnlineInfoDataValues($data);

        $data = $this->appendDescriptionValues($data);

        $data = $this->appendItemFeesValues($data, $response);
        $data = $this->appendStartDateEndDateValues($data, $response);
        $data = $this->appendGalleryImagesValues($data, $response);

        $data = $this->removeConditionNecessary($data);

        $data = $this->appendIsVariationMpnFilledValue($data);
        $data = $this->appendVariationsThatCanNotBeDeleted($data, $response);

        $data = $this->appendIsVariationValue($data);
        $data = $this->appendIsAuctionType($data);

        $data = $this->processRecheckInstructions($data);

        if (isset($data['additional_data'])) {
            $data['additional_data'] = Mage::helper('M2ePro')->jsonEncode($data['additional_data']);
        }

        $this->getListingProduct()->addData($data)->save();

        $this->updateVariationsValues(false);
    }

    public function processAlreadyActive(array $response, array $responseParams = array())
    {
        $responseParams['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;
        $this->processSuccess($response, $responseParams);
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
                    $instructionType     = self::INSTRUCTION_TYPE_CHECK_PRICE;
                    $instructionPriority = 60;
                    break;

                case 'title':
                    $instructionType     = self::INSTRUCTION_TYPE_CHECK_TITLE;
                    $instructionPriority = 30;
                    break;

                case 'subtitle':
                    $instructionType     = self::INSTRUCTION_TYPE_CHECK_SUBTITLE;
                    $instructionPriority = 30;
                    break;

                case 'description':
                    $instructionType     = self::INSTRUCTION_TYPE_CHECK_DESCRIPTION;
                    $instructionPriority = 30;
                    break;

                case 'images':
                    $instructionType     = self::INSTRUCTION_TYPE_CHECK_IMAGES;
                    $instructionPriority = 30;
                    break;

                case 'payment':
                    $instructionType     = self::INSTRUCTION_TYPE_CHECK_PAYMENT;
                    $instructionPriority = 30;
                    break;

                case 'shipping':
                    $instructionType     = self::INSTRUCTION_TYPE_CHECK_SHIPPING;
                    $instructionPriority = 30;
                    break;

                case 'return':
                    $instructionType     = self::INSTRUCTION_TYPE_CHECK_RETURN;
                    $instructionPriority = 30;
                    break;

                case 'other':
                    $instructionType     = self::INSTRUCTION_TYPE_CHECK_OTHER;
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

    protected function removeConditionNecessary($data)
    {
        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        if (isset($data['additional_data']['is_need_relist_condition'])) {
            unset($data['additional_data']['is_need_relist_condition']);
        }

        return $data;
    }

    //########################################
}
