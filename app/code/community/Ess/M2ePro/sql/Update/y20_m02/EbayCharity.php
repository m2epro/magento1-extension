<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m02_EbayCharity extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('ebay_template_selling_format'),
            array('charity' => null),
            '`charity` = "" OR `charity` = "[]" OR `charity` = "{}"'
        );

        $queryStmt = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('ebay_template_selling_format'))
            ->where('`charity` IS NOT NULL')
            ->query();

        while ($template = $queryStmt->fetch()) {
            $listingId = $this->_installer->getConnection()->select()
                ->from(
                    $this->_installer->getFullTableName('ebay_listing'),
                    array('listing_id')
                )
                ->where('template_selling_format_id = ?', $template['template_selling_format_id'])
                ->orWhere('template_selling_format_custom_id = ?', $template['template_selling_format_id'])
                ->query()
                ->fetchColumn();

            if (!$listingId) {
                $listingId = $this->_installer->getConnection()->select()
                    ->from(
                        array('elp' => $this->_installer->getFullTableName('ebay_listing_product')),
                        array()
                    )
                    ->joinLeft(
                        array('lp' => $this->_installer->getFullTableName('listing_product')),
                        'elp.listing_product_id = lp.id',
                        array('listing_id')
                    )
                    ->where('elp.template_selling_format_id = ?', $template['template_selling_format_id'])
                    ->orWhere('elp.template_selling_format_custom_id = ?', $template['template_selling_format_id'])
                    ->query()
                    ->fetchColumn();
            }

            $marketplaceId = $this->_installer->getConnection()->select()
                ->from(
                    $this->_installer->getFullTableName('listing'),
                    array('marketplace_id')
                )
                ->where('id = ?', $listingId)
                ->query()
                ->fetchColumn();

            $oldCharity = json_decode($template['charity'], true);

            if (isset($oldCharity[$marketplaceId])) {
                continue;
            }

            $newCharity = array(
                $marketplaceId => array(
                    'marketplace_id'      => $marketplaceId,
                    'organization_id'     => $oldCharity['id'],
                    'organization_name'   => $oldCharity['name'],
                    'organization_custom' => 1,
                    'percentage'          => $oldCharity['percentage'],
                )
            );

            $this->_installer->getConnection()->update(
                $this->_installer->getFullTableName('ebay_template_selling_format'),
                array('charity' => json_encode($newCharity)),
                "`template_selling_format_id` = {$template['template_selling_format_id']}"
            );
        }
    }

    //########################################
}