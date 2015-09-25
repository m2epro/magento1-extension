<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Listing_Product_Variation_Updater
{
    // ########################################

    abstract public function process(Ess_M2ePro_Model_Listing_Product $listingProduct);

    // ########################################

    public function beforeMassProcessEvent() {}

    public function afterMassProcessEvent() {}

    // ########################################
}