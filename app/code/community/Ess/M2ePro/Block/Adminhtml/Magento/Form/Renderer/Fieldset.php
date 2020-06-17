<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Form_Renderer_Fieldset
    extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset
{
    //########################################

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->addClass('m2epro-fieldset');

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
