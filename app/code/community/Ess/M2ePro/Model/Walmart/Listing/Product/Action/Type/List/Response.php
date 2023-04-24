<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_List_Response
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Response
{
    const INSTRUCTION_TYPE_CHECK_QTY        = 'success_list_check_qty';
    const INSTRUCTION_TYPE_CHECK_LAG_TIME   = 'success_list_check_lag_time';
    const INSTRUCTION_TYPE_CHECK_PRICE      = 'success_list_check_price';
    const INSTRUCTION_TYPE_CHECK_PROMOTIONS = 'success_list_check_promotions';

    //########################################

    /**
     * @param array $params
     */
    public function processSuccess($params = array())
    {
        $data = array(
            'sku'        => $this->getRequestData()->getSku(),
            'wpid'       => $params['wpid'],
            'item_id'    => $params['item_id'],
            'gtin'       => $params['identifiers']['GTIN'],
            'online_qty' => 0,
            'list_date'  => Mage::helper('M2ePro')->getCurrentGmtDate()
        );

        $data = $this->appendStatusChangerValue($data);
        $data = $this->appendPriceValues($data);
        $data = $this->appendDetailsValues($data);
        $data = $this->appendProductIdsData($data);
        $data = $this->appendIsStoppedManually($data, false);

        $this->getListingProduct()->addData($data);
        $this->getListingProduct()->save();

        $instructionDate = new DateTime('now', new DateTimeZone('UTC'));
        $instructionDate->modify('+ 3 hours');
        $this->throwSynchronizationInstructions($instructionDate);

        $instructionDate = new DateTime('now', new DateTimeZone('UTC'));
        $instructionDate->modify('+ 24 hours');
        $this->throwSynchronizationInstructions($instructionDate);
    }

    //########################################

    /**
     * Updating of Promotions/Price will be skipped for 24 hours. So we add instructions to check them after
     * that time
     */
    protected function throwSynchronizationInstructions(DateTime $instructionDate)
    {
        $instructionsData = array(
            array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => self::INSTRUCTION_TYPE_CHECK_QTY,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 80,
                'skip_until'         => $instructionDate->format('Y-m-d H:i:s')
            ),
            array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => self::INSTRUCTION_TYPE_CHECK_LAG_TIME,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 60,
                'skip_until'         => $instructionDate->format('Y-m-d H:i:s')
            ),
            array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => self::INSTRUCTION_TYPE_CHECK_PRICE,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 60,
                'skip_until'         => $instructionDate->format('Y-m-d H:i:s')
            ),
            array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => self::INSTRUCTION_TYPE_CHECK_PROMOTIONS,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 30,
                'skip_until'         => $instructionDate->format('Y-m-d H:i:s')
            ),
        );
        Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add($instructionsData);
    }

    //########################################
}
