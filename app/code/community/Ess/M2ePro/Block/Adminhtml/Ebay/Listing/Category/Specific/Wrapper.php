<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Specific_Wrapper
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingCategorySpecificWrapper');
        // ---------------------------------------

        $this->_headerText = Mage::helper('M2ePro')->__('eBay Categories Specifics');

        // ---------------------------------------
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'class'     => 'back back_category_button',
            'onclick'   => 'EbayListingCategorySpecificWrapperHandlerObj.renderPrevCategory();'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('continue', array(
            'id'        => 'save_button',
            'label'     => Mage::helper('M2ePro')->__('Continue'),
            'class'     => 'scalable next continue specifics_buttons',
            'onclick'   => "EbayListingCategorySpecificWrapperHandlerObj.save();"
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('next_category_header_button', array(
            'id'        => 'next_category_header_button',
            'label'     => Mage::helper('M2ePro')->__('Next Category'),
            'class'     => 'next next_category_button specifics_buttons',
            'onclick'   => "EbayListingCategorySpecificWrapperHandlerObj.renderNextCategory();"
        ));
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/category/specific/wrapper.phtml');
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

        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing', $this->getRequest()->getParam('listing_id')
        );

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header','',
            array('listing' => $listing)
        );

        $this->setChild('view_header', $viewHeaderBlock);

        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'next_category_button',
            'class'   => 'next next_category_button specifics_buttons',
            'label'   => Mage::helper('M2ePro')->__('Next Category'),
            'onclick' => 'EbayListingCategorySpecificWrapperHandlerObj.renderNextCategory();'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('next_category_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'class'   => 'scalable next continue specifics_buttons',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
            'onclick' => 'EbayListingCategorySpecificWrapperHandlerObj.save();'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('continue', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'class'   => 'next',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
            'onclick' => 'EbayListingCategorySpecificWrapperHandlerObj.save();'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('popup_confirm_button', $buttonBlock);
        // ---------------------------------------

    }

    //########################################

    protected function _toHtml()
    {
        // ---------------------------------------
        $urls = array();

        $path = 'adminhtml_ebay_listing_categorySettings/stepThreeSaveCategorySpecificsToSession';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            '_current' => true
        ));

        $path = 'adminhtml_ebay_listing_categorySettings/stepThreeGetCategorySpecifics';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            '_current' => true
        ));

        $path = 'adminhtml_ebay_listing_categorySettings/save';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            '_current' => true
        ));

        $path = 'adminhtml_ebay_listing_categorySettings';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'step' => 2,
            '_current' => true,
            'skip_get_suggested' => true
        ));

        $path = 'adminhtml_ebay_listing/review';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            '_current' => true,
        ));

        $urls = json_encode($urls);
        // ---------------------------------------

        // M2ePro_TRANSLATIONS
        // Loading. Please wait
        $text = 'Loading. Please wait';
        $translations[$text] = Mage::helper('M2ePro')->__($text);

        $translations = json_encode($translations);
        // ---------------------------------------

        $javascript = <<<HTML
<script type="text/javascript">

    M2ePro.translator.add({$translations});

    M2ePro.url.add({$urls});

    Event.observe(window, 'load', function() {

        EbayListingCategorySpecificWrapperHandlerObj = new EbayListingCategorySpecificWrapperHandler(
            '{$this->getData('current_category')}',
            new AreaWrapper('specifics_main_container_wrapper')
        );

    });

</script>
HTML;

        return parent::_toHtml() . $javascript;
    }

    //########################################
}