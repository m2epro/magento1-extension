<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Same_Chooser extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->_headerText = Mage::helper('M2ePro')->__('Set Category (All Products same Category)');
        $this->_listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing', $this->getRequest()->getParam('listing_id')
        );

        $this->_addButton(
            'back', array(
                'label'   => Mage::helper('M2ePro')->__('Back'),
                'class'   => 'back',
                'onclick' => 'setLocation(\'' . $this->getUrl('*/*/*', array('_current' => true, 'step' => 1)) . '\');'
            )
        );

        $url = $this->getUrl(
            '*/adminhtml_ebay_listing_categorySettings/exitToListing',
            array('listing_id' => $this->getRequest()->getParam('listing_id'))
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

        $onClick = <<<JS
EbayListingCategoryObj.modeSameSubmitData(
    '{$this->getUrl('*/*/*', array('step' => 2,'_current' => true))}'
);
JS;
        $this->_addButton(
            'next', array(
                'id'      => 'next',
                'label'   => Mage::helper('M2ePro')->__('Continue'),
                'class'   => 'scalable next',
                'onclick' => $onClick
            )
        );
    }

    //########################################

    protected function _toHtml()
    {
        $helper = Mage::helper('M2ePro');
        $urls = array_merge(
            $helper->getControllerActions('adminhtml_ebay_listing_categorySettings', array('_current' => true)),
            $helper->getControllerActions('adminhtml_ebay_category', array('_current' => true)),
            $helper->getControllerActions('adminhtml_ebay_accountStoreCategory')
        );

        $path = 'adminhtml_ebay_listing_categorySettings';
        $urls[$path] = $this->getUrl('*/' . $path, array('step' => 3, '_current' => true));

        $path = 'adminhtml_ebay_listing_categorySettings/review';
        $urls[$path] = $this->getUrl('*/' . $path, array('_current' => true));

        $urls = $helper->jsonEncode($urls);

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header', '',
            array('listing' => $this->_listing)
        );

        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_category_chooser');
        $chooserBlock->setMarketplaceId($this->_listing->getMarketplaceId());
        $chooserBlock->setAccountId($this->_listing->getAccountId());
        $chooserBlock->setCategoriesData($this->getData('categories_data'));

        return <<<HTML
<script>
    EbayListingCategoryObj = new EbayListingCategory(null);
    M2ePro.url.add($urls);
    
    Event.observe(window, 'load', function() {

        EbayTemplateCategoryChooserObj.confirmSpecificsCallback = function() {
            var typeMain = M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN');
            this.selectedCategories[typeMain].specific = this.selectedSpecifics;
        }.bind(EbayTemplateCategoryChooserObj)
        
        EbayTemplateCategoryChooserObj.resetSpecificsCallback = function() {
            var typeMain = M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN');
            this.selectedCategories[typeMain].specific = this.selectedSpecifics;
        }.bind(EbayTemplateCategoryChooserObj)
    });
    
</script>

<div class="content-header">
    <table cellspacing="0">
        <tr>
            <td style="width:50%;">{$this->getHeaderHtml()}</td>
            <td class="form-buttons">{$this->getButtonsHtml()}</td>
        </tr>
    </table>
</div>

{$viewHeaderBlock->toHtml()}

<div id="ebay_category_chooser">{$chooserBlock->toHtml()}</div>
HTML;
    }

    //########################################
}
