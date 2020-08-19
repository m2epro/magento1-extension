<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Add_SourceMode as SourceModeBlock;

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Add_Review extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $_source;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('listingProductReview');

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $this->_headerText = Mage::helper('M2ePro')->__(
                '%component_name% / Congratulations',
                Mage::helper('M2ePro/Component_Walmart')->getTitle()
            );
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Congratulations');
        }

        $this->setTemplate('M2ePro/walmart/listing/product/add/review.phtml');
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

        /** @var Ess_M2ePro_Model_Listing $listing */
        $listing = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Listing', $this->getRequest()->getParam('id')
        );

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header', '',
            array('listing' => $listing)
        );

        $this->setChild('view_header', $viewHeaderBlock);

        $url = $this->getUrl(
            '*/*/viewListing', array(
            '_current' => true,
            'id' => $this->getRequest()->getParam('id')
            )
        );

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                    'id'      => 'go_to_the_listing',
                    'label'   => Mage::helper('M2ePro')->__('Review Your Products'),
                    'onclick' => 'setLocation(\'' . $url . '\');',
                    'class'   => 'save'
                )
            );
        $this->setChild('review', $buttonBlock);

        $url = $this->getUrl(
            '*/*/viewListingAndList', array(
            '_current' => true,
            'id' => $this->getRequest()->getParam('id')
            )
        );

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'label'   => Mage::helper('M2ePro')->__('List Added Products Now'),
                'onclick' => 'setLocation(\''.$url.'\');',
                'class' => 'save'
                )
            );
        $this->setChild('list', $buttonBlock);

        if ($this->getSource() === SourceModeBlock::SOURCE_OTHER) {
            $url = $this->getUrl(
                '*/adminhtml_walmart_listing_other/view', array(
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
    }

    //########################################

    public function getComponentTitle()
    {
        return Mage::helper('M2ePro/Component_Walmart')->getChannelTitle();
    }

    //########################################

    public function setSource($source)
    {
        $this->_source = $source;
    }

    public function getSource()
    {
        return $this->_source;
    }

    //########################################
}
