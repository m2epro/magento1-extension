<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Payment_Service extends Ess_M2ePro_Model_Component_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Payment
     */
    protected $_paymentTemplateModel = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Payment_Service');
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->_paymentTemplateModel = null;
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Payment
     */
    public function getPaymentTemplate()
    {
        if ($this->_paymentTemplateModel === null) {
            $this->_paymentTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Ebay_Template_Payment', $this->getTemplatePaymentId(), NULL, array('template')
            );
        }

        return $this->_paymentTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Payment $instance
     */
    public function setPaymentTemplate(Ess_M2ePro_Model_Ebay_Template_Payment $instance)
    {
         $this->_paymentTemplateModel = $instance;
    }

    //########################################

    /**
     * @return int
     */
    public function getTemplatePaymentId()
    {
        return (int)$this->getData('template_payment_id');
    }

    public function getCodeName()
    {
        return $this->getData('code_name');
    }

    //########################################
}
