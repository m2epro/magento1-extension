<?php

class Ess_M2ePro_Adminhtml_Walmart_Marketplace_WithProductTypeController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    public function runSynchNowAction()
    {
        // @codingStandardsIgnoreLine
        session_write_close();

        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = Mage::helper('M2ePro/Component')->getUnknownObject(
            'Marketplace',
            (int)$this->getRequest()->getParam('marketplace_id')
        );

        /** @var Ess_M2ePro_Model_Walmart_Marketplace_WithProductType_Synchronization $synchronization */
        $synchronization = Mage::getModel('M2ePro/Walmart_Marketplace_WithProductType_Synchronization');
        $synchronization->setMarketplace($marketplace);

        if ($synchronization->isLocked()) {
            $synchronization->getlog()->addMessage(
                Mage::helper('M2ePro')->__(
                    'Marketplaces cannot be updated now. '
                    . 'Please wait until another marketplace synchronization is completed, then try again.'
                ),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
            );

            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => 'error')));
        }

        try {
            $synchronization->process();
        } catch (Exception $e) {
            $synchronization->getlog()->addMessageFromException($e);

            $synchronization->getLockItemManager()->remove();

            Mage::getModel('M2ePro/Servicing_Dispatcher')->processTask(
                \Ess_M2ePro_Model_Servicing_Task_License::NAME
            );

            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => 'error')));
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => 'success')));
    }

    public function synchGetExecutingInfoAction()
    {
        /** @var Ess_M2ePro_Model_Walmart_Marketplace_WithProductType_Synchronization $synchronization */
        $synchronization = Mage::getModel('M2ePro/Walmart_Marketplace_WithProductType_Synchronization');
        if (!$synchronization->isLocked()) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('mode' => 'inactive')));
        }

        $contentData = $synchronization->getLockItemManager()->getContentData();
        $progressData = $contentData[Ess_M2ePro_Model_Lock_Item_Progress::CONTENT_DATA_KEY];

        $response = array('mode' => 'executing');
        if (!empty($progressData)) {
            $response['title'] = 'Marketplace Synchronization';
            $response['percents'] = $progressData[key($progressData)]['percentage'];
            $response['status'] = key($progressData);
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($response));
    }

}