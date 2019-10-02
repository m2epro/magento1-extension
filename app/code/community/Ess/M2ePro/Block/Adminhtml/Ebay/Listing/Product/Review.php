<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_Ebay_Listing_SourceMode as SourceModeBlock;

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_Review extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $_source;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingProductReview');
        // ---------------------------------------

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $this->_headerText = Mage::helper('M2ePro')->__(
                '%component_name% / Congratulations',
                Mage::helper('M2ePro/Component_Ebay')->getTitle()
            );
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__("Congratulations");
        }

        $this->setTemplate('M2ePro/ebay/listing/product/review.phtml');
    }

    //########################################

    public function getHeaderWidth()
    {
        return 'width:50%;';
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------

        /** @var Ess_M2ePro_Model_Listing $listing */
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing', $this->getRequest()->getParam('listing_id')
        );

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header', '',
            array('listing' => $listing)
        );

        $this->setChild('view_header', $viewHeaderBlock);

        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_ebay_listing/view', array(
            'id' => $this->getRequest()->getParam('listing_id')
            )
        );
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'label'   => Mage::helper('M2ePro')->__('Go To The Listing'),
                'onclick' => 'setLocation(\''.$url.'\');',
                )
            );
        $this->setChild('review', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $addedProductsIds = Mage::helper('M2ePro/Data_Session')->getValue('added_products_ids');
        $url = $this->getUrl(
            '*/adminhtml_ebay_listing/previewItems', array(
            'currentProductId' => $addedProductsIds[0],
            'productIds' => implode(',', $addedProductsIds),
            )
        );
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'label'   => Mage::helper('M2ePro')->__('Preview Added Products Now'),
                'onclick' => 'window.open(\''.$url.'\').focus();',
                'class'   => 'go'
                )
            );
        $this->setChild('preview', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_ebay_listing/view', array(
            'id' => $this->getRequest()->getParam('listing_id'),
            'do_list' => true
            )
        );
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'label' => Mage::helper('M2ePro')->__('List Added Products Now'),
                'onclick' => 'setLocation(\''.$url.'\');',
                'class' => 'save'
                )
            );
        $this->getRequest()->getParam('disable_list', false) && $buttonBlock->setData('style', 'display: none');
        $this->setChild('save_and_list', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        if ($this->getSource() === SourceModeBlock::SOURCE_OTHER) {
            $url = $this->getUrl(
                '*/adminhtml_ebay_listing_other/view', array(
                'account'     => $listing->getAccountId(),
                'marketplace' => $listing->getMarketplaceId(),
                )
            );
            $buttonBlock = $this->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                    'label'   => Mage::helper('M2ePro')->__('Back to 3rd Party Listing'),
                    'onclick' => 'setLocation(\''.$url.'\');',
                    'class' => 'save'
                    )
                );
            $this->setChild('back_to_listing_other', $buttonBlock);
        }

        // ---------------------------------------
    }

    //########################################

    public function setSource($value)
    {
        $this->_source = $value;
    }

    public function getSource()
    {
        return $this->_source;
    }

    //########################################
}
