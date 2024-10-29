<?php

class Ess_M2ePro_Sql_Update_y24_m10_EbayAccountAddSiteColumn
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->addEbaySiteColumn();
        $this->fillEbaySiteColumn();
    }

    private function addEbaySiteColumn()
    {
        $modifier = $this->_installer->getTableModifier('ebay_account');

        $modifier->addColumn(
            'ebay_site',
            'VARCHAR(20) NOT NULL',
            null,
            'feedbacks_last_used_id',
            false,
            false
        );

        $modifier->commit();
    }

    private function fillEbaySiteColumn()
    {
        $tableName = $this->_installer->getFullTableName('ebay_account');
        $query = $this->_installer
            ->getConnection()
            ->select()
            ->from($tableName)
            ->query();

        while ($row = $query->fetch()) {
            if (!isset($row['info'])) {
                continue;
            }

            $infoData = json_decode($row['info'], true);

            if (
                !is_array($infoData)
                || !isset($infoData['Site'])
            ) {
                continue;
            }

            $this->_installer->getConnection()->update(
                $tableName,
                array('ebay_site' => $infoData['Site']),
                array('account_id' => $row['account_id'])
            );
        }
    }
}
