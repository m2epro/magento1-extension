<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Account as AmazonAccount;
use Ess_M2ePro_Model_Amazon_Order as AmazonOrder;
use Ess_M2ePro_Model_Amazon_Order_Invoice as AmazonOrderInvoice;

class Ess_M2ePro_Model_Cron_Task_Amazon_Order_SendInvoice
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'amazon/order/send_invoice';

    /** ~4-10 seconds on call, ~5-10 invoices per minute, 50 requests in 10 minutes */
    const LIMIT_ORDER_CHANGES = 50;

    /** @var int $_interval (in seconds) */
    protected $_interval = 600;

    protected $_maxOrderChangesPerTask = 0;

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

            if ($this->_maxOrderChangesPerTask === self::LIMIT_ORDER_CHANGES) {
                break;
            }

            $this->getOperationHistory()->addText('Starting account "' . $account->getTitle() . '"');

            $this->getOperationHistory()->addTimePoint(
                __METHOD__ . 'process' . $account->getId(),
                'Process account ' . $account->getTitle()
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

            $this->getOperationHistory()->saveTimePoint(__METHOD__ . 'process' . $account->getId());
        }
    }

    //########################################

    protected function getPermittedAccounts()
    {
        /** @var $accountsCollection Ess_M2ePro_Model_Resource_Amazon_Account_Collection */
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        $accountsCollection->getSelect()->where(
            'auto_invoicing = ' . AmazonAccount::AUTO_INVOICING_UPLOAD_MAGENTO_INVOICES .
            ' OR (' .
                'auto_invoicing = ' . AmazonAccount::AUTO_INVOICING_VAT_CALCULATION_SERVICE .
                ' AND ' .
                'invoice_generation = ' . AmazonAccount::INVOICE_GENERATION_BY_EXTENSION .
            ')'
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

        $failedChangesIds = array();
        $changesCount = count($relatedChanges);

        foreach ($relatedChanges as $change) {
            $changeParams = $change->getParams();

            /** @var Ess_M2ePro_Model_Order $order */
            $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', $change->getOrderId());

            if ($changeParams['invoice_source'] == AmazonOrder::INVOICE_SOURCE_MAGENTO) {
                if (($changeParams['document_type'] == AmazonOrderInvoice::DOCUMENT_TYPE_INVOICE &&
                        !$order->getChildObject()->canSendMagentoInvoice()) ||
                    ($changeParams['document_type'] == AmazonOrderInvoice::DOCUMENT_TYPE_CREDIT_NOTE &&
                        !$order->getChildObject()->canSendMagentoCreditmemo())
                ) {
                    $failedChangesIds[] = $change->getId();
                    continue;
                }

                $this->processMagentoDocument($account, $order, $change);

                if ($changesCount > 1) {
                    $this->trotlleProcess();
                }
                continue;
            }

            if ($changeParams['invoice_source'] == AmazonOrder::INVOICE_SOURCE_EXTENSION) {
                if (!$order->getChildObject()->canSendInvoiceFromReport()) {
                    $failedChangesIds[] = $change->getId();
                    continue;
                }

                $this->processExtensionDocument($order, $change);
            }
        }

        if (!empty($failedChangesIds)) {
            Mage::getResourceModel('M2ePro/Order_Change')->deleteByIds($failedChangesIds);
        }
    }

    /**
     * @param Ess_M2ePro_Model_Account $account
     * @param Ess_M2ePro_Model_Order $order
     * @param Ess_M2ePro_Model_Order_Change $change
     * @return void
     */
    protected function processMagentoDocument($account, $order, $change)
    {
        $magentoOrder = $order->getMagentoOrder();

        if ($magentoOrder === null) {
            return;
        }

        $changeParams = $change->getParams();
        $documentData = $this->getMagentoDocumentData($order, $changeParams['document_type']);

        $requestData = array(
            'change_id'       => $change->getId(),
            'order_id'        => $change->getOrderId(),
            'amazon_order_id' => $order->getChildObject()->getAmazonOrderId(),
            'document_number' => $documentData['document_number'],
            'document_type'   => $changeParams['document_type'],
            'document_pdf'    => $documentData['document_pdf'],
            'document_total_amount' => $magentoOrder->getGrandTotal(),
            'document_total_vat_amount' => $magentoOrder->getTaxAmount(),
        );

        /** @var $dispatcherObject Ess_M2ePro_Model_Amazon_Connector_Dispatcher */
        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
        /** @var Ess_M2ePro_Model_Cron_Task_Amazon_Order_SendInvoice_Requester $connectorObj */
        $connectorObj = $dispatcherObject->getCustomConnector(
            'Cron_Task_Amazon_Order_SendInvoice_Requester',
            array('order' => $requestData), $account
        );

        $dispatcherObject->process($connectorObj);
    }

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param $type
     * @return array
     * @throws Zend_Pdf_Exception
     */
    protected function getMagentoDocumentData($order, $type)
    {
        switch ($type) {
            case AmazonOrderInvoice::DOCUMENT_TYPE_INVOICE:
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

            case AmazonOrderInvoice::DOCUMENT_TYPE_CREDIT_NOTE:
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
            'document_pdf'    => $documentPdf
        );
    }

    //########################################

    protected function processExtensionDocument($order, $change)
    {
        $reportData = $order->getChildObject()->getSettings('invoice_data_report');

        $itemsByShippingId = $this->groupItemsByField($reportData['items'], 'shipping-id');

        foreach ($itemsByShippingId as $shippingData) {
            $itemsByInvoiceStatus = $this->groupItemsByField($shippingData, 'invoice-status');

            if (!empty($itemsByInvoiceStatus['InvoicePending'])) {
                $this->processExtensionDocumentInvoice($itemsByInvoiceStatus['InvoicePending'], $order, $change);
            }

            if (!empty($itemsByInvoiceStatus['CreditNotePending'])) {
                $this->processExtensionDocumentCreditNote($itemsByInvoiceStatus['CreditNotePending'], $order, $change);
            }
        }

        $order->setData('invoice_data_report', null);
        $order->save();
    }

    protected function processExtensionDocumentInvoice($items, $order, $change)
    {
        $invocieData = $order->getChildObject()->getSettings('invoice_data_report');
        $invocieData['shipping-id'] = $items[0]['shipping-id'];
        $invocieData['transaction-id'] = $items[0]['transaction-id'];
        $invocieData['items'] = $items;

        /** @var Ess_M2ePro_Model_Amazon_Order_Invoice $lastInvoice */
        $lastInvoice = Mage::getModel('M2ePro/Amazon_Order_Invoice')->getCollection()
            ->addFieldToFilter('document_type', AmazonOrderInvoice::DOCUMENT_TYPE_INVOICE)
            ->setOrder('create_date', Varien_Data_Collection::SORT_ORDER_DESC)
            ->getFirstItem();

        $lastInvoiceNumber = $lastInvoice->getDocumentNumber();

        /** @var Mage_Eav_Model_Entity_Increment_Numeric $incrementModel */
        $incrementModel = Mage::getModel('eav/entity_increment_numeric');
        $incrementModel->setPrefix('IN-')
            ->setPadLength(12)
            ->setLastId($lastInvoiceNumber);

        /** @var Ess_M2ePro_Model_Amazon_Order_Invoice $invoice */
        $invoice = Mage::getModel('M2ePro/Amazon_Order_Invoice');
        $invoice->addData(array(
            'order_id' => $order->getId(),
            'document_type' => AmazonOrderInvoice::DOCUMENT_TYPE_INVOICE,
            'document_number' => $incrementModel->getNextId()
        ));
        $invoice->setSettings('document_data', $invocieData);
        $invoice->save();

        /** @var Ess_M2ePro_Model_Amazon_Order_Invoice_Pdf_Invoice $orderPdfInvoice */
        $orderPdfInvoice = Mage::getModel('M2ePro/Amazon_Order_Invoice_Pdf_Invoice');
        $orderPdfInvoice->setOrder($order);
        $orderPdfInvoice->setInvocie($invoice);
        $pdf = $orderPdfInvoice->getPdf();

        $documentPdf = $pdf->render();

        $requestData = array(
            'change_id' => $change->getId(),
            'order_id'  => $change->getOrderId(),
            'amazon_order_id' => $invoice->getSetting('document_data', 'order-id'),
            'document_shipping_id' => $invoice->getSetting('document_data', 'shipping-id'),
            'document_transaction_id' => $invoice->getSetting('document_data', 'transaction-id'),
            'document_total_amount' => $orderPdfInvoice->getDocumentTotal(),
            'document_total_vat_amount' => $orderPdfInvoice->getDocumentVatTotal(),
            'document_type' => $invoice->getDocumentType(),
            'document_number' => $invoice->getDocumentNumber(),
            'document_pdf' => $documentPdf
        );

        /** @var $dispatcherObject Ess_M2ePro_Model_Amazon_Connector_Dispatcher */
        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
        /** @var Ess_M2ePro_Model_Cron_Task_Amazon_Order_SendInvoice_Requester $connectorObj */
        $connectorObj = $dispatcherObject->getCustomConnector(
            'Cron_Task_Amazon_Order_SendInvoice_Requester',
            array('order' => $requestData), $order->getAccount()
        );

        $dispatcherObject->process($connectorObj);
        $this->trotlleProcess();
    }

    protected function processExtensionDocumentCreditNote($items, $order, $change)
    {
        $itemsByTransactionId = $this->groupItemsByField($items, 'transaction-id');

        foreach ($itemsByTransactionId as $items) {
            $invocieData = $order->getChildObject()->getSettings('invoice_data_report');
            $invocieData['shipping-id'] = $items[0]['shipping-id'];
            $invocieData['transaction-id'] = $items[0]['transaction-id'];
            $invocieData['items'] = $items;

            /** @var Ess_M2ePro_Model_Amazon_Order_Invoice $lastInvoice */
            $lastInvoice = Mage::getModel('M2ePro/Amazon_Order_Invoice')->getCollection()
                ->addFieldToFilter('document_type', AmazonOrderInvoice::DOCUMENT_TYPE_CREDIT_NOTE)
                ->setOrder('create_date', Varien_Data_Collection::SORT_ORDER_DESC)
                ->getFirstItem();

            $lastInvoiceNumber = $lastInvoice->getDocumentNumber();

            /** @var Mage_Eav_Model_Entity_Increment_Numeric $incrementModel */
            $incrementModel = Mage::getModel('eav/entity_increment_numeric');
            $incrementModel->setPrefix('CN-')
                ->setPadLength(12)
                ->setLastId($lastInvoiceNumber);

            /** @var Ess_M2ePro_Model_Amazon_Order_Invoice $invoice */
            $invoice = Mage::getModel('M2ePro/Amazon_Order_Invoice');
            $invoice->addData(array(
                'order_id' => $order->getId(),
                'document_type' => AmazonOrderInvoice::DOCUMENT_TYPE_CREDIT_NOTE,
                'document_number' => $incrementModel->getNextId()
            ));
            $invoice->setSettings('document_data', $invocieData);
            $invoice->save();

            /** @var Ess_M2ePro_Model_Amazon_Order_Invoice_Pdf_CreditNote $orderPdfCreditNote */
            $orderPdfCreditNote = Mage::getModel('M2ePro/Amazon_Order_Invoice_Pdf_CreditNote');
            $orderPdfCreditNote->setOrder($order);
            $orderPdfCreditNote->setInvocie($invoice);
            $pdf = $orderPdfCreditNote->getPdf();

            $documentPdf = $pdf->render();

            $requestData = array(
                'change_id' => $change->getId(),
                'order_id'  => $change->getOrderId(),
                'amazon_order_id' => $invoice->getSetting('document_data', 'order-id'),
                'document_shipping_id' => $invoice->getSetting('document_data', 'shipping-id'),
                'document_transaction_id' => $invoice->getSetting('document_data', 'transaction-id'),
                'document_total_amount' => $orderPdfCreditNote->getDocumentTotal(),
                'document_total_vat_amount' => $orderPdfCreditNote->getDocumentVatTotal(),
                'document_type' => $invoice->getDocumentType(),
                'document_number' => $invoice->getDocumentNumber(),
                'document_pdf' => $documentPdf
            );

            /** @var $dispatcherObject Ess_M2ePro_Model_Amazon_Connector_Dispatcher */
            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
            /** @var Ess_M2ePro_Model_Cron_Task_Amazon_Order_SendInvoice_Requester $connectorObj */
            $connectorObj = $dispatcherObject->getCustomConnector(
                'Cron_Task_Amazon_Order_SendInvoice_Requester',
                array('order' => $requestData), $order->getAccount()
            );

            $dispatcherObject->process($connectorObj);
            $this->trotlleProcess();
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
        $changesCollection->addProcessingAttemptDateFilter();
        $changesCollection->addFieldToFilter('component', Ess_M2ePro_Helper_Component_Amazon::NICK);
        $changesCollection->addFieldToFilter('action', Ess_M2ePro_Model_Order_Change::ACTION_SEND_INVOICE);
        $changesCollection->getSelect()->joinLeft(
            array('pl' => Mage::getResourceModel('M2ePro/Processing_Lock')->getMainTable()),
            'pl.object_id = main_table.order_id AND pl.model_name = \'M2ePro/Order\'',
            array()
        );
        $changesCollection->addFieldToFilter('pl.id', array('null' => true));
        $changesCollection->getSelect()->limit(self::LIMIT_ORDER_CHANGES);
        $changesCollection->getSelect()->group(array('order_id'));

        $this->_maxOrderChangesPerTask += $changesCollection->count();

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

    protected function groupItemsByField($data, $field)
    {
        $groupedData = array();
        foreach ($data as $row) {
            $groupedData[$row[$field]][] = $row;
        }
        return $groupedData;
    }

    //########################################

    protected function trotlleProcess()
    {
        /**
         * Amazon trolling 1 request per 3 sec.
         */
        // @codingStandardsIgnoreLine
        sleep(3);
    }

    //########################################
}
