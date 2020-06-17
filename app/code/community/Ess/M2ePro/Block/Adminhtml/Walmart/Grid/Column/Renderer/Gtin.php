<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Grid_Column_Renderer_Gtin
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    //########################################

    public function render(Varien_Object $row)
    {
        $gtin = $this->_getValue($row);

        if (empty($gtin)) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $productId = $row->getData('id');
        $gtinHtml = Mage::helper('M2ePro')->escapeHtml($gtin);

        $walmartHelper = Mage::helper('M2ePro/Component_Walmart');
        $marketplaceId = ($this->getColumn()->getData('marketplace_id') !== null)
                              ? $this->getColumn()->getData('marketplace_id')
                              : $row->getData('marketplace_id');

        $channelUrl = $walmartHelper->getItemUrl(
            $row->getData($walmartHelper->getIdentifierForItemUrl($marketplaceId)),
            $marketplaceId
        );

        if (!empty($channelUrl)) {
            $gtinHtml = <<<HTML
<a href="{$channelUrl}" target="_blank">{$gtin}</a>
HTML;
        }

        $html = '<div class="walmart-identifiers-gtin">'.$gtinHtml;

        $showEditIdentifier = ($this->getColumn()->getData('show_edit_identifier') !== null)
                              ? $this->getColumn()->getData('show_edit_identifier')
                              : true;

        if ($showEditIdentifier) {
            $isVariationParent = $row->getData('is_variation_parent');

            if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED && !$isVariationParent) {
                $html .= <<<HTML
&nbsp;&nbsp;<a href="#" class="walmart-identifiers-gtin-edit"
   onclick="ListingGridObj.editChannelDataHandler.showIdentifiersPopup('$productId')">(edit)</a>
HTML;
            }
        }

        $html .= '</div>';

        $identifiers = array(
            'UPC'        => $row->getData('upc'),
            'EAN'        => $row->getData('ean'),
            'ISBN'       => $row->getData('isbn'),
            'Walmart ID' => $row->getData('wpid'),
            'Item ID'    => $row->getData('item_id')
        );

        $htmlAdditional = '';
        foreach ($identifiers as $title => $value) {
            if (empty($value)) {
                continue;
            }

            if (($row->getData('upc') || $row->getData('ean') || $row->getData('isbn')) &&
                ($row->getData('wpid') || $row->getData('item_id')) && $title == 'Walmart ID') {
                $htmlAdditional .= "<div class='separator-line'></div>";
            }

            $identifierCode  = Mage::helper('M2ePro')->__($title);
            $identifierValue = Mage::helper('M2ePro')->escapeHtml($value);

            $htmlAdditional .= <<<HTML
<div>
    <span style="display: inline-block; float: left;">
        <strong>{$identifierCode}:</strong>&nbsp;&nbsp;&nbsp;&nbsp;
    </span>
    <span style="display: inline-block; float: right;">
        {$identifierValue}
    </span>
    <div style="clear: both;"></div>
</div>
HTML;
        }

        if ($htmlAdditional != '') {
            $html .= <<<HTML
<div style="float:right; width: 16px;">
    <img class="tool-tip-image"
         style="vertical-align: middle;"
         src="{$this->getSkinUrl('M2ePro/images/i_icon.png')}">
    <span class="tool-tip-message tool-tip-message tip-left" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_logo.png')}">
        <div class="walmart-identifiers">
            {$htmlAdditional}
        </div>
    </span>
</div>
HTML;
        }

        return $html;
    }

    //########################################
}
