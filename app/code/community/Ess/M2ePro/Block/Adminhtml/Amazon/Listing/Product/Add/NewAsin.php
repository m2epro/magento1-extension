<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Add_SourceMode as SourceModeBlock;

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Add_NewAsin
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    const MODE_SAME     = 'same';
    const MODE_CATEGORY = 'category';
    const MODE_MANUALLY = 'manually';

    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonListingProductAddNewAsin');

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $this->_headerText = Mage::helper('M2ePro')->__(
                '%component_name% / New ASIN/ISBN Creation',
                Mage::helper('M2ePro/Component_Amazon')->getTitle()
            );
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__("New ASIN/ISBN Creation");
        }

        $url = $this->getUrl(
            '*/*/index', array(
                'step'     => 3,
                '_current' => true
            )
        );
        $this->_addButton(
            'back', array(
                'label'   => Mage::helper('M2ePro')->__('Back'),
                'class'   => 'back',
                'onclick' => 'setLocation(\'' . $url . '\');'
            )
        );

        $url = $this->getUrl(
            '*/adminhtml_amazon_listing_productAdd/exitToListing',
            array('id' => $this->getRequest()->getParam('id'))
        );
        $confirm =
            $this->__('Are you sure?') . '\n\n'
            . $this->__('All unsaved changes will be lost and you will be returned to the Listings grid.');
        $this->_addButton(
            'exit_to_listing',
            array(
                'id' => 'exit_to_listing',
                'label' => Mage::helper('M2ePro')->__('Cancel'),
                'onclick' => "confirmSetLocation('$confirm', '$url');",
                'class' => 'scalable'
            )
        );

        $this->_addButton(
            'next', array(
                'label'   => Mage::helper('M2ePro')->__('Continue'),
                'class'   => 'scalable next',
                'onclick' => "descriptionTemplateModeFormSubmit()"
            )
        );

        $this->setTemplate('M2ePro/amazon/listing/product/add/new_asin.phtml');
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

        $listing = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
            'Listing', $this->getRequest()->getParam('id')
        );

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header', '',
            array('listing' => $listing)
        );

        $this->setChild('view_header', $viewHeaderBlock);

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                    'label'   => Mage::helper('M2ePro')->__('Continue'),
                    'onclick' => '',
                )
            );
        $this->setChild('mode_same_remember_pop_up_confirm_button', $buttonBlock);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     * @throws Exception
     */
    public function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component_Amazon')
                ->getObject('Listing', $listingId);
        }

        return $this->_listing;
    }

    //########################################

    public function getProductsIds()
    {
        return $this->getListing()->getSetting('additional_data', 'adding_new_asin_listing_products_ids');
    }

    //########################################

    public function getDescriptionTemplateMode()
    {
        $listingAdditionalData = $this->getListing()->getData('additional_data');
        $listingAdditionalData = Mage::helper('M2ePro')->jsonDecode($listingAdditionalData);

        $mode = self::MODE_SAME;

        if (isset($listingAdditionalData['source']) &&
            $listingAdditionalData['source'] == SourceModeBlock::SOURCE_CATEGORIES
        ) {
            $mode = self::MODE_CATEGORY;
        }

        if (!empty($listingAdditionalData['new_asin_mode'])) {
            $mode = $listingAdditionalData['new_asin_mode'];
        }

        return $mode;
    }

    //########################################
}
