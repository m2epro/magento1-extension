<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y21_m04_AmazonRelistPrice extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $scheduledAction = $this->_installer->getTablesObject()->getFullName('listing_product_scheduled_action');

        $stmt = $this->_installer->getConnection()->select()
            ->from(
                $scheduledAction,
                array('id', 'tag')
            )
            ->where('component = ?', 'amazon')
            ->where('tag LIKE ?', '%price_regular%')
            ->orWhere('tag LIKE ?', '%price_business%')
            ->query();

        while ($row = $stmt->fetch()) {
            $tags = array_filter(
                explode('/', $row['tag']),
                function ($tag) {
                    return !empty($tag) && $tag !== 'price_regular' && $tag !== 'price_business';
                }
            );

            $tags[] = 'price';

            $tags = '/' . implode('/', $tags) . '/';

            $this->_installer->getConnection()->update(
                $scheduledAction,
                array('tag' => $tags),
                array('id = ?' => (int)$row['id'])
            );
        }
    }

    //########################################
}
