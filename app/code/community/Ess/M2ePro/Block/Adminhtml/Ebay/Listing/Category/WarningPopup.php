<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_WarningPopup extends Mage_Adminhtml_Block_Template
{
    public $categoryGridJsHandler;

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setId('ebayListingCategoryWarningPopup');
    }

    //########################################

    public function setCategoryGridJsHandler($handler)
    {
        $this->categoryGridJsHandler = $handler;
    }

    //########################################

    protected function _toHtml()
    {
        $helper = Mage::helper('M2ePro');

        $data = array(
            'class'   => 'next',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
            'onclick' => "{$this->categoryGridJsHandler}.categoryNotSelectedWarningPopupContinueClick();"
        );
        $continueButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);

        return <<<HTML
<div id="next_step_warning_popup_content" style="display: none">

    <div style="margin: 10px; height: 235px">
{$helper->__(
    'You have not specified full Category data for
    <strong><span class="failed_count"></span></strong> of <strong><span class="total_count"></span></strong>
    Product(s).<br><br>

    Products without a Primary eBay Category and Item Specifics <b>will not be added to M2E Pro Listing</b>
    as they cannot be listed on the Channel.
    eBay requires submitting full Category data when listing a new Item.<br><br>

    To set Primary eBay Category to <strong><span class="failed_count"></span></strong> Product(s) now,
    click <i>Cancel</i> and complete the configurations. Otherwise, click <i>Continue</i>.
    You will be able to add these Products later using the Add Products button on the View Listing page.'
)}
    </div>

    <div style="text-align: right; margin-bottom: 10px;">
        <a href="javascript:"
            onclick="Windows.getFocusedWindow().close();">{$helper->__('Cancel')}</a>
        &nbsp;&nbsp;&nbsp;&nbsp;
        {$continueButton->toHtml()}
    </div>

</div>
HTML;
    }

    //########################################
}
