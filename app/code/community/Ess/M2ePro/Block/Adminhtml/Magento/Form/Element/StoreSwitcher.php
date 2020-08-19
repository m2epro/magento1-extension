<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_StoreSwitcher extends Varien_Data_Form_Element_Abstract
{
    //########################################

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setNoWrapAsAddon(true);
    }

    //########################################

    public function getElementHtml()
    {
        $html = Mage::app()->getLayout()->createBlock('Ess_M2ePro_Block_Adminhtml_StoreSwitcher')
            ->addData(
                array(
                    'id'                         => $this->getHtmlId(),
                    'selected'                   => $this->getData('value'),
                    'name'                       => $this->getName(),
                    'display_default_store_mode' => $this->getData('display_default_store_mode'),
                    'required_option'            => $this->getData('required'),
                    'empty_option'               => $this->getData('has_empty_option'),
                    'class'                      => $this->getData('class'),
                    'has_default_option'         => $this->getData('has_default_option'),
                )
            )->toHtml();

        return $html . $this->getAfterElementHtml();
    }

    //########################################
}
