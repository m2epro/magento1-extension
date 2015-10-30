<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Listing_OtherController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //########################################

    public function clearLogAction()
    {
        $ids = $this->getRequestIds();

        if (count($ids) == 0) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select Item(s) to clear.'));
            $this->_redirect('*/*/index');
            return;
        }

        foreach ($ids as $id) {
            Mage::getModel('M2ePro/Listing_Other_Log')->clearMessages($id);
        }

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('The 3rd party listing(s) Log has been successfully cleared.')
        );
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list'));
    }

    //########################################

    public function getErrorsSummaryAction()
    {
        $blockParams = array(
            'action_ids' => $this->getRequest()->getParam('action_ids'),
            'table_name' => Mage::getResourceModel('M2ePro/Listing_Other_Log')->getMainTable(),
            'type_log' => 'listing_other'
        );
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_log_errorsSummary','',$blockParams);
        return $this->getResponse()->setBody($block->toHtml());
    }

    //########################################
}