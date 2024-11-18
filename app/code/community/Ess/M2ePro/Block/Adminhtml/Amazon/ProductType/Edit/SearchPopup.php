<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_ProductType_Edit_SearchPopup
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    private $productTypes = array();

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonProductTypeEditSearchPopup');
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        // ---------------------------------------
        $this->setTemplate('M2ePro/amazon/productType/edit/search_popup.phtml');
        // ---------------------------------------
    }

    protected function _toHtml()
    {
        // ---------------------------------------
        $data = array(
            'id'      => 'product_type_confirm',
            'class'   => '',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
        );
        $doneButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        // ---------------------------------------

        $cancelWord = Mage::helper('M2ePro')->__('Cancel');

        $buttonsContainer = <<< HTML
<div id="chooser_buttons_container">
    <a href="javascript:void(0)"
       onclick="AmazonProductTypeObj.cancelPopUp()">{$cancelWord}</a>
    &nbsp;&nbsp;&nbsp;&nbsp;
    {$doneButton->toHtml()}
    <script type="text/javascript">amazonProductTypeSearchPopupTabsJsTabs.moveTabContentInDest();</script>
</div>
HTML;

        /** @var Ess_M2ePro_Block_Adminhtml_Amazon_ProductType_Edit_SearchPopup_Tabs $tabsBlock */
        $tabsBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_productType_edit_searchPopup_tabs'
        );

        return parent::_toHtml() .
            $tabsBlock->toHtml() .
            '<div id="chooser_tabs_container"></div>' .
            $buttonsContainer;
    }


    /**
     * @param array $productTypes
     * @return $this
     */
    public function setProductTypes(array $productTypes)
    {
        $this->productTypes = $productTypes;

        return $this;
    }

    /**
     * @return array
     */
    public function getProductTypes()
    {
        return $this->productTypes;
    }

    protected function _beforeToHtml()
    {
        Mage::helper('M2ePro/View')->getJsTranslatorRenderer()->addTranslations(
            array(
                'product_type_configured' => Mage::helper('M2ePro')->__(
                    <<<HTML
<p>This Product Type is already configured in your M2E Pro. There's no need to go through the setup process again. If
you wish to review or adjust the settings, please click <a target="_blank" href="exist_product_type_url">here</a>.</p>
HTML
                )
            )
        );

        return parent::_beforeToHtml();
    }

}
