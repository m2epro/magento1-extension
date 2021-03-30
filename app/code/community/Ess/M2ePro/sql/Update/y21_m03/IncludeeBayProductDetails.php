<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y21_m03_IncludeeBayProductDetails extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $ebayTemplateDescriptionTable = $this->_installer->getFullTableName('ebay_template_description');
        $query = $this->_installer->getConnection()
            ->select()
            ->from($ebayTemplateDescriptionTable, array('template_description_id', 'product_details'))
            ->query();

        while ($row = $query->fetch()) {
            $productDetails = (array)json_decode($row['product_details'], true);
            if (isset($productDetails['include_description'])) {
                $productDetails['include_ebay_details'] = $productDetails['include_description'];
                unset($productDetails['include_description']);

                $this->_installer->getConnection()->update(
                    $ebayTemplateDescriptionTable,
                    array(
                        'product_details' => json_encode($productDetails)
                    ),
                    array(
                        'template_description_id = ?'    => (int)$row['template_description_id']
                    )
                );
            }
        }
    }

    //########################################
}