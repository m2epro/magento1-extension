<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Switcher extends Mage_Adminhtml_Block_Widget
{
    private $templates = NULL;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingTemplateSwitcher');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/template/switcher.phtml');
    }

    //########################################

    public function getHeaderText()
    {
        $title = '';

        switch ($this->getTemplateNick()) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT:
                $title = Mage::helper('M2ePro')->__('Payment');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING:
                $title = Mage::helper('M2ePro')->__('Shipping');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN:
                $title = Mage::helper('M2ePro')->__('Return');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT:
                $title = Mage::helper('M2ePro')->__('Price, Quantity and Format');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION:
                $title = Mage::helper('M2ePro')->__('Description');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION:
                $title = Mage::helper('M2ePro')->__('Synchronization');
                break;
        }

        return $title;
    }

    //########################################

    public function getHeaderWidth()
    {
        switch ($this->getTemplateNick()) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN:
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING:
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT:
                $width = 70;
                break;

            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT:
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION:
                $width = 200;
                break;

            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION:
                $width = 140;
                break;

            default:
                $width = 100;
                break;
        }

        return $width;
    }

    //########################################

    public static function getSwitcherUrlHtml()
    {
        $urls = json_encode(array(
            'adminhtml_ebay_template/getTemplateHtml' => self::getSwitcherUrl()
        ));

        return <<<HTML
<script type="text/javascript">
    M2ePro.url.add({$urls});
</script>
HTML;
    }

    public static function getSwitcherUrl()
    {
        $params = array();

        // initiate account param
        // ---------------------------------------
        $account = Mage::helper('M2ePro/Data_Global')->getValue('ebay_account');
        $params['account_id'] = $account->getId();
        // ---------------------------------------

        // initiate marketplace param
        // ---------------------------------------
        $marketplace = Mage::helper('M2ePro/Data_Global')->getValue('ebay_marketplace');
        $params['marketplace_id'] = $marketplace->getId();
        // ---------------------------------------

        // initiate attribute sets param
        // ---------------------------------------
        $attributeSets = Mage::helper('M2ePro/Data_Global')->getValue('ebay_attribute_sets');
        $params['attribute_sets'] = implode(',', $attributeSets);
        // ---------------------------------------

        // initiate display use default option param
        // ---------------------------------------
        $displayUseDefaultOption = Mage::helper('M2ePro/Data_Global')->getValue('ebay_display_use_default_option');
        $params['display_use_default_option'] = (int)(bool)$displayUseDefaultOption;
        // ---------------------------------------

        return Mage::helper('adminhtml')->getUrl('M2ePro/adminhtml_ebay_template/getTemplateHtml', $params);
    }

    //########################################

    public function getTemplateNick()
    {
        if (!isset($this->_data['template_nick'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Template nick is not defined.');
        }

        return $this->_data['template_nick'];
    }

    public function getTemplateMode()
    {
        $templateMode = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_mode_' . $this->getTemplateNick());

        if (is_null($templateMode)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Template Mode is not initialized.');
        }

        return $templateMode;
    }

    public function getTemplateId()
    {
        $template = $this->getTemplateObject();

        if (is_null($template)) {
            return NULL;
        }

        return $template->getId();
    }

    public function getTemplateObject()
    {
        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_' . $this->getTemplateNick());

        if (!is_null($template) && !is_null($template->getId())) {
            return $template;
        }

        return NULL;
    }

    // ---------------------------------------

    public function isTemplateModeParentForced()
    {
        $key = 'ebay_template_force_parent_' . $this->getTemplateNick();
        $forcedParent = Mage::helper('M2ePro/Data_Global')->getValue($key);

        return (bool)$forcedParent;
    }

    public function isTemplateModeParent()
    {
        return $this->getTemplateMode() == Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT;
    }

    public function isTemplateModeCustom()
    {
        return $this->getTemplateMode() == Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM;
    }

    public function isTemplateModeTemplate()
    {
        return $this->getTemplateMode() == Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE;
    }

    //########################################

    public function getFormDataBlock()
    {
        $blockName = NULL;

        switch ($this->getTemplateNick()) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT:
                $blockName = 'M2ePro/adminhtml_ebay_template_payment_edit_form_data';
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN:
                $blockName = 'M2ePro/adminhtml_ebay_template_return_edit_form_data';
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING:
                $blockName = 'M2ePro/adminhtml_ebay_template_shipping_edit_form_data';
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT:
                $blockName = 'M2ePro/adminhtml_ebay_template_sellingFormat_edit_form_data';
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION:
                $blockName = 'M2ePro/adminhtml_ebay_template_description_edit_form_data';
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION:
                $blockName = 'M2ePro/adminhtml_ebay_template_synchronization_edit_form_data';
                break;
        }

        if (is_null($blockName)) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                sprintf('Form data Block for Template nick "%s" is unknown.', $this->getTemplateNick())
            );
        }

        $parameters = array(
            'is_custom' => $this->isTemplateModeCustom(),
            'custom_title' => Mage::helper('M2ePro/Data_Global')->getValue('ebay_custom_template_title'),
            'policy_localization' => $this->getData('policy_localization')
        );
        $block = $this->getLayout()->createBlock($blockName,'',$parameters);

        return $block;
    }

    public function getFormDataBlockHtml($templateDataForce = false)
    {
        $nick = $this->getTemplateNick();

        if ($this->isTemplateModeCustom() || $templateDataForce) {
            $html = $this->getFormDataBlock()->toHtml();
            $style = '';
        } else {
            $html = '';
            $style = 'display: none;';
        }

        return <<<HTML
<div id="template_{$nick}_data_container" class="template-data-container" style="{$style}">
    {$html}
</div>
HTML;
    }

    //########################################

    public function canDisplaySwitcher()
    {
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            return false;
        }

        $templates = $this->getTemplates();

        if (count($templates) == 0 && !$this->canDisplayUseDefaultOption()) {
            return false;
        }

        return true;
    }

    public function canDisplayUseDefaultOption()
    {
        $displayUseDefaultOption = Mage::helper('M2ePro/Data_Global')->getValue('ebay_display_use_default_option');

        if (is_null($displayUseDefaultOption)) {
            return true;
        }

        return (bool)$displayUseDefaultOption;
    }

    //########################################

    public function getTemplates()
    {
        if (!is_null($this->templates)) {
            return $this->templates;
        }

        $manager = Mage::getModel('M2ePro/Ebay_Template_Manager')->setTemplate($this->getTemplateNick());

        $collection = $manager->getTemplateModel()
            ->getCollection()
            ->addFieldToFilter('is_custom_template', 0)
            ->setOrder('title', 'ASC');

        if ($manager->isMarketplaceDependentTemplate()) {
            $marketplace = Mage::helper('M2ePro/Data_Global')->getValue('ebay_marketplace');
            $collection->addFieldToFilter('marketplace_id', $marketplace->getId());
        }

        $this->templates = $collection->getItems();

        return $this->templates;
    }

    //########################################

    public function getSwitcherJsObjectName()
    {
        $nick = ucfirst($this->getTemplateNick());
        return "ebayTemplate{$nick}SwitcherJsObject";
    }

    public function getSwitcherId()
    {
        $nick = $this->getTemplateNick();
        return "template_{$nick}";
    }

    public function getSwitcherName()
    {
        $nick = $this->getTemplateNick();
        return "template_{$nick}";
    }

    //########################################

    public function getButtonsHtml()
    {
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            return '';
        }

        $html = $this->getChildHtml('save_custom_as_template');
        $nick = $this->getTemplateNick();

        return <<<HTML
<div id="template_{$nick}_buttons_container" class="entry-edit">
    <div class="fieldset">
        <div class="hor-scroll" style="padding-right: 1px;">{$html}</div>
    </div>
</div>
HTML;
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $nick = $this->getTemplateNick();
        $data = array(
            'class'   => 'save-custom-template-' . $nick,
            'label'   => Mage::helper('M2ePro')->__('Save as New Policy'),
            'onclick' => 'EbayListingTemplateSwitcherHandlerObj.customSaveAsTemplate(\''. $nick .'\');',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('save_custom_as_template', $buttonBlock);
        // ---------------------------------------
    }

    //########################################

    protected function _toHtml()
    {
        return parent::_toHtml() . $this->getFormDataBlockHtml() . $this->getButtonsHtml();
    }

    //########################################
}