<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Order_SendInvoice
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'amazon/order/send_invoice';

    //####################################

    public function isPossibleToRun()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_ORDERS);

        return $synchronizationLog;
    }

    //########################################

    protected function performActions()
    {
        $this->deleteNotActualChanges();

        $permittedAccounts = $this->getPermittedAccounts();
        if (empty($permittedAccounts)) {
            return;
        }

        foreach ($permittedAccounts as $account) {
            /** @var Ess_M2ePro_Model_Account $account */

            $this->getOperationHistory()->addText('Starting account "'.$account->getTitle().'"');

            $this->getOperationHistory()->addTimePoint(
                __METHOD__.'process'.$account->getId(),
                'Process account '.$account->getTitle()
            );

            try {
                $this->processAccount($account);
            } catch (Exception $exception) {
                $message = Mage::helper('M2ePro')->__(
                    'The "Send Invoice" Action for Amazon Account "%account%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }

            $this->getOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
        }
    }

    //########################################

    protected function getPermittedAccounts()
    {
        /** @var $accountsCollection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        $accountsCollection->addFieldToFilter(
            'auto_invoicing',
            Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_AUTO_INVOICING_UPLOAD_MAGENTO_INVOICES
        );
        return $accountsCollection->getItems();
    }

    // ---------------------------------------

    protected function processAccount(Ess_M2ePro_Model_Account $account)
    {
        $relatedChanges = $this->getRelatedChanges($account);
        if (empty($relatedChanges)) {
            return;
        }

        Mage::getResourceModel('M2ePro/Order_Change')->incrementAttemptCount(array_keys($relatedChanges));

        /** @var $dispatcherObject Ess_M2ePro_Model_Amazon_Connector_Dispatcher */
        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');

        $failedChangesIds = array();
        $changesCount = count($relatedChanges);

        foreach ($relatedChanges as $change) {
            $changeParams = $change->getParams();

            /** @var Ess_M2ePro_Model_Order $order */
            $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', $change->getOrderId());

            if (!$order->getChildObject()->canSendInvoice()) {
                $failedChangesIds[] = $change->getId();
                continue;
            }

            $documentData = $this->getDocumentData($order, $changeParams['document_type']);

            $connectorData = array(
                'change_id' => $change->getId(),
                'order_id'  => $change->getOrderId(),
                'amazon_order_id' => $order->getChildObject()->getAmazonOrderId(),
                'document_number' => $documentData['document_number'],
                'document_pdf' => $documentData['document_pdf'],
                'document_type' => $changeParams['document_type']
            );

            /** @var Ess_M2ePro_Model_Cron_Task_Amazon_Order_SendInvoice_Requester $connectorObj */
            $connectorObj = $dispatcherObject->getCustomConnector(
                'Cron_Task_Amazon_Order_SendInvoice_Requester',
                array('order' => $connectorData), $account
            );
            $dispatcherObject->process($connectorObj);

            /**
             * Amazon trolling 1 request per 3 sec.
             */
            if ($changesCount > 1) {
                // @codingStandardsIgnoreLine
                sleep(3);
            }
        }

        if (!empty($failedChangesIds)) {
            Mage::getResourceModel('M2ePro/Order_Change')->deleteByIds($failedChangesIds);
        }
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Account $account
     * @return Ess_M2ePro_Model_Order_Change[]
     */
    protected function getRelatedChanges(Ess_M2ePro_Model_Account $account)
    {
        /** @var Ess_M2ePro_Model_Resource_Order_Change_Collection $changesCollection */
        $changesCollection = Mage::getModel('M2ePro/Order_Change')->getCollection();
        $changesCollection->addAccountFilter($account->getId());
        $changesCollection->addProcessingAttemptDateFilter(10);
        $changesCollection->addFieldToFilter('component', Ess_M2ePro_Helper_Component_Amazon::NICK);
        $changesCollection->addFieldToFilter('action', Ess_M2ePro_Model_Order_Change::ACTION_SEND_INVOICE);
        $changesCollection->getSelect()->joinLeft(
            array('pl' => Mage::getResourceModel('M2ePro/Processing_Lock')->getMainTable()),
            'pl.object_id = main_table.order_id AND pl.model_name = \'M2ePro/Order\'',
            array()
        );
        $changesCollection->addFieldToFilter('pl.id', array('null' => true));
        $changesCollection->getSelect()->group(array('order_id'));

        return $changesCollection->getItems();
    }

    // ---------------------------------------

    protected function deleteNotActualChanges()
    {
        Mage::getResourceModel('M2ePro/Order_Change')
            ->deleteByProcessingAttemptCount(
                Ess_M2ePro_Model_Order_Change::MAX_ALLOWED_PROCESSING_ATTEMPTS,
                Ess_M2ePro_Helper_Component_Amazon::NICK
            );
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param $type
     * @return array
     * @throws Zend_Pdf_Exception
     */
    protected function getDocumentData($order, $type)
    {
        switch ($type) {
            case Ess_M2ePro_Model_Amazon_Order::DOCUMENT_TYPE_INVOICE:
                /** @var Mage_Sales_Model_Resource_Order_Invoice_Collection $invoices */
                $invoices = $order->getMagentoOrder()->getInvoiceCollection();
                /** @var Mage_Sales_Model_Order_Invoice $invoice */
                $invoice = $invoices->getLastItem();

                /** @var Mage_Sales_Model_Order_Pdf_Invoice $orderPdfInvoice */
                $orderPdfInvoice = Mage::getModel('sales/order_pdf_invoice');
                $pdf = $orderPdfInvoice->getPdf(array($invoice));

                $documentNumber = $invoice->getIncrementId();
                $documentPdf = $pdf->render();
                break;

            case Ess_M2ePro_Model_Amazon_Order::DOCUMENT_TYPE_CREDIT_NOTE:
                /** @var Mage_Sales_Model_Resource_Order_Creditmemo_Collection $creditmemos */
                $creditmemos = $order->getMagentoOrder()->getCreditmemosCollection();
                /** @var Mage_Sales_Model_Order_Creditmemo $creditmemo */
                $creditmemo = $creditmemos->getLastItem();

                /** @var Mage_Sales_Model_Order_Pdf_Creditmemo $orderpPdfCreditmemos */
                $orderpPdfCreditmemos = Mage::getModel('sales/order_pdf_creditmemo');
                $pdf = $orderpPdfCreditmemos->getPdf(array($creditmemo));

                $documentNumber = $creditmemo->getIncrementId();
                $documentPdf = $pdf->render();
                break;

            default:
                $documentNumber = '';
                $documentPdf = '';
                break;
        }

        return array(
            'document_number' => $documentNumber,
            'document_pdf' => $documentPdf
        );
    }

    //########################################
}
