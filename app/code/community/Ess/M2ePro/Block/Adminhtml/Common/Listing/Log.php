<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingLog');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_listing_log';
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

    /** @var Ess_M2ePro_Model_Listing $listing */
    protected $listing = NULL;

    /**
     * @return Ess_M2ePro_Model_Listing|null
     */
    public function getListing()
    {
        if (is_null($this->listing)) {
            $this->listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject(
                'Listing', $this->getListingId()
            );
        }

        return $this->listing;
    }

    //########################################

    public function getListingProductId()
    {
        return $this->getRequest()->getParam('listing_product_id', false);
    }

    // ---------------------------------------

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    protected $listingProduct = NULL;

    /**
     * @return Ess_M2ePro_Model_Listing_Product|null
     */
    public function getListingProduct()
    {
        if (is_null($this->listingProduct)) {
            $this->listingProduct = Mage::helper('M2ePro/Component')
                ->getUnknownObject('Listing_Product', $this->getListingProductId());
        }

        return $this->listingProduct;
    }

    //########################################

    protected function _beforeToHtml()
    {
        // Set header text
        // ---------------------------------------
        $this->_headerText = '';

        if ($this->getListingId()) {

            $listing = $this->getListing();

            if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
                $component =  Mage::helper('M2ePro/Component')->getComponentTitle($listing->getComponentMode());
                $this->_headerText = Mage::helper('M2ePro')->__(
                    'Log For %component_name% Listing "%listing_title%"',
                    $component, $this->escapeHtml($listing->getTitle())
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

            if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
                $component =  Mage::helper('M2ePro/Component')->getComponentTitle($listing->getComponentMode());
                $this->_headerText = Mage::helper('M2ePro')->__(
                    'Log For Product "%product_name%" (ID:%product_id%) Of %component_name% Listing "%listing_title%"',
                    $this->escapeHtml($onlineTitle),
                    $listingProduct->getProductId(),
                    $component,
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

        if ($this->_headerText != '') {
            $this->addButton('show_general_log', array(
                'label'     => Mage::helper('M2ePro')->__('Show General Log'),
                'onclick'   => 'setLocation(\'' .$this->getUrl('*/adminhtml_common_log/listing', array(
                        'channel' => $this->getRequest()->getParam('channel',
                            Mage::helper('M2ePro/View_Common_Component')->getDefaultComponent())
                    )).'\')',
                'class'     => 'button_link'
            ));
        } else {
            $this->setTemplate('M2ePro/widget/grid/container/only_content.phtml');
        }
    }

    //########################################
}