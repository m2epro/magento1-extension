<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Search extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartListingSearch');
        // ---------------------------------------
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $listingType = $this->getRequest()->getParam('listing_type', false);
        $gridBlock = $listingType == Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher::LISTING_TYPE_LISTING_OTHER
            ? $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_search_other_grid')
            : $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_search_m2ePro_grid');

        $this->setChild('help', $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_search_help'));
        $this->setChild('grid', $gridBlock);
    }

    protected function _toHtml()
    {
        $switchersSettings = array(
            'component_mode'  => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'controller_name' => $this->getRequest()->getControllerName(),
            'action_name'     => 'index',
            'action_params'   => array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Walmart_ManageListings::TAB_ID_SEARCH
            )
        );

        /** @var Ess_M2ePro_Block_Adminhtml_Marketplace_Switcher $marketplaceSwitcher */
        $marketplaceSwitcher = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_marketplace_switcher', '', $switchersSettings
        );
        $marketplaceSwitcher->setUseConfirm(false);

        /** @var Ess_M2ePro_Block_Adminhtml_Account_Switcher $accountSwitcher */
        $accountSwitcher = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_account_switcher', '', $switchersSettings
        );
        $accountSwitcher->setUseConfirm(false);

        /** @var Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher $searchSwitcher */
        $searchSwitcher = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_search_switcher', '', $switchersSettings
        );

        if (!Mage::helper('M2ePro/View_Walmart')->is3rdPartyShouldBeShown()) {
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
               $this->getTemplatesButtonJavascript() .
               parent::_toHtml() .
               $this->getChildHtml('help') .
               $this->getChildHtml('grid');
    }

    //########################################

    protected function getTemplatesButtonJavascript()
    {
        $data = array(
            'target_css_class' => 'templates-drop-down',
            'items'            => $this->getTemplatesButtonDropDownItems()
        );
        $dropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $dropDownBlock->setData($data);

        return $dropDownBlock->toHtml();
    }

    protected function getTemplatesButtonDropDownItems()
    {
        $items = array();

        $filter = base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Walmart::NICK);

        // ---------------------------------------
        $url = $this->getUrl('*/adminhtml_walmart_template_sellingFormat/index', array('filter' => $filter));
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('Selling Policies'),
            'target' => '_blank'
        );
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl('*/adminhtml_walmart_template_synchronization/index', array('filter' => $filter));
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('Synchronization Policies'),
            'target' => '_blank'
        );
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_walmart_template_description/index'
        );
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('Description Policies'),
            'target' => '_blank'
        );
        // ---------------------------------------

        return $items;
    }

    //########################################
}