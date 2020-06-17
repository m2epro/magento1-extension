<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Variation_Product_Manage_View
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct;

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

    // ---------------------------------------

    protected function getListingProduct()
    {
        if (empty($this->_listingProduct)) {
            $this->_listingProduct = Mage::helper('M2ePro/Component_Ebay')
                                         ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->_listingProduct;
    }

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/ebay/listing/variation/product/manage/view.phtml');
    }

    //########################################

    protected function _toHtml()
    {
        $javascriptMain = <<<HTML
<script type="text/javascript">
    GridFrameObj = new GridFrame();

    EbayListingEbayGridObj.variationProductManageHandler.loadVariationsGrid(true);
    EbayListingEbayGridObj.variationProductManageHandler.loadDeletedVariationsGrid(true);
</script>
HTML;
        return $javascriptMain . parent::_toHtml();
    }

    //########################################

    public function getDeletedVariations()
    {
        return $this->getListingProduct()->getSetting(
            'additional_data', 'variations_that_can_not_be_deleted'
        );
    }

    //########################################
}
