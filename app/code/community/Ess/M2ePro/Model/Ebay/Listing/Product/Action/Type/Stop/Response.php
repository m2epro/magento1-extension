<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract as ChangeProcessor;

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Stop_Response
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
{
    //########################################

    public function processSuccess(array $response, array $responseParams = array())
    {
        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE
        );

        $data = $this->appendStatusChangerValue($data, $responseParams);

        $data = $this->appendItemFeesValues($data, $response);
        $data = $this->appendStartDateEndDateValues($data, $response);

        if (isset($data['additional_data'])) {
            $data['additional_data'] = Mage::helper('M2ePro')->jsonEncode($data['additional_data']);
        }

        $this->getListingProduct()->addData($data)->save();

        $this->updateVariationsValues(false);
    }

    public function processAlreadyStopped(array $response, array $responseParams = array())
    {
        $responseParams['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;
        $this->processSuccess($response, $responseParams);
    }

    //########################################

    protected function appendItemFeesValues($data, $response)
    {
        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        $data['additional_data']['ebay_item_fees'] = array();

        return $data;
    }

    // ---------------------------------------

    protected function updateVariationsValues($saveQtySold)
    {
        $variations = $this->getListingProduct()->getVariations(true);

        foreach ($variations as $variation) {

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */

            $data = array(
                'add' => 0
            );

            if ($variation->getChildObject()->isListed() || $variation->getChildObject()->isHidden()) {
                $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE;
            }

            $variation->addData($data)->save();
        }
    }

    //########################################

    public function throwRepeatActionInstructions()
    {
        Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add(
            array(
                array(
                    'listing_product_id' => $this->getListingProduct()->getId(),
                    'type'               => ChangeProcessor::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
                    'initiator'          => self::INSTRUCTION_INITIATOR,
                    'priority'           => 80
                )
            )
        );
    }

    //########################################
}
