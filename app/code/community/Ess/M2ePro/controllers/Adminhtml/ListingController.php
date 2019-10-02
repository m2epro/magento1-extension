<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_ListingController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //########################################

    public function saveTitleAction()
    {
        $listingId = $this->getRequest()->getParam('id');
        $title = $this->getRequest()->getParam('title');

        if ($listingId === null) {
            return;
        }

        $model = Mage::getModel('M2ePro/Listing')->loadInstance((int)$listingId);
        $model->setTitle($title)->save();

        Mage::getModel('M2ePro/Listing_Log')->getResource()->updateListingTitle($listingId, $title);
    }

    public function clearLogAction()
    {
        $ids = $this->getRequestIds();

        if (empty($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select Item(s) to clear.'));
            $this->_redirect('*/*/index');
            return;
        }

        foreach ($ids as $id) {
            Mage::getModel('M2ePro/Listing_Log')->clearMessages($id);
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The Listing(s) Log was successfully cleared.'));
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list'));
    }

    public function getErrorsSummaryAction()
    {
        $actionIds = $this->getRequest()->getParam('action_ids');

        if (empty($actionIds)) {
            return $this->getResponse()->setBody('action_ids can not be empty');
        }

        $blockParams = array(
            'action_ids' => $this->getRequest()->getParam('action_ids'),
            'table_name' => Mage::getResourceModel('M2ePro/Listing_Log')->getMainTable(),
            'type_log'   => 'listing'
        );
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_log_errorsSummary', '', $blockParams);

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'result' => 'success',
                'html' => $block->toHtml()
                )
            )
        );
    }

    //########################################

    public function saveListingAdditionalDataAction()
    {
        $listingId = $this->getRequest()->getParam('id');
        $paramName = $this->getRequest()->getParam('param_name');
        $paramValue = $this->getRequest()->getParam('param_value');

        if (empty($listingId) || empty($paramName) || empty($paramValue)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $listing = Mage::helper('M2ePro/Component')->getUnknownObject('Listing', $listingId);

        $listing->setSetting('additional_data', $paramName, $paramValue);
        $listing->save();

        return $this->getResponse()->setBody(0);
    }

    //########################################
}
