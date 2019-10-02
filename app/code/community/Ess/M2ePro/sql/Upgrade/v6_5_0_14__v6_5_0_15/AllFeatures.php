<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_5_0_14__v6_5_0_15_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        /** AmazonOrdersUpdateDetails */
        //########################################

        $installer->getMainConfigModifier()->insert('/cron/task/amazon/order/receive/details/', 'mode', '0');
        $installer->getMainConfigModifier()->insert('/cron/task/amazon/order/receive/details/', 'interval', '7200');

        /** AddWatermarks */
        //########################################

        $installer->getTableModifier('ebay_template_description')
            ->addColumn('watermark_mode', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'use_supersize_images', false, false)
            ->addColumn('watermark_image', 'LONGBLOB', 'NULL', 'watermark_mode', false, false)
            ->addColumn('watermark_settings', 'TEXT', 'NULL', 'watermark_image', false, false)
            ->commit();

        /** AmazonOrderProcessingRunner */
        //########################################

        if ($installer->getTablesObject()->isExists('processing')) {

            $queryStmt = $connection
                ->select()
                ->from($installer->getTablesObject()->getFullName('processing'))
                ->where("model LIKE 'M2ePro/Amazon_Connector_Orders_%_ProcessingRunner'")
                ->query();

            while($processing = $queryStmt->fetch()) {

                $params = (array)@json_decode($processing['params'], true);
                if (isset($params['responser_params']['order'])) {

                    switch ($processing['model']) {

                        case 'M2ePro/Amazon_Connector_Orders_Refund_ProcessingRunner':
                            $actionType = 'refund';
                            $lockName   = 'refund_order';
                            break;

                        case 'M2ePro/Amazon_Connector_Orders_Cancel_ProcessingRunner':
                            $actionType = 'cancel';
                            $lockName   = 'cancel_order';
                            break;

                        case 'M2ePro/Amazon_Connector_Orders_Update_ProcessingRunner':
                            $actionType = 'update';
                            $lockName   = 'update_shipping_status';
                            break;

                        default:
                            $actionType = NULL;
                            $lockName   = NULL;
                    }

                    $params['responser_params']['order']['action_type'] = $actionType;
                    $params['responser_params']['order']['lock_name']   = $lockName;
                }

                $connection->update(
                    $installer->getTablesObject()->getFullName('processing'),
                    array(
                        'model'  => 'M2ePro/Amazon_Connector_Orders_ProcessingRunner',
                        'params' => json_encode($params)
                    ),
                    array('id = ?' => $processing['id'])
                );
            }
        }

        /** RemoveShippingOverrideWizard */
        //########################################

        $installer->getConnection()->delete(
            $installer->getTablesObject()->getFullName('wizard'),
            array('nick = ?' => 'amazonShippingOverridePolicy')
        );
    }

    //########################################
}