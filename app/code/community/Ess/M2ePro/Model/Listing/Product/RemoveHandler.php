<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_Product_RemoveHandler
{
    /** @var Ess_M2ePro_Model_Listing_Product */
    protected $_listingProduct = null;

    //########################################

    public function __construct($args)
    {
        if (empty($args['listing_product'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Listing Product is not defined.');
        }

        $this->_listingProduct = $args['listing_product'];
    }

    //########################################

    public function process()
    {
        $this->eventBeforeProcess();

        if (!$this->_listingProduct->isNotListed()) {
            $this->_listingProduct->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)->save();
        }

        $this->_listingProduct->deleteInstance();
        $this->_listingProduct->isDeleted(true);

        $this->eventAfterProcess();
    }

    //########################################

    protected function eventBeforeProcess()
    {
        return null;
    }

    protected function eventAfterProcess()
    {
        return null;
    }

    //########################################
}
