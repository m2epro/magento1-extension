<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_HelpBlock extends Varien_Data_Form_Element_Abstract
{
    //########################################

    public function getElementHtml()
    {
        return Mage::app()->getLayout()->createBlock('Ess\M2ePro\Block\Adminhtml\HelpBlock')
            ->addData(
                array(
                    'id'            => $this->getId(),
                    'title'         => $this->getData('title'),
                    'content'       => $this->getData('content'),
                    'class'         => $this->getData('class'),
                    'tooltiped'     => $this->getData('tooltiped'),
                    'no_hide'       => $this->getData('no_hide'),
                    'no_collapse'   => $this->getData('no_collapse'),
                    'style'         => $this->getData('style'),
                )
            );
    }

    //########################################
}
