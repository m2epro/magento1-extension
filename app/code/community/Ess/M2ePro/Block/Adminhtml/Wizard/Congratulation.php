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
            'Installation Wizard is completed. If you can\'t proceed, please contact us at <a href="mailto:support@m2epro.com">support@m2epro.com</a>.'
        );

        return <<<HTML
<h2>
    {$content}
</h2>

HTML;
    }

    //########################################
}
