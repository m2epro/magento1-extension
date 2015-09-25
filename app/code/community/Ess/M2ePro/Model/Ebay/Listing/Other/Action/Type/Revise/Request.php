<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Revise_Request
    extends Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Request
{
    // ########################################

    public function getActionData()
    {
        $data = array(
            'item_id' => $this->getEbayListingOther()->getItemId()
        );

        return array_merge(
            $data,
            $this->getRequestSelling()->getData(),
            $this->getRequestDescription()->getData()
        );
    }

    protected function prepareFinalData(array $data)
    {
        $data = $this->removeNodesIfItemHasTheSaleOrBid($data);

        return parent::prepareFinalData($data);
    }

    // ########################################

    private function removeNodesIfItemHasTheSaleOrBid(array $data)
    {
        if (!isset($data['title']) && !isset($data['subtitle'])) {
            return $data;
        }

        $deleteFlag = (is_null($this->getEbayListingOther()->getOnlineQtySold())
                           ? false
                           : $this->getEbayListingOther()->getOnlineQtySold() > 0)
                      ||
                      ($this->getEbayListingOther()->getOnlineBids() > 0);

        if ($deleteFlag) {
            unset($data['title'], $data['subtitle']);
        }

        return $data;
    }

    // ########################################
}