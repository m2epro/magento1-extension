<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
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
            $this->getResponse()->setBody(json_encode(array('error' => $e->getMessage())));
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

            $dispatcherObject = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('marketplace', 'get', 'charity',
                                                                   $params, NULL,
                                                                   $marketplaceId);

            $responseData = $dispatcherObject->process($connectorObj);

        } catch (Exception $e) {
            $message = Mage::helper('M2ePro')->__('Error search charity');
            $response = array('result' => 'error','data' => $message);
            return $this->getResponse()->setBody(json_encode($response));
        }

        $grid = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_template_sellingFormat_searchCharity_grid', '',
            array('Charities' => $responseData['Charities']));
        $data = $grid->toHtml();

        $response = array(
            'result' => 'success',
            'data' => $data
        );

        if ((int)$responseData['total_count'] > 10) {
            $response['count'] = (int)$responseData['total_count'];
        }

        return $this->getResponse()->setBody(json_encode($response));
    }

    //########################################
}