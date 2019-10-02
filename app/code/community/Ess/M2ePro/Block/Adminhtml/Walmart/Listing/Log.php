<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /** @var Ess_M2ePro_Model_Listing $_listing */
    protected $_listing;

    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingLog');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_listing_log';
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    //########################################

    public function getListingId()
    {
        return $this->getRequest()->getParam('id', false);
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing|null
     */
    public function getListing()
    {
        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject(
                'Listing', $this->getListingId()
            );
        }

        return $this->_listing;
    }

    //########################################

    public function getListingProductId()
    {
        return $this->getRequest()->getParam('listing_product_id', false);
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing_Product|null
     */
    public function getListingProduct()
    {
        if ($this->_listingProduct === null) {
            $this->_listingProduct = Mage::helper('M2ePro/Component')
                                         ->getUnknownObject('Listing_Product', $this->getListingProductId());
        }

        return $this->_listingProduct;
    }

    //########################################

    protected function _toHtml()
    {
        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
            'Description' => Mage::helper('M2ePro')->__('Description')
            )
        );

        $javascript = <<<JAVASCIRPT

<script type="text/javascript">

    M2ePro.translator.add({$translations});

    Event.observe(window, 'load', function() {
        CommonHandlerObj = new CommonHandler();
        LogHandlerObj = new LogHandler();
    });

</script>

JAVASCIRPT;

        return $javascript . parent::_toHtml();
    }

    protected function _beforeToHtml()
    {
        // Set header text
        // ---------------------------------------
        $this->_headerText = '';

        if ($this->getListingId()) {
            $listing = $this->getListing();

            if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
                $this->_headerText = Mage::helper('M2ePro')->__(
                    '%component_name% / Log For Listing "%listing_title%"',
                    Mage::helper('M2ePro/Component_Walmart')->getTitle(),
                    $this->escapeHtml($listing->getTitle())
                );
            } else {
                $this->_headerText = Mage::helper('M2ePro')->__(
                    'Log For Listing "%listing_title%"',
                    $this->escapeHtml($listing->getTitle())
                );
            }
        } else if ($this->getListingProductId()) {
            $listingProduct = $this->getListingProduct();
            $listing = $listingProduct->getListing();

            $onlineTitle = $listingProduct->getOnlineTitle();
            if (empty($onlineTitle)) {
                $onlineTitle = $listingProduct->getMagentoProduct()->getName();
            }

            if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
                $this->_headerText = Mage::helper('M2ePro')->__(
                    '%component_name% / Log For Product "%product_name%"'
                    . ' (ID:%product_id%) Of Listing "%listing_title%"',
                    Mage::helper('M2ePro/Component_Walmart')->getTitle(),
                    $this->escapeHtml($onlineTitle),
                    $listingProduct->getProductId(),
                    $this->escapeHtml($listing->getTitle())
                );
            } else {
                $this->_headerText = Mage::helper('M2ePro')->__(
                    'Log For Product "%product_name%" (ID:%product_id%) Of Listing "%listing_title%"',
                    $this->escapeHtml($onlineTitle),
                    $listingProduct->getProductId(),
                    $this->escapeHtml($listing->getTitle())
                );
            }
        }

        // ---------------------------------------
        $this->addButton(
            'show_general_log', array(
            'label'     => Mage::helper('M2ePro')->__('Show General Log'),
            'onclick'   => 'setLocation(\'' .$this->getUrl('*/adminhtml_walmart_log/listing').'\')',
            'class'     => 'button_link'
            )
        );
    }

    //########################################
}
