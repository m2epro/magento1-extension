<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m08_AmazonSkipTax extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $codes = array(
            'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DC', 'GA', 'HI', 'ID',
            'IL', 'IN', 'IA', 'KY', 'LA', 'ME', 'MD', 'MA', 'MI', 'MN', 'MS',
            'NE', 'NV', 'NJ', 'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'PA', 'PR',
            'RI', 'SC', 'SD', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY',
        );

        $query = $this->_installer->getConnection()
            ->select()
            ->from($this->_installer->getFullTableName('amazon_account'))
            ->where('marketplace_id = ?', 29)
            ->query();

        while ($row = $query->fetch()) {
            $data = Mage::helper('M2ePro')->jsonDecode($row['magento_orders_settings']);

            if (!$this->canAddExcludedStates($data)) {
                continue;
            }

            $data['tax']['amazon_collects'] = 1;
            $data['tax']['excluded_states'] = $codes;

            $this->_installer->getConnection()->update(
                $this->_installer->getFullTableName('amazon_account'),
                array('magento_orders_settings' => Mage::helper('M2ePro')->jsonEncode($data)),
                array('account_id' => $row['account_id'])
            );
        }
    }

    private function canAddExcludedStates($data)
    {
        if (!isset($data['tax']['mode'])) {
            return false;
        }

        if ($data['tax']['mode'] != 1 && $data['tax']['mode'] != 3) {
            return false;
        }

        return true;
    }

    //########################################
}
