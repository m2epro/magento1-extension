<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing getListing()
 */
class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Transferring_Products extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct(array $args = array())
    {
        parent::__construct();
        $this->addData($args);

        $this->setId('amazonListingTransferringProducts');
    }

    //########################################

    protected function _toHtml()
    {
        $helper = Mage::helper('M2ePro');
        $translations = $helper->jsonEncode(
            array(
                'Sell on Another Marketplace' => $helper->__('Sell on Another Marketplace'),
                'Adding has been completed' => $helper->__('Adding has been completed'),
                'Adding Products in process. Please wait...' => $helper->__(
                    'Adding Products in process. Please wait...'
                )
            )
        );

        $urls = $helper->jsonEncode(
            $helper->getControllerActions(
                'adminhtml_amazon_listing_transferring',
                array('listing_id' => $this->getListing()->getId())
            )
        );

        /** @var Ess_M2ePro_Model_Amazon_Listing_Transferring $transferring */
        $transferring = Mage::getModel('M2ePro/Amazon_Listing_Transferring');
        $transferring->setListing($this->getListing());

        $products = Mage::helper('M2ePro')->jsonEncode($transferring->getProductsIds());
        $successUrl = $this->getUrl(
            '*/adminhtml_amazon_listing/view',
            array(
                'id' => $transferring->getTargetListingId()
            )
        );

        return <<<HTML
<script>
    M2ePro.translator.add({$translations});
    M2ePro.url.add($urls);

    Event.observe(window,'load',function() {
        AmazonListingTransferringObj = new AmazonListingTransferring({$this->getListing()->getId()});
        AmazonListingTransferringObj.addProducts(
            'transferring_progress_bar',
            {$products},
            function() {
                window.location = '{$successUrl}';
            }
        );
    });
</script>

<div id="transferring_progress_bar"></div>
HTML;
    }

    //########################################
}
