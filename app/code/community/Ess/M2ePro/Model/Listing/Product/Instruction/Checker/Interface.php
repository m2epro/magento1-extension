<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

interface Ess_M2ePro_Model_Synchronization_Instructions_Checker_Interface
{
    //########################################

    public function isAllowed(Ess_M2ePro_Model_Listing_Product $listingProduct);

    public function check(Ess_M2ePro_Model_Listing_Product $listingProduct);

    //########################################
}