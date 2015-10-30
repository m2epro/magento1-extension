<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2EPro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Status
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    //########################################

    protected function check() {}

    protected function execute()
    {
        $childListingProducts = $this->getProcessor()->getTypeModel()->getChildListingsProducts();

        if (!$this->getProcessor()->isGeneralIdSet() || empty($childListingProducts)) {
            $this->getProcessor()->getListingProduct()->addData(array(
                'status'                   => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
                'variation_child_statuses' => null,
            ));

            return;
        }

        $sameStatus = null;
        $isStatusSame = true;

        $resultStatus = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;

        $childStatuses = array(
            Ess_M2ePro_Model_Listing_Product::STATUS_LISTED     => 0,
            Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => 0,
            Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED    => 0,
            Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED    => 0,
            Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN    => 0,
        );

        foreach ($childListingProducts as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            $childStatus = $childListingProduct->getStatus();

            $childStatuses[$childStatus]++;

            if ($childStatus == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $resultStatus = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
                continue;
            }

            if (!$isStatusSame || $resultStatus == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                continue;
            }

            if (is_null($sameStatus)) {
                $sameStatus = $childStatus;
                continue;
            }

            if ($childStatus != $sameStatus) {
                $isStatusSame = false;
            }
        }

        if ($isStatusSame && !is_null($sameStatus) &&
            $sameStatus != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED
        ) {
            $resultStatus = $sameStatus;
        }

        $this->getProcessor()->getListingProduct()->addData(array(
            'status'                   => $resultStatus,
            'variation_child_statuses' => json_encode($childStatuses),
        ));
    }

    //########################################
}