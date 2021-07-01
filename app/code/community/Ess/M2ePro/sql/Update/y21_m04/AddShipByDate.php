<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y21_m04_AddShipByDate extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('walmart_order')
            ->addColumn('shipping_date_to', 'DATETIME', 'NULL', 'shipping_price', true);
        $this->_installer->getTableModifier('ebay_order')
            ->addColumn('shipping_date_to', 'DATETIME', 'NULL', 'shipping_details', true);

        $this->_installer->getTableModifier('amazon_order')
            ->addColumn('shipping_date_to', 'DATETIME', 'NULL', 'shipping_price', true, false)
            ->addColumn('delivery_date_to', 'DATETIME', 'NULL', 'shipping_date_to', false, false)
            ->commit();

        // ---------------------------------------

        $query = $this->_installer->getConnection()
            ->select()
            ->from($this->_installer->getFullTableName('amazon_order'))
            ->query();

        while ($row = $query->fetch()) {
            $data = Mage::helper('M2ePro')->jsonDecode($row['shipping_dates']);

            $shippingDateTo = isset($data['ship']['to']) ? $data['ship']['to'] : '';
            $deliveryDateTo = isset($data['delivery']['to']) ? $data['delivery']['to'] : '';

            $this->_installer->getConnection()->update(
                $this->_installer->getFullTableName('amazon_order'),
                array(
                    'shipping_date_to' => $shippingDateTo,
                    'delivery_date_to' => $deliveryDateTo
                ),
                array('order_id = ?' => $row['order_id'])
            );
        }

        $this->_installer->getTableModifier('amazon_order')->dropColumn('shipping_dates');
    }

    //########################################
}
