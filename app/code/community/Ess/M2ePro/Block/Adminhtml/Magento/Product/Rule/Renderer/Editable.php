<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Product_Rule_Renderer_Editable
    extends Mage_Core_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    //########################################

    /**
     * Render element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @see Varien_Data_Form_Element_Renderer_Interface::render()
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->addClass('element-value-changer');
        $valueName = $element->getValueName();

        if ($element instanceof Varien_Data_Form_Element_Select && $valueName == '...') {
            $optionValues = $element->getValues();

            foreach ($optionValues as $option) {
                if ($option['value'] === '') {
                    $valueName = $option['label'];
                }
            }
        }

        if ($valueName === '') {
            $valueName = '...';
        }

        if ($element->getShowAsText()) {
            $html = ' <input type="hidden" class="hidden" id="' . $element->getHtmlId()
                . '" name="' . $element->getName() . '" value="' . $element->getValue() . '"/> '
                . htmlspecialchars($valueName) . '&nbsp;';
        } else {
            $html = ' <span class="rule-param"'
                . ($element->getParamId() ? ' id="' . $element->getParamId() . '"' : '') . '>'
                . '<a href="javascript:void(0)" class="label">';

            $translate = Mage::getSingleton('core/translate_inline');

            $html .= $translate->isAllowed() ? Mage::helper('core')->escapeHtml($valueName) :
                Mage::helper('core')->escapeHtml(Mage::helper('core/string')->truncate($valueName, 33, '...'));

            $html .= '</a><span class="element"> ' . $element->getElementHtml();

            if ($element->getExplicitApply()) {
                $html .= ' <a href="javascript:void(0)" class="rule-param-apply"><img src="'
                . $this->getSkinUrl('M2ePro/images/rule_component_apply.gif') . '" class="v-middle" alt="'
                . Mage::helper('M2ePro')->__('Apply') . '" title="' . Mage::helper('M2ePro')->__('Apply') . '" /></a> ';
            }

            $html .= '</span></span>&nbsp;';
        }

        return $html;
    }

    //########################################
}