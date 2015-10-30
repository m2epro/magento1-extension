<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Same_Chooser extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingCategorySameChooser');
        // ---------------------------------------

        $this->_headerText = Mage::helper('M2ePro')->__('eBay Same Categories');

        $this->setTemplate('M2ePro/ebay/listing/category/same/chooser.phtml');

        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'class'     => 'back',
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/*', array('_current' => true, 'step' => 1)) . '\');'
        ));

        $onClick = <<<JS
EbayListingCategoryChooserHandlerObj.submitData(
    '{$this->getUrl('*/*/*', array('step' => 2,'_current' => true))}'
);
JS;
        $this->_addButton('next', array(
            'label'     => Mage::helper('M2ePro')->__('Continue'),
            'class'     => 'scalable next',
            'onclick'   => $onClick
        ));
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
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $internalData = $this->getData('internal_data');

        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser');
        $chooserBlock->setMarketplaceId($listingData['marketplace_id']);
        $chooserBlock->setAccountId($listingData['account_id']);

        if (!empty($internalData)) {
            $chooserBlock->setInternalData($internalData);
        }

        $this->setChild('category_chooser', $chooserBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'label' => Mage::helper('adminhtml')->__('Yes'),
            'id'    => 'existing_templates_confirm_button'
        );
        $this->setChild(
            'existing_templates_confirm_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data)
        );
        // ---------------------------------------
        $data = array(
            'label' => Mage::helper('adminhtml')->__('No'),
            'id'    => 'existing_templates_cancel_button'
        );
        $this->setChild(
            'existing_templates_cancel_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data)
        );
        // ---------------------------------------
    }

    //########################################

    protected function _toHtml()
    {
        $parentHtml = parent::_toHtml();

        // ---------------------------------------

        $urls = Mage::helper('M2ePro')->getControllerActions(
            'adminhtml_ebay_listing_categorySettings',
            array(
                '_current' => true
            )
        );

        $path = 'adminhtml_ebay_listing_categorySettings';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'step' => 3,
            '_current' => true
        ));

        $path = 'adminhtml_ebay_listing/review';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            '_current' => true
        ));

        $urls = json_encode($urls);

        // ---------------------------------------

        $js = <<<HTML

<script type="text/javascript">
    M2ePro.url.add($urls);
</script>
HTML;

        // ---------------------------------------

        return <<<HTML
{$parentHtml}
{$js}
HTML;
    }

    //########################################
}