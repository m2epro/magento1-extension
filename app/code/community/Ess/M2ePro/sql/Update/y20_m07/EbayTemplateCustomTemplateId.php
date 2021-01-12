<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m07_EbayTemplateCustomTemplateId
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    private $_templates = array(
        'payment',
        'shipping',
        'description',
        'return_policy',
        'selling_format',
        'synchronization'
    );

    //########################################

    public function execute()
    {
        $this->updateTable('ebay_listing');
        $this->updateTable('ebay_listing_product');
    }

    private function updateTable($tableName)
    {
        $modifier = $this->_installer->getTableModifier($tableName);

        foreach ($this->_templates as $template) {
            if (!$modifier->isColumnExists("template_{$template}_custom_id")) {
                continue;
            }

            $this->_installer->run(
                <<<SQL
UPDATE `{$this->_installer->getFullTableName($tableName)}`
SET `template_{$template}_id` = template_{$template}_custom_id
WHERE `template_{$template}_mode` = 1

SQL
            );
        }

        $this->_installer->getTableModifier($tableName)
            ->dropColumn("template_payment_custom_id", true, false)
            ->dropColumn("template_shipping_custom_id", true, false)
            ->dropColumn("template_description_custom_id", true, false)
            ->dropColumn("template_return_policy_custom_id", true, false)
            ->dropColumn("template_selling_format_custom_id", true, false)
            ->dropColumn("template_synchronization_custom_id", true, false)
            ->commit();
    }

    //########################################
}
