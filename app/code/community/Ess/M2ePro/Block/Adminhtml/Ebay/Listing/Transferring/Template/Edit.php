<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Transferring_Template_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingTransferringTemplateEdit');
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
        $parameters = array(
            'allowed_tabs' => $this->getAllowedTabs(),
            'policy_localization' => $this->getData('policy_localization')
        );
        $tabs = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_template_edit_tabs', '',$parameters);
        $tabs->setDestElementId('transferring_policies_block');
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
}