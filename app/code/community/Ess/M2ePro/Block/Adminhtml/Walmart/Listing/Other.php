<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Cron_Task_Walmart_Listing_SynchronizeInventory_ProcessingRunner as ProcessingRunner;

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Other extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('walmartListingOther');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_listing_other';

        $this->_headerText = '';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if ($this->getRequest()->getParam('back') !== null) {
            $url = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_walmart_listing/index');
            $this->_addButton(
                'back',
                array(
                    'label'   => Mage::helper('M2ePro')->__('Back'),
                    'onclick' => "CommonObj.back_click('{$url}')",
                    'class'   => 'back'
                )
            );
        }

        $label = 'Reset Unmanaged Listings';
        $disabled = false;

        /** @var Ess_M2ePro_Model_Lock_Item_Manager $lockItemManager */
        $lockItemManager = Mage::getModel(
            'M2ePro/Lock_Item_Manager',
            array(
                'nick' => ProcessingRunner::LOCK_ITEM_PREFIX
            )
        );

        if ($lockItemManager->isExist()) {
            $label = 'Products Import Is in Progress';
            $disabled = true;
        }

        $this->_addButton(
            'reset_other_listings',
            array(
                'label'    => Mage::helper('M2ePro')->__($label),
                'onclick'  => 'ListingOtherObj.showResetPopup()',
                'class'    => 'scalable',
                'disabled' => $disabled
            )
        );
    }

    //########################################

    protected function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_other_help');

        $javascript = <<<HTML
<script type="text/javascript">
    ListingOtherObj = new ListingOther();
</script>
HTML;

        return $helpBlock->toHtml()
            . parent::_toHtml()
            . $this->getResetPopupHtml()
            . $javascript;
    }

    protected function getResetPopupHtml()
    {
        $helper = Mage::helper('M2ePro');

        $url = $this->getUrl('*/adminhtml_walmart_listing_other/reset');
        $yesButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
            array(
                'label'   => Mage::helper('M2ePro')->__('Yes'),
                'onclick' => "ListingOtherObj.resetPopupYesClick('{$url}')"
            )
        );
        $noButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
            array(
                'label'   => Mage::helper('M2ePro')->__('No'),
                'onclick' => 'Windows.getFocusedWindow().close()'
            )
        );

        return <<<HTML
<div id="reset_other_listings_popup_content" style="display: none">
    <div style="margin: 10px; height: 100px">
        <h3>{$helper->__('Confirm the Unmanaged Listings reset')}</h3>
        <p>{$helper->__(
            'This action will remove all the items from Walmart Unmanaged Listings.
         It will take some time to import them again.'
        )}</p>
        <p>{$helper->__('Do you want to reset the Unmanaged Listings?')}</p>
    </div>

    <div class="clear"></div>
    <div class="right">
        {$noButton->toHtml()}
        <div style="display: inline-block;"></div>
        {$yesButton->toHtml()}
    </div>
    <div class="clear"></div>
</div>
HTML;
    }

    //########################################
}
