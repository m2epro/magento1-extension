<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Congratulation extends Mage_Adminhtml_Block_Template
{
    //########################################

    protected function _toHtml()
    {
        $content = Mage::helper('M2ePro')->__(
            'This wizard was already finished.
            Please <a href="%1%">Contact Us</a>, if it is need.',
            Mage::helper('M2ePro/Module_Support')->getSupportUrl()
        );

        return <<<HTML
<h2>
    {$content}
</h2>

HTML;
    }

    //########################################
}
