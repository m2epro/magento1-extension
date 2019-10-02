<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Variation_Product_Manage_Tabs_Variations
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $_listingProductId;

    //########################################

    /**
     * @param mixed $listingProductId
     * @return $this
     */
    public function setListingProductId($listingProductId)
    {
        $this->_listingProductId = $listingProductId;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getListingProductId()
    {
        return $this->_listingProductId;
    }

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/walmart/listing/variation/product/manage/tabs/variations.phtml');
    }
}
