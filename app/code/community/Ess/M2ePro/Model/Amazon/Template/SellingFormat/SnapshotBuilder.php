<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Template_SellingFormat_SnapshotBuilder
    extends Ess_M2ePro_Model_Template_SnapshotBuilder_Abstract
{
    //########################################

    public function getSnapshot()
    {
        $data = $this->_model->getData();
        if (empty($data)) {
            return array();
        }

        $data['business_discounts'] = $this->_model->getChildObject()->getBusinessDiscounts();

        foreach ($data['business_discounts'] as &$businessDiscount) {
            foreach ($businessDiscount as &$value) {
                $value !== null && !is_array($value) && $value = (string)$value;
            }
        }

        unset($value);

        return $data;
    }

    //########################################
}
