<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y21_m10_UpdateWatermarkImage extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $ebayTemplateDescription =  $this->_installer->getFullTableName('ebay_template_description');

        $query = $this->_installer->getConnection()
            ->select()
            ->from($ebayTemplateDescription)
            ->query();

        while ($row = $query->fetch()) {
            if ($row['watermark_image'] !== null) {
                $newWatermarkImage = base64_encode($row['watermark_image']);

                $this->_installer->getConnection()->update(
                    $ebayTemplateDescription,
                    array('watermark_image' => $newWatermarkImage),
                    array('template_description_id = ?' => $row['template_description_id'])
                );
            }
        }

        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_description');
    }

    //########################################
}
