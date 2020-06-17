<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_CustomContainer extends Varien_Data_Form_Element_Abstract
{
    //########################################

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setType('custom_container');
    }

    //########################################

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $html = '<div id="'
            . $this->getHtmlId()
            . '" '.$this->getClass().
            $this->serialize(
                $this->getHtmlAttributes()
            )
            .'>'
            . $this->getText()
            . '</div>'
            . $this->getAfterElementHtml();
        return $html;
    }

    public function getHtmlAttributes()
    {
        return array_diff(parent::getHtmlAttributes(), array('class'));
    }

    protected function getClass()
    {
        $cssClass = ' class="control-value admin__field-value ';

        if (isset($this->_data['container_class'])) {
            return $cssClass . $this->_data['container_class'].'" ';
        }

        return $cssClass . '" ';
    }
}
