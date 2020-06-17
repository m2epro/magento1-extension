<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Grid_Column_Renderer_Sku
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    //########################################

    public function render(Varien_Object $row)
    {
        $value = $this->_getValue($row);

        if ((!$row->getData('is_variation_parent') &&
                $row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) ||
            ($row->getData('is_variation_parent') && $row->getData('general_id') == '')) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if ($value === null || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        }

        $showDefectedMessages = ($this->getColumn()->getData('show_defected_messages') !== null)
                                ? $this->getColumn()->getData('show_defected_messages')
                                : true;

        if (!$showDefectedMessages) {
            return $value;
        }

        if (!$row->getData('is_variation_parent') && $row->getData('defected_messages')) {
            $defectedMessages = Mage::helper('M2ePro')->jsonDecode($row->getData('defected_messages'));
            $msg = '';

            foreach ($defectedMessages as $message) {
                if (empty($message['message'])) {
                    continue;
                }

                $msg .= '<p>'.$message['message'] . '&nbsp;';
                if (!empty($message['value'])) {
                    $msg .= Mage::helper('M2ePro')->__('Current Value') . ': "' . $message['value'] . '"';
                }

                $msg .= '</p>';
            }

            if (empty($msg)) {
                return $value;
            }

            $value .= <<<HTML
<span style="float:right;">
    <img id="map_link_defected_message_icon_{$row->getId()}"
         class="tool-tip-image"
         style="vertical-align: middle;"
         src="{$this->getSkinUrl('M2ePro/images/warning.png')}">
    <span class="tool-tip-message tool-tip-warning tip-left" style="display:none; max-width: 400px;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_notice.gif')}">
        <span>{$msg}</span>
    </span>
</span>
HTML;
        }

        return $value;
    }

    //########################################
}
