<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_Ebay_ItemDuplicate extends Mage_Adminhtml_Block_Widget
{
    /** @var Ess_M2ePro_Model_Listing_Product */
    public $listingProduct;

    //########################################

    public function __construct(array $args = array())
    {
        parent::__construct($args);

        if (!empty($args['listing_product'])) {
            $this->listingProduct = $args['listing_product'];
        }

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingViewEbayItemDuplicate');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/view/ebay/item_duplicate.phtml');
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $data = array(
            'class'   => 'close_button',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'Windows.getFocusedWindow().close();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('close_button', $buttonBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}
