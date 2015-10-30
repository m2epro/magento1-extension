<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_ShippingOverride
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Abstract
{
    const TYPE_EXCLUSIVE = 'Exclusive';
    const TYPE_ADDITIVE = 'Additive';

    /**
     * @var Ess_M2ePro_Model_Amazon_Template_ShippingOverride
     */
    private $shippingOverrideTemplate = NULL;

    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        if (!$this->getConfigurator()->isShippingOverrideAllowed()) {
            return array();
        }

        if (!$this->getAmazonListingProduct()->isExistShippingOverrideTemplate()) {
            return array();
        }

        $data = array();

        foreach ($this->getShippingOverrideTemplate()->getServices(true) as $service) {

            /** @var Ess_M2ePro_Model_Amazon_Template_ShippingOverride_Service $service */

            $tempService = array(
                'option' => $service->getOption()
            );

            if ($service->isTypeRestrictive()) {
                $tempService['is_restricted'] = true;
            }

            if ($service->isTypeExclusive()) {
                $tempService['type'] = self::TYPE_EXCLUSIVE;
            }

            if ($service->isTypeAdditive()) {
                $tempService['type'] = self::TYPE_ADDITIVE;
            }

            if ($service->isTypeExclusive() || $service->isTypeAdditive()) {
                $tempService['amount'] = $service->getSource($this->getMagentoProduct())->getCost();
            }

            $data['shipping_data'][] = $tempService;
        }

        return $data;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_ShippingOverride
     */
    private function getShippingOverrideTemplate()
    {
        if (is_null($this->shippingOverrideTemplate)) {
            $this->shippingOverrideTemplate = $this->getAmazonListingProduct()->getShippingOverrideTemplate();
        }
        return $this->shippingOverrideTemplate;
    }

    //########################################
}