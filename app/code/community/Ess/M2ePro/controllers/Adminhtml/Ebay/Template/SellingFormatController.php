<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_Template_SellingFormatController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    public function getSearchCharityPopUpHtmlAction()
    {
        $this->loadLayout();

        try {
            $searchBlock = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_template_sellingFormat_searchCharity'
            );
            $this->getResponse()->setBody($searchBlock->toHtml());
        } catch (Exception $e) {
            $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('error' => $e->getMessage())));
        }
    }

    public function searchCharityAction()
    {
        $this->loadLayout();

        $query = $this->getRequest()->getPost('query');
        $destination = $this->getRequest()->getPost('destination');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');

        $params = array(
            $destination    => $query,
            'maxRecord'     => 10,
        );

        try {
            $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'marketplace', 'get', 'charity',
                $params, null,
                $marketplaceId
            );

            $dispatcherObject->process($connectorObj);
            $responseData = $connectorObj->getResponseData();
        } catch (Exception $e) {
            $message = Mage::helper('M2ePro')->__('Error search charity');
            $response = array('result' => 'error','data' => $message);
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($response));
        }

        $grid = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_template_sellingFormat_searchCharity_grid', '',
            array('Charities' => $responseData['Charities'])
        );
        $data = $grid->toHtml();

        $response = array(
            'result' => 'success',
            'data' => $data
        );

        if ((int)$responseData['total_count'] > 10) {
            $response['count'] = (int)$responseData['total_count'];
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($response));
    }

    //########################################
}
