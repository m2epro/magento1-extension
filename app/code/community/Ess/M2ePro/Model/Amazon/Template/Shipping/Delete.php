<?php

class Ess_M2ePro_Model_Amazon_Template_Shipping_Delete
{
    /** @var Ess_M2ePro_Model_Amazon_Dictionary_TemplateShipping_Repository */
    private $dictionaryTemplateShippingRepository;
    /** @var Ess_M2ePro_Model_Amazon_Template_Shipping_Repository */
    private $templateShippingRepository;

    public function __construct()
    {
        $this->dictionaryTemplateShippingRepository = Mage::getModel(
            'M2ePro/Amazon_Dictionary_TemplateShipping_Repository'
        );
        $this->templateShippingRepository = Mage::getModel(
            'M2ePro/Amazon_Template_Shipping_Repository'
        );
    }

    public function deleteByAccount(Ess_M2ePro_Model_Account $account)
    {
        $this->templateShippingRepository->deleteByAccount($account);
        $this->dictionaryTemplateShippingRepository->deleteByAccount($account);
    }
}