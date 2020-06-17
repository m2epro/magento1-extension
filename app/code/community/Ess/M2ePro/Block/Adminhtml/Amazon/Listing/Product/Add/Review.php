<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Add_Review extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('listingProductReview');

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $this->_headerText = Mage::helper('M2ePro')->__(
                '%component_name% / Congratulations',
                Mage::helper('M2ePro/Component_Amazon')->getTitle()
            );
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__("Congratulations");
        }

        $this->setTemplate('M2ePro/amazon/listing/product/add/review.phtml');
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

        $listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject(
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
                'id'       => $this->getRequest()->getParam('id')
            )
        );

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                    'label'   => Mage::helper('M2ePro')->__('Review Your Products'),
                    'onclick' => 'setLocation(\'' . $url . '\');',
                    'class'   => 'save'
                )
            );
        $this->setChild('review', $buttonBlock);

        $url = $this->getUrl(
            '*/*/viewListingAndList', array(
                '_current' => true,
                'id'       => $this->getRequest()->getParam('id')
            )
        );

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                    'label'   => Mage::helper('M2ePro')->__('List Added Products Now'),
                    'onclick' => 'setLocation(\'' . $url . '\');',
                    'class'   => 'save'
                )
            );
        $this->setChild('list', $buttonBlock);
    }

    //########################################

    public function getComponentTitle()
    {
        return Mage::helper('M2ePro/Component_Amazon')->getChannelTitle();
    }

    //########################################
}
