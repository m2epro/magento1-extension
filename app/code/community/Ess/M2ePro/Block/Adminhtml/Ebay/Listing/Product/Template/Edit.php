<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_Template_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingProductTemplateEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_template';
        $this->_mode = 'edit';
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        //------------------------------

        $this->setTemplate('M2ePro/widget/form/container/simplified.phtml');
    }

    // ####################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        //------------------------------
        $data = array(
            'allowed_tabs' => $this->getAllowedTabs()
        );
        $tabs = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_template_edit_tabs');
        $tabs->addData($data);
        $this->setChild('tabs', $tabs);
        //------------------------------

        return $this;
    }

    // ####################################

    public function getAllowedTabs()
    {
        if (!isset($this->_data['allowed_tabs']) || !is_array($this->_data['allowed_tabs'])) {
            return array();
        }

        return $this->_data['allowed_tabs'];
    }

    // ####################################

    public function getFormHtml()
    {
        $html = '';
        $tabs = $this->getChild('tabs');

        //------------------------------
        $html .= $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_template_switcher_initialization')
            ->toHtml();
        //------------------------------

        // initiate template switcher url
        //------------------------------
        $html .= Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Switcher::getSwitcherUrlHtml();
        //------------------------------

        //------------------------------
        $html .= <<<HTML
<script type="text/javascript">
    EbayListingTemplateSwitcherHandlerObj.checkAttributesAvailability = true;
</script>
HTML;
        //------------------------------

        // hide tabs selector if only one tab is allowed for displaying
        //------------------------------
        if (count($this->getAllowedTabs()) == 1) {
            $html .= <<<HTML
<script type="text/javascript">
    $('{$tabs->getId()}').hide();
</script>
HTML;
        }
        //------------------------------

        return $html . $tabs->toHtml() . parent::getFormHtml();
    }

    // ####################################

    public function getButtonsHtml($area = NULL)
    {
        $html = parent::getButtonsHtml($area);

        if ($area != 'footer') {
            return $html;
        }

        $cancelWord = Mage::helper('M2ePro')->__('Cancel');

        //------------------------------
        $callback = 'function(params) { EbayListingSettingsGridHandlerObj.saveSettings(params); }';
        $data = array(
            'class'   => 'save',
            'label'   => Mage::helper('M2ePro')->__('Save'),
            'onclick' => 'EbayListingTemplateSwitcherHandlerObj.saveSwitchers(' . $callback . ')',
        );
        $saveButtonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        //------------------------------

        //------------------------------
        $html .= '<div style="height: 25px;"></div>';
        //------------------------------

        $html .= <<<HTML
<div style="position: absolute; text-align: right; bottom: 0; padding: 10px 0 10px 0; background: #fff; width: 950px;">
    <span style="padding-left: 10px;">
        <a href="javascript:void(0);" onclick="Windows.getFocusedWindow().close();">{$cancelWord}</a>
    </span>
    <span style="padding-left: 10px;">{$saveButtonBlock->toHtml()}</span>
</div>
HTML;

        return $html;
    }

    public function hasFooterButtons()
    {
        return true;
    }

    // ####################################
}