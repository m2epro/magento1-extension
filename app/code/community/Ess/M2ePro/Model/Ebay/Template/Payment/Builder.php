<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2EPro_Model_Ebay_Template_Payment_Builder
    extends Ess_M2ePro_Model_Ebay_Template_AbstractBuilder
{
    //########################################

    public function build($model, array $rawData)
    {
        /** @var Ess_M2ePro_Model_Ebay_Template_Payment $model */
        $model = parent::build($model, $rawData);

        $services = $model->getServices(true);
        foreach ($services as $service) {
            $service->deleteInstance();
        }

        if (empty($this->_rawData['services']) || !is_array($this->_rawData['services'])) {
            return $model;
        }

        foreach ($this->_rawData['services'] as $codeName) {
            $this->createService($model->getId(), $codeName);
        }

        return $model;
    }

    //########################################

    protected function validate()
    {
        if (empty($this->_rawData['marketplace_id'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Marketplace ID is empty.');
        }

        parent::validate();
    }

    protected function prepareData()
    {
        $this->validate();

        $data = parent::prepareData();

        $data['marketplace_id'] = (int)$this->_rawData['marketplace_id'];

        if (isset($this->_rawData['pay_pal_mode'])) {
            $data['pay_pal_mode'] = (int)(bool)$this->_rawData['pay_pal_mode'];
        } else {
            $data['pay_pal_mode'] = 0;
        }

        if (isset($this->_rawData['pay_pal_email_address'])) {
            $data['pay_pal_email_address'] = $this->_rawData['pay_pal_email_address'];
        }

        $data['pay_pal_immediate_payment'] = 0;
        if (isset($this->_rawData['pay_pal_immediate_payment'])) {
            $data['pay_pal_immediate_payment'] = (int)(bool)$this->_rawData['pay_pal_immediate_payment'];
        }

        return $data;
    }

    //########################################

    protected function createService($templatePaymentId, $codeName)
    {
        $data = array(
            'template_payment_id' => $templatePaymentId,
            'code_name' => $codeName
        );

        $model = Mage::getModel('M2ePro/Ebay_Template_Payment_Service');
        $model->addData($data);
        $model->save();

        return $model;
    }

    //########################################

    public function getDefaultData()
    {
        return array(
            'pay_pal_mode'              => 0,
            'pay_pal_email_address'     => '',
            'pay_pal_immediate_payment' => 0,
            'services'                  => array()
        );
    }

    //########################################
}
