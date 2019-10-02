<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Search extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingSearch');
        // ---------------------------------------
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $listingType = $this->getRequest()->getParam('listing_type', false);
        $gridBlock = $listingType == Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher::LISTING_TYPE_LISTING_OTHER
            ? $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_search_other_grid')
            : $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_search_m2ePro_grid');

        $this->setChild('help', $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_search_help'));
        $this->setChild('grid', $gridBlock);
    }

    protected function _toHtml()
    {
        $switchersSettings = array(
            'component_mode'  => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'controller_name' => $this->getRequest()->getControllerName(),
            'action_name'     => 'index',
            'action_params'   => array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Ebay_ManageListings::TAB_ID_SEARCH
            )
        );

        /** @var Ess_M2ePro_Block_Adminhtml_Marketplace_Switcher $marketplaceSwitcher */
        $marketplaceSwitcher = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_marketplace_switcher', '',
            $switchersSettings
        );
        $marketplaceSwitcher->setUseConfirm(false);

        /** @var Ess_M2ePro_Block_Adminhtml_Account_Switcher $accountSwitcher */
        $accountSwitcher = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_account_switcher', '',
            $switchersSettings
        );
        $accountSwitcher->setUseConfirm(false);

        /** @var Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher $searchSwitcher */
        $searchSwitcher = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_search_switcher', '',
            $switchersSettings
        );

        if (!Mage::helper('M2ePro/View_Ebay')->isAdvancedMode() ||
            !Mage::helper('M2ePro/View_Ebay')->is3rdPartyShouldBeShown()) {
            $searchSwitcher->showOtherOption = false;
        }

        $filterHtml = <<<HTML
<div class="filter_block">
    {$searchSwitcher->toHtml()}
    {$accountSwitcher->toHtml()}
    {$marketplaceSwitcher->toHtml()}
</div>
HTML;
        return $filterHtml .
               parent::_toHtml() .
               $this->getChildHtml('help') .
               $this->getChildHtml('grid');
    }

    //########################################
}
