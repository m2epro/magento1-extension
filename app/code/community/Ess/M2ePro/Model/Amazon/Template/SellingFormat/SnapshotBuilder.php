<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Template_SellingFormat getModel()
 */
class Ess_M2ePro_Model_Amazon_Template_SellingFormat_SnapshotBuilder
    extends Ess_M2ePro_Model_ActiveRecord_SnapshotBuilder
{
    //########################################

    public function getSnapshot()
    {
        $data = $this->getModel()->getData();
        if (empty($data)) {
            return array();
        }

        $data['business_discounts'] = $this->getModel()->getChildObject()->getBusinessDiscounts();

        foreach ($data['business_discounts'] as &$businessDiscount) {
            foreach ($businessDiscount as &$value) {
                $value !== null && !is_array($value) && $value = (string)$value;
            }

            unset($value);
        }

        unset($businessDiscount);

        return $data;
    }

    //########################################
}
