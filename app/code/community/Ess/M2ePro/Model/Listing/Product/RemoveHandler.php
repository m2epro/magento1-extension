<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_Product_RemoveHandler
{
    /** @var Ess_M2ePro_Model_Listing_Product */
    protected $listingProduct = NULL;

    //########################################

    public function __construct($args)
    {
        if (empty($args['listing_product'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Listing Product is not defined.');
        }

        $this->listingProduct = $args['listing_product'];
    }

    //########################################

    public function process()
    {
        $this->eventBeforeProcess();

        if (!$this->listingProduct->isNotListed()) {
            $this->listingProduct->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)->save();
        }

        $this->listingProduct->deleteInstance();
        $this->listingProduct->isDeleted(true);

        $this->eventAfterProcess();
    }

    //########################################

    protected function eventBeforeProcess() {}

    protected function eventAfterProcess() {}

    //########################################
}