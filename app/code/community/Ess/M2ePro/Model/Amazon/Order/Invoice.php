<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Order_Invoice extends Ess_M2ePro_Model_Component_Abstract
{
    const DOCUMENT_TYPE_INVOICE = 'invoice';
    const DOCUMENT_TYPE_CREDIT_NOTE = 'credit_note';

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Order_Invoice');
    }

    //########################################

    public function getOrderId()
    {
        return $this->getData('order_id');
    }

    public function getDocumentType()
    {
        return $this->getData('document_type');
    }

    public function getDocumentNumber()
    {
        return $this->getData('document_number');
    }

    public function getDocumentData()
    {
        return $this->getSettings('document_data');
    }

    //########################################

    public function deleteInstance()
    {
        return $this->delete();
    }

    //########################################
}
