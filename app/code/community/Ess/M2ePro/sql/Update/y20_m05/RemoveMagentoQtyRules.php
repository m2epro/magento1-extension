<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m05_RemoveMagentoQtyRules extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        foreach (array('ebay', 'amazon', 'walmart') as $component) {
            $tableModifier = $this->_installer->getTableModifier("{$component}_template_synchronization");

            foreach (array('list', 'relist', 'stop') as $action) {
                if (!$tableModifier->isColumnExists("{$action}_qty_magento")) {
                    continue;
                }

                $this->_installer->getConnection()
                    ->update(
                        $tableModifier->getTableName(),
                        array(
                            "{$action}_qty_calculated"       => new Zend_Db_Expr("{$action}_qty_magento"),
                            "{$action}_qty_calculated_value" => new Zend_Db_Expr("{$action}_qty_magento_value"),
                        ),
                        "{$action}_qty_calculated = 0 AND {$action}_qty_magento <> 0"
                    );

                $this->_installer->getConnection()
                    ->update(
                        $tableModifier->getTableName(),
                        array("{$action}_qty_calculated" => '1'),
                        "{$action}_qty_calculated <> 0"
                    );

                $tableModifier
                    ->dropColumn("{$action}_qty_magento", true, false)
                    ->dropColumn("{$action}_qty_magento_value", true, false)
                    ->dropColumn("{$action}_qty_magento_value_max", true, false)
                    ->dropColumn("{$action}_qty_calculated_value_max", true, false)
                    ->commit();
            }
        }
    }

    //########################################
}
