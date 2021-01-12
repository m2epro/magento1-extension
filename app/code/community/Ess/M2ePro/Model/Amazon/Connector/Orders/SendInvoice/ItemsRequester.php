<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Connector_Orders_SendInvoice_ItemsRequester
    extends Ess_M2ePro_Model_Amazon_Connector_Command_Pending_Requester
{
    /** @var string */
    protected $_rawPdfDoc;

    //########################################

    public function __construct(
        array $params = array(),
        Ess_M2ePro_Model_Account $account = null
    ) {
        $this->_rawPdfDoc = $params['order']['document_pdf'];
        unset($params['order']['document_pdf']);

        parent::__construct($params, $account);
    }

    //########################################

    public function getCommand()
    {
        return array('orders','send','invoice');
    }

    //########################################

    public function process()
    {
        parent::process();

        if ($this->getProcessingRunner()->getProcessingObject() == null) {
            return;
        }

        /** @var Ess_M2ePro_Model_Amazon_Order_Action_Processing $processingAction */
        $processingAction = $this->getProcessingRunner()->getProcessingAction();

        if (!empty($this->_processingServerHash)) {
            $requestPendingSingle = Mage::getModel('M2ePro/Request_Pending_Single');
            $requestPendingSingle->setData(
                array(
                    'component'       => Ess_M2ePro_Helper_Component_Amazon::NICK,
                    'server_hash'     => $this->_processingServerHash,
                    'expiration_date' => Mage::helper('M2ePro')->getDate(
                        Mage::helper('M2ePro')->getCurrentGmtDate(true)
                        + Ess_M2ePro_Model_Amazon_Order_Action_Processor::PENDING_REQUEST_MAX_LIFE_TIME
                    )
                )
            );
            $requestPendingSingle->save();

            Mage::getResourceModel('M2ePro/Amazon_Order_Action_Processing')->markAsInProgress(
                array($processingAction->getId()), $requestPendingSingle
            );
        }
    }

    //########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Amazon_Connector_Orders_ProcessingRunner';
    }

    protected function getProcessingParams()
    {
        return array_merge(
            parent::getProcessingParams(),
            array(
                'request_data' => $this->getRequestData(),
                'order_id'     => $this->_params['order']['order_id'],
                'change_id'    => $this->_params['order']['change_id'],
                'action_type'  => Ess_M2ePro_Model_Amazon_Order_Action_Processing::ACTION_TYPE_SEND_INVOICE,
                'lock_name'    => 'send_invoice_order',
                'start_date'   => Mage::helper('M2ePro')->getCurrentGmtDate(),
            )
        );
    }

    protected function buildRequestInstance()
    {
        $request = parent::buildRequestInstance();
        $request->setRawData($this->_rawPdfDoc);

        return $request;
    }

    //########################################

    public function getRequestData()
    {
        $requestData = array(
            'order_id' => $this->_params['order']['amazon_order_id'],
            'document_number' => $this->_params['order']['document_number'],
            'document_type' => $this->_params['order']['document_type']
        );

        if (isset($this->_params['order']['document_shipping_id'])) {
            $requestData['document_shipping_id'] = $this->_params['order']['document_shipping_id'];
        }

        if (isset($this->_params['order']['document_transaction_id'])) {
            $requestData['document_transaction_id'] = $this->_params['order']['document_transaction_id'];
        }

        if (isset($this->_params['order']['document_total_amount'])) {
            $requestData['document_total_amount'] = $this->_params['order']['document_total_amount'];
        }

        if (isset($this->_params['order']['document_total_vat_amount'])) {
            $requestData['document_total_vat_amount'] = $this->_params['order']['document_total_vat_amount'];
        }

        return $requestData;
    }

    //########################################
}
