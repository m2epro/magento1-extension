<?php

class Ess_M2ePro_Sql_Update_y24_m11_RemoveImageRelatedDataFromAmazonProcessing
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->removeImagesFromScheduledAction();
        $this->removeImagesFromProcessing();
    }

    /**
     * @return void
     */
    private function removeImagesFromScheduledAction()
    {
        $scheduledActionTable = $this->_installer->getFullTableName('listing_product_scheduled_action');

        $stmt = $this->_installer->getConnection()->select()
                     ->from(
                         $scheduledActionTable,
                         array('id', 'additional_data')
                     )
                     ->where('component = ?', 'amazon')
                     ->where('additional_data LIKE ?', '%images%')
                     ->query();

        while ($row = $stmt->fetch()) {
            $additionalData = json_decode($row['additional_data'], true);
            $isSaveRequired = false;

            if (!empty($additionalData['configurator']['allowed_data_types'])) {
                $key = array_search('images', $additionalData['configurator']['allowed_data_types']);
                if ($key) {
                    unset($additionalData['configurator']['allowed_data_types'][$key]);
                    $isSaveRequired = true;
                }
            }

            if ($isSaveRequired) {
                $value = json_encode($additionalData);
                $this->_installer->getConnection()->update(
                    $scheduledActionTable,
                    array('additional_data' => $value),
                    array('id = ?' => (int)$row['id'])
                );
            }
        }
    }

    /**
     * @return void
     */
    private function removeImagesFromProcessing()
    {
        $processingTable = $this->_installer->getFullTableName('processing');

        $stmt = $this->_installer->getConnection()->select()
                     ->from(
                         $processingTable,
                         array('id', 'params')
                     )
                     ->where('params LIKE ?', '%images%')
                     ->query();

        while ($row = $stmt->fetch()) {
            $params = json_decode($row['params'], true);
            $isSaveRequired = false;

            if (
                !empty($params['component'])
                && $params['component'] === 'Amazon'
                && !empty($params['configurator']['allowed_data_types'])
            ) {
                $key = array_search('images', $params['configurator']['allowed_data_types']);
                if ($key) {
                    unset($params['configurator']['allowed_data_types'][$key]);
                    $isSaveRequired = true;
                }
            }

            if ($isSaveRequired) {
                $value = json_encode($params);
                $this->_installer->getConnection()->update(
                    $processingTable,
                    array('params' => $value),
                    array('id = ?' => (int)$row['id'])
                );
            }
        }
    }
}