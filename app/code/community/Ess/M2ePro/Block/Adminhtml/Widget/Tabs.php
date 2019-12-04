<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Widget_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    //########################################

    /**
     * {@inheritdoc}
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        return Mage::helper('M2ePro/Data')->escapeHtml(
            $data, array('img', 'b',' strong', 'i', 'span', 'a'), ENT_NOQUOTES
        );
    }

    //########################################
}
