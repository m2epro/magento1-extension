<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Template_Category_Specific as Specific;

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser_Specific_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayTemplateCategoryChooserSpecificEdit');
        $this->setTemplate('M2ePro/widget/form/container/simplified.phtml');

        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_template_category_chooser_specific';
        $this->_mode = 'edit';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    //########################################

    public function prepareFormData()
    {
        $templateSpecifics = array();
        $dictionarySpecifics = $this->getDictionarySpecifics();

        $selectedSpecs = Mage::helper('M2ePro')->jsonDecode($this->getData('selected_specifics'));

        if ($this->getData('template_id')) {
            /** @var Ess_M2ePro_Model_Ebay_Template_Category $template */
            $template = Mage::getModel('M2ePro/Ebay_Template_Category');
            $template->load($this->getData('template_id'));
            $templateSpecifics = $template->getSpecifics();
        } elseif (!empty($selectedSpecs)) {
            $builder = Mage::getModel('M2ePro/Ebay_Template_Category_Builder');
            foreach ($selectedSpecs as $selectedSp) {
                $templateSpecifics[] = $builder->serializeSpecific($selectedSp);
            }
        } else {
            /** @var Ess_M2ePro_Model_Ebay_Template_Category $template */
            $template = Mage::getModel('M2ePro/Ebay_Template_Category');
            $template->loadByCategoryValue(
                $this->getData('category_value'),
                $this->getData('category_mode'),
                $this->getData('marketplace_id'),
                0
            );

            $template->getId() && $templateSpecifics = $template->getSpecifics();
        }

        foreach ($dictionarySpecifics as &$dictionarySpecific) {
            foreach ($templateSpecifics as $templateSpecific) {
                if ($dictionarySpecific['title'] == $templateSpecific['attribute_title']) {
                    $dictionarySpecific['template_specific'] = $templateSpecific;
                    continue;
                }
            }
        }

        unset($dictionarySpecific);

        $templateCustomSpecifics = array();
        foreach ($templateSpecifics as $templateSpecific) {
            if ($templateSpecific['mode'] == Specific::MODE_CUSTOM_ITEM_SPECIFICS) {
                $templateCustomSpecifics[] = $templateSpecific;
            }
        }

        $this->getChild('form')->setData(
            'form_data', array(
                'dictionary_specifics'      => $dictionarySpecifics,
                'template_custom_specifics' => $templateCustomSpecifics
            )
        );
    }

    protected function getDictionarySpecifics()
    {
        if ($this->getData('category_mode') == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            return array();
        }

        $specifics = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getSpecifics(
            $this->getData('category_value'), $this->getData('marketplace_id')
        );

        return $specifics === null ? array() : $specifics;
    }

    //########################################

    protected function _toHtml()
    {
        $infoBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_template_category_chooser_specific_info', '', array(
                'category_mode'  => $this->getData('category_mode'),
                'category_value' => $this->getData('category_value'),
                'marketplace_id' => $this->getData('marketplace_id')
            )
        );

        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
                'Item Specifics cannot have the same Labels.' => Mage::helper('M2ePro')->__(
                    'Item Specifics cannot have the same Labels.'
                ),
            )
        );

        $constants = Mage::helper('M2ePro')
            ->getClassConstantAsJson('Ess_M2ePro_Model_Ebay_Template_Category_Specific');

        $parentHtml = parent::_toHtml();

        return <<<HTML
<style type="text/css">
    .grid th, .grid td {
        padding: 2px 4px 2px 4px !important;
    }
    [id*='specific_dictionary_custom_value_table_body_'] > tr > td {
        padding: 0 !important;
    }
</style>

<script type="text/javascript">
    M2ePro.translator.add({$translations});
    M2ePro.php.setConstants({$constants}, 'Ess_M2ePro_Model_Ebay_Template_Category_Specific');

    EbayTemplateCategorySpecificsObj = new EbayTemplateCategorySpecifics();
</script>

<div style="margin-top: 15px;">
    {$infoBlock->_toHtml()}
</div>

<div id="ebay-category-chooser-specific" style="height: 375px; overflow: auto;">
    {$parentHtml}
</div>
{$this->getFormButtonsHtml()}
HTML;
    }

    protected function getFormButtonsHtml()
    {
        $data = array(
            'id'      => 'ebay_specifics_edit_reset_btn',
            'class'   => 'confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Reset'),
            'onclick' => 'EbayTemplateCategorySpecificsObj.resetSpecifics();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('reset_button', $buttonBlock);

        $data = array(
            'id'      => 'ebay_specifics_edit_save_btn',
            'class'   => 'scalable save done',
            'label'   => Mage::helper('M2ePro')->__('Save'),
            'onclick' => 'EbayTemplateCategoryChooserObj.confirmSpecifics();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_button', $buttonBlock);

        return <<<HTML
<div style="margin-top: 25px; width: 100%;">
<div style="margin-left: 10px; margin-bottom: 10px; float: right">{$this->getChildHtml('confirm_button')}</div>
<div style="margin-left: 10px; margin-bottom: 10px; float: right">{$this->getChildHtml('reset_button')}</div>
<div style="margin-left: 10px; margin-bottom: 10px; float: right">
<a onclick="Windows.getFocusedWindow().close();" href="javascript:void(0);">Cancel</a>
</div>
</div>
HTML;
    }

    //########################################
}
