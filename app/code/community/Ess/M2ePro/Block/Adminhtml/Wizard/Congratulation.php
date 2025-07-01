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
            'The Installation Wizard has finished successfully. To finalize the setup, please clear the Magento cache.
If you experience any issues, feel free to contact our support team at <a href="mailto:support@m2epro.com">support@m2epro.com</a>.'
        );

        return <<<HTML
<h2>
    {$content}
</h2>

HTML;
    }

    //########################################
}
