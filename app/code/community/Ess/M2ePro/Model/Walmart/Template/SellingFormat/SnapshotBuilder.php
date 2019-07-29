<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_SellingFormat_SnapshotBuilder
    extends Ess_M2ePro_Model_Template_SnapshotBuilder_Abstract
{
    //########################################

    public function getSnapshot()
    {
        $data = $this->model->getData();
        if (empty($data)) {
            return array();
        }

        /** @var Ess_M2ePro_Model_Walmart_Template_SellingFormat $childModel */
        $childModel = $this->model->getChildObject();

        $ignoredKeys = array(
            'id',
            'template_selling_format_id',
        );

        // ---------------------------------------
        $data['shipping_override_services'] = $childModel->getShippingOverrideServices();

        if (!is_null($data['shipping_override_services'])) {
            foreach ($data['shipping_override_services'] as &$shippingOverride) {
                foreach ($shippingOverride as $key => &$value) {
                    if (in_array($key, $ignoredKeys)) {
                        unset($shippingOverride[$key]);
                        continue;
                    }

                    !is_null($value) && !is_array($value) && $value = (string)$value;
                }
                unset($value);
            }
            unset($shippingOverride);
        }
        // ---------------------------------------

        // ---------------------------------------
        $data['promotions'] = $childModel->getPromotions();

        if (!is_null($data['promotions'])) {
            foreach ($data['promotions'] as &$promotion) {
                foreach ($promotion as $key => &$value) {
                    if (in_array($key, $ignoredKeys)) {
                        unset($promotion[$key]);
                        continue;
                    }

                    !is_null($value) && !is_array($value) && $value = (string)$value;
                }
                unset($value);
            }
            unset($promotion);
        }
        // ---------------------------------------

        return $data;
    }

    //########################################
}