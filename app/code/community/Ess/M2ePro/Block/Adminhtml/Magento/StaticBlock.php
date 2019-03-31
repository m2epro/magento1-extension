<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Magento_StaticBlock extends Mage_Cms_Model_Block
{
    //########################################

    protected function _afterSave()
    {
        parent::_afterSave();
        Mage::dispatchEvent('m2epro_magento_static_block_save_after', array('object' => $this));
    }

    //########################################
}