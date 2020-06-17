<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Form_Renderer_Element
    extends Mage_Adminhtml_Block_Widget_Form_Renderer_Element
{
    //########################################

    protected function getTooltipHtml($content)
    {
        $toolTipIconSrc = $this->getSkinUrl('M2ePro/images/tool-tip-icon.png');
        $helpIconSrc = $this->getSkinUrl('M2ePro/images/help.png');

        return <<<HTML
<span>
    <img class="tool-tip-image" style="vertical-align: middle;" src="{$toolTipIconSrc}" />
    <span class="tool-tip-message" style="display:none; text-align: left; width: 120px; background: #E3E3E3;">
        <img src="{$helpIconSrc}" />
        <span style="color:gray;">
           {$content}
        </span>
    </span>
</span>
HTML;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $isRequired = $element->getData('required');

        if ($isRequired === true) {
            $element->removeClass('required-entry');
            $element->removeClass('_required');
            $element->setClass('M2ePro-required-when-visible ' . $element->getClass());
        }

        $tooltip = $element->getData('tooltip');

        if ($tooltip === null) {
            $element->addClass('m2epro-field-without-tooltip');
            return parent::render($element);
        }

        $element->setAfterElementHtml(
            $element->getAfterElementHtml() . $this->getTooltipHtml($tooltip)
        );

        $element->addClass('m2epro-field-with-tooltip');

        return parent::render($element);
    }

    //########################################

    /**
     * @param array|string $data
     * @param null $allowedTags
     * @return array|string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        return Mage::helper('M2ePro')->escapeHtml(
            $data,
            array('div', 'a', 'strong', 'br', 'i', 'b', 'ul', 'li', 'p'),
            ENT_NOQUOTES
        );
    }

    //########################################
}
