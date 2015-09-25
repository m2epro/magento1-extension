<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Request_Shipping
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Request_Abstract
{
    // ########################################

    public function getData()
    {
        if (!$this->getConfigurator()->isShippingAllowed()) {
            return array();
        }

        $data = array();

        // ---------------------
        if (!isset($this->validatorsData['shipping_standard_rate'])) {
            $rate = $this->getBuyListingProduct()->getListingSource()->getShippingStandardRate();
            !is_null($rate) && ($this->validatorsData['shipping_standard_rate'] = $rate);
        }

        if (isset($this->validatorsData['shipping_standard_rate'])) {
            $data['shipping_standard_rate'] = $this->validatorsData['shipping_standard_rate'];
        }
        // ---------------------

        // ---------------------
        if (!isset($this->validatorsData['shipping_expedited_mode'])) {
            $mode = $this->getBuyListingProduct()->getListingSource()->getShippingExpeditedMode();
            !is_null($mode) && ($this->validatorsData['shipping_expedited_mode'] = $mode);
        }

        if (isset($this->validatorsData['shipping_expedited_mode'])) {
            $data['shipping_expedited_mode'] = $this->validatorsData['shipping_expedited_mode'];
        }

        if (!isset($this->validatorsData['shipping_expedited_rate'])) {
            $rate = $this->getBuyListingProduct()->getListingSource()->getShippingExpeditedRate();
            !is_null($rate) && ($this->validatorsData['shipping_expedited_rate'] = $rate);
        }

        if (isset($this->validatorsData['shipping_expedited_rate'])) {
            $data['shipping_expedited_rate'] = $this->validatorsData['shipping_expedited_rate'];
        }
        // ---------------------

        // ---------------------
        if (!isset($this->validatorsData['shipping_one_day_mode'])) {
            $mode = $this->getBuyListingProduct()->getListingSource()->getShippingOneDayMode();
            !is_null($mode) && ($this->validatorsData['shipping_one_day_mode'] = $mode);
        }

        if (isset($this->validatorsData['shipping_one_day_mode'])) {
            $data['shipping_one_day_mode'] = $this->validatorsData['shipping_one_day_mode'];
        }

        if (!isset($this->validatorsData['shipping_one_day_rate'])) {
            $rate = $this->getBuyListingProduct()->getListingSource()->getShippingOneDayRate();
            !is_null($rate) && ($this->validatorsData['shipping_one_day_rate'] = $rate);
        }

        if (isset($this->validatorsData['shipping_one_day_rate'])) {
            $data['shipping_one_day_rate'] = $this->validatorsData['shipping_one_day_rate'];
        }
        // ---------------------

        // ---------------------
        if (!isset($this->validatorsData['shipping_two_day_mode'])) {
            $mode = $this->getBuyListingProduct()->getListingSource()->getShippingTwoDayMode();
            !is_null($mode) && ($this->validatorsData['shipping_two_day_mode'] = $mode);
        }

        if (isset($this->validatorsData['shipping_two_day_mode'])) {
            $data['shipping_two_day_mode'] = $this->validatorsData['shipping_two_day_mode'];
        }

        if (!isset($this->validatorsData['shipping_two_day_rate'])) {
            $rate = $this->getBuyListingProduct()->getListingSource()->getShippingTwoDayRate();
            !is_null($rate) && ($this->validatorsData['shipping_one_day_rate'] = $rate);
        }

        if (isset($this->validatorsData['shipping_two_day_rate'])) {
            $data['shipping_two_day_rate'] = $this->validatorsData['shipping_two_day_rate'];
        }
        // ---------------------

        return $data;
    }

    // ########################################
}