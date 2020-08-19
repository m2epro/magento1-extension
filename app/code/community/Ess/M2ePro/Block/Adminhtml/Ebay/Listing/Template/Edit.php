<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingTemplateEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_template';
        $this->_mode = 'edit';
        // ---------------------------------------

        // ---------------------------------------
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('ebay_listing');
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Ebay')->getTitle();

            if ($listing) {
                $this->_headerText = Mage::helper('M2ePro')->__(
                    'Edit %component_name% Listing Settings "%listing_title%"', $componentName,
                    $listing->getTitle()
                );
            } else {
                $this->_headerText = Mage::helper('M2ePro')->__(
                    '%component_name% / Creating A New M2E Pro Listing',
                    $componentName
                );
            }
        } else {
            if ($listing) {
                $this->_headerText = Mage::helper('M2ePro')->__(
                    'Edit Listing Settings "%listing_title%"',
                    $listing->getTitle()
                );
            } else {
                $this->_headerText = Mage::helper('M2ePro')->__('Creating A New M2E Pro Listing');
            }
        }

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

        if ($listing) {
            // ---------------------------------------
            $url = $this->getUrl('*/adminhtml_ebay_listing/view', array('id' => $listing->getId()));

            if ($this->getRequest()->getParam('back')) {
                $url = Mage::helper('M2ePro')->getBackUrl();
            }

            $this->_addButton(
                'back', array(
                    'label'     => Mage::helper('M2ePro')->__('Back'),
                    'onclick'   => 'CommonObj.back_click(\'' . $url . '\')',
                    'class'     => 'back'
                )
            );
            // ---------------------------------------

            // ---------------------------------------
            $backUrl = Mage::helper('M2ePro')->makeBackUrlParam(
                '*/adminhtml_ebay_listing/view', array('id' => $listing->getId())
            );
            $url = $this->getUrl(
                '*/adminhtml_ebay_template/saveListing',
                array(
                    'id' => $listing->getId(),
                    'back' => $backUrl
                )
            );
            $callback = 'function(params) { CommonObj.postForm(\''.$url.'\', params); }';
            $this->_addButton(
                'save', array(
                    'label'     => Mage::helper('M2ePro')->__('Save'),
                    'onclick'   => 'EbayListingTemplateSwitcherObj.saveSwitchers(' . $callback . ')',
                    'class'     => 'save'
                )
            );
            // ---------------------------------------

            // ---------------------------------------
            $backUrl = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebay_template/editListing');
            $url = $this->getUrl(
                '*/adminhtml_ebay_template/saveListing',
                array(
                    'id' => $listing->getId(),
                    'back' => $backUrl
                )
            );

            $callback = 'function(params) { CommonObj.postForm(\''.$url.'\', params); }';
            $this->_addButton(
                'save_and_continue', array(
                    'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                    'onclick'   => 'EbayListingTemplateSwitcherObj.saveSwitchers(' . $callback . ')',
                    'class'     => 'save'
                )
            );
            // ---------------------------------------
        }

        if (!$listing) {
            // ---------------------------------------
            $currentStep = (int)$this->getRequest()->getParam('step', 2);
            $prevStep = $currentStep - 1;
            // ---------------------------------------

            if ($prevStep >= 1 && $prevStep <= 4) {
                // ---------------------------------------
                $url = $this->getUrl(
                    '*/adminhtml_ebay_listing_create/index',
                    array('_current' => true, 'step' => $prevStep)
                );
                $this->_addButton(
                    'back', array(
                    'label'     => Mage::helper('M2ePro')->__('Previous Step'),
                    'onclick'   => 'CommonObj.back_click(\'' . $url . '\')',
                    'class'     => 'back'
                    )
                );
                // ---------------------------------------
            }

            $nextStepBtnText = 'Next Step';

            $sessionKey = 'ebay_listing_create';
            $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($sessionKey);
            if ($currentStep == 4 && isset($sessionData['creation_mode']) && $sessionData['creation_mode'] ===
                Ess_M2ePro_Helper_View::LISTING_CREATION_MODE_LISTING_ONLY) {
                $nextStepBtnText = 'Complete';
            }

            // ---------------------------------------
            $url = $this->getUrl(
                '*/adminhtml_ebay_listing_create/index', array('_current' => true, 'step' => $currentStep)
            );
            $callback = 'function(params) { CommonObj.postForm(\''.$url.'\', params); }';
            $this->_addButton(
                'save', array(
                    'id'        => 'save',
                    'label'     => Mage::helper('M2ePro')->__($nextStepBtnText),
                    'onclick'   => 'EbayListingTemplateSwitcherObj.saveSwitchers(' . $callback . ')',
                    'class'     => 'next'
                )
            );
            // ---------------------------------------
        }
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $data = array(
            'allowed_tabs' => $this->getAllowedTabs()
        );
        $tabs = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_template_edit_tabs');
        $tabs->addData($data);
        $this->setChild('tabs', $tabs);
        // ---------------------------------------

        return $this;
    }

    //########################################

    public function getAllowedTabs()
    {
        if (!isset($this->_data['allowed_tabs']) || !is_array($this->_data['allowed_tabs'])) {
            return array();
        }

        return $this->_data['allowed_tabs'];
    }

    //########################################

    public function getFormHtml()
    {
        $html = '';
        $tabs = $this->getChild('tabs');

        // ---------------------------------------
        $html .= $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_template_switcher_initialization')
            ->toHtml();
        // ---------------------------------------

        // initiate template switcher url
        // ---------------------------------------
        $html .= Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Switcher::getSwitcherUrlHtml(
            Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Switcher::MODE_COMMON
        );
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'display_tab_buttons' => false
        );
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_floatingToolbarFixer');
        $block->addData($data);
        $html .= $block->toHtml();
        // ---------------------------------------

        // ---------------------------------------
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('ebay_listing');
        $headerHtml = '';
        if ($listing) {
            $headerHtml = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_listing_view_header', '',
                array(
                    'listing' => $listing
                )
            )->toHtml();
        }

        // ---------------------------------------

        // hide tabs selector if only one tab is allowed for displaying
        // ---------------------------------------
        if (count($this->getAllowedTabs()) == 1) {
            $html .= <<<HTML
<script type="text/javascript">
    Event.observe(window, 'load', function() {
        $('{$tabs->getId()}').hide();
    });
</script>
HTML;
        }

        // ---------------------------------------

        return $html . $headerHtml . $tabs->toHtml() . parent::getFormHtml();
    }

    //########################################
}
