<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Tabs_Summary extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('controlPanelSummary');
        $this->setTemplate('M2ePro/controlPanel/tabs/summary.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->setChild(
            'actual_info',
            $this->getLayout()->createBlock('M2ePro/adminhtml_controlPanel_info_actual')
        );

        $this->setChild(
            'license_info',
            $this->getLayout()->createBlock('M2ePro/adminhtml_controlPanel_info_license')
        );

        //----------------------------------------

        $this->setChild(
            'cron_info',
            $this->getLayout()->createBlock('M2ePro/adminhtml_controlPanel_inspection_cron')
        );

        $this->setChild(
            'version_info',
            $this->getLayout()->createBlock('M2ePro/adminhtml_controlPanel_inspection_versionInfo')
        );

        //----------------------------------------

        $this->setChild(
            'database_general',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_controlPanel_info_mysqlTables',
                '',
                array(
                    'tables_list' => array(
                        'Config' => array(
                            'm2epro_config',
                            'm2epro_registry'
                        ),
                        'General' => array(
                            'm2epro_account',
                            'm2epro_listing',
                            'm2epro_listing_product',
                            'm2epro_listing_other'
                        ),
                        'Processing' => array(
                            'm2epro_processing',
                            'm2epro_processing_lock',
                            'm2epro_request_pending_single',
                            'm2epro_request_pending_partial',
                            'm2epro_connector_pending_requester_single',
                            'm2epro_connector_pending_requester_partial',
                        ),
                        'Additional' => array(
                            'm2epro_lock_item',
                            'm2epro_system_log',
                            'm2epro_listing_product_instruction',
                            'm2epro_listing_product_scheduled_action',
                            'm2epro_order_change',
                            'm2epro_operation_history',
                        ),
                    )
                )
            )
        );

        $this->setChild(
            'database_components',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_controlPanel_info_mysqlTables',
                '',
                array(
                    'tables_list' => array(
                        'Amazon' => array(
                            'm2epro_amazon_account',
                            'm2epro_amazon_item',
                            'm2epro_amazon_listing',
                            'm2epro_amazon_listing_product',
                            'm2epro_amazon_listing_other'
                        ),
                        'Ebay' => array(
                            'm2epro_ebay_account',
                            'm2epro_ebay_item',
                            'm2epro_ebay_listing',
                            'm2epro_ebay_listing_product',
                            'm2epro_ebay_listing_other'
                        ),
                        'Walmart' => array(
                            'm2epro_walmart_account',
                            'm2epro_walmart_item',
                            'm2epro_walmart_listing',
                            'm2epro_walmart_listing_product',
                            'm2epro_walmart_listing_other'
                        )
                    )
                )
            )
        );

        return parent::_beforeToHtml();
    }

    //########################################
}
