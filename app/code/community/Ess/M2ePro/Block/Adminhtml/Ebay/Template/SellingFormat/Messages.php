<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_SellingFormat_Messages
    extends Ess_M2ePro_Block_Adminhtml_Template_SellingFormat_Messages
{
    // ########################################

    public function getCurrencyConversionMessage($marketplaceCurrency = null)
    {
        $messageText = parent::getCurrencyConversionMessage($marketplaceCurrency);

        if (is_null($messageText)) {
            return NULL;
        }

        $toolTipIconSrc = $this->getSkinUrl('M2ePro/images/tool-tip-icon.png');
        $helpIconSrc = $this->getSkinUrl('M2ePro/images/help.png');

        $docUrl = 'http://www.magentocommerce.com/wiki/modules_reference/English/Mage_Adminhtml/system_currency/index';

        // M2ePro_TRANSLATIONS
        // More about Currency rate set-up can be found in the <a href="%url%" target="_blank">Magento documentation</a>
        $helpText = 'More about Currency rate set-up can be found in the ';
        $helpText .= '<a href="%url%" target="_blank">Magento documentation</a>';
        $helpText = Mage::helper('M2ePro')->__($helpText, $docUrl);

        return <<<HTML
{$messageText}
<div style="display: inline-block;">
    <img src="{$toolTipIconSrc}" class="tool-tip-image">
    <span class="tool-tip-message" style="font-size: 12px; display: none;">
        <img src="{$helpIconSrc}">
        <span>{$helpText}</span>
    </span>
</div>
HTML;
    }

    // ########################################
}