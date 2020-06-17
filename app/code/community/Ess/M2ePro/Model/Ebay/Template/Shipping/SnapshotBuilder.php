<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Ebay_Template_Shipping getModel()
 */
class Ess_M2ePro_Model_Ebay_Template_Shipping_SnapshotBuilder extends Ess_M2ePro_Model_ActiveRecord_SnapshotBuilder
{
    //########################################

    public function getSnapshot()
    {
        $data = $this->getModel()->getData();
        if (empty($data)) {
            return array();
        }

        $data['services'] = $this->getModel()->getServices();
        $data['calculated_shipping'] = $this->getModel()->getCalculatedShipping()
            ? $this->getModel()->getCalculatedShipping()->getData()
            : array();

        $ignoredKeys = array(
            'id',
            'template_shipping_id',
        );

        foreach ($data['services'] as &$serviceData) {
            foreach ($serviceData as $key => &$value) {
                if (in_array($key, $ignoredKeys)) {
                    unset($serviceData[$key]);
                    continue;
                }

                $value !== null && !is_array($value) && $value = (string)$value;
            }

            unset($value);
        }

        unset($serviceData);

        foreach ($data['calculated_shipping'] as $key => &$value) {
            if (in_array($key, $ignoredKeys)) {
                unset($data['calculated_shipping'][$key]);
                continue;
            }

            $value !== null && !is_array($value) && $value = (string)$value;
        }

        return $data;
    }

    //########################################
}
