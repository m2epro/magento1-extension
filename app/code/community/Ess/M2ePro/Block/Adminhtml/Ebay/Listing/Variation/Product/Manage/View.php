<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Variation_Product_Manage_View
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $listingProductId;

    //########################################

    /**
     * @param mixed $listingProductId
     * @return $this
     */
    public function setListingProductId($listingProductId)
    {
        $this->listingProductId = $listingProductId;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getListingProductId()
    {
        return $this->listingProductId;
    }

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/ebay/listing/variation/product/manage/view.phtml');

        return $this;
    }

    protected function _toHtml()
    {
        $data = array(
            'style' => 'float: right; margin-top: 7px; ',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'EbayListingEbayGridHandlerObj.variationProductManageHandler.closeManageVariationsPopup()'
        );
        $closeBtn = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);

        $additionalJavascript = <<<HTML
<script type="text/javascript">
    EbayListingEbayGridHandlerObj.variationProductManageHandler.loadVariationsGrid(true);
</script>
HTML;

        return parent::_toHtml() .
        $additionalJavascript .
        $closeBtn->toHtml();
    }
}