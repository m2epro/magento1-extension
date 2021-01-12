<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m10_EbayRemoveCustomTemplates extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        if ($this->_installer->getTableModifier('ebay_listing')->isColumnExists('template_payment_mode')) {
            $listingTable = $this->_installer->getFullTableName('listing');
            $ebayListingTable = $this->_installer->getFullTableName('ebay_listing');
            $ebayTemplatePaymentTable = $this->_installer->getFullTableName('ebay_template_payment');
            $ebayTemplateShippingTable = $this->_installer->getFullTableName('ebay_template_shipping');
            $ebayTemplateReturnPolicyTable = $this->_installer->getFullTableName('ebay_template_return_policy');
            $templateDescriptionTable = $this->_installer->getFullTableName('template_description');
            $ebayTemplateDescriptionTable = $this->_installer->getFullTableName('ebay_template_description');
            $templateSellingFormatTable = $this->_installer->getFullTableName('template_selling_format');
            $ebayTemplateSellingFormatTable = $this->_installer->getFullTableName('ebay_template_selling_format');
            $templateSynchronizationTable = $this->_installer->getFullTableName('template_synchronization');
            $ebayTemplateSynchronizationTable = $this->_installer->getFullTableName('ebay_template_synchronization');

            $query = $this->_installer->getConnection()
                ->select()
                ->from($listingTable, array('id', 'title'))
                ->joinLeft(
                    $ebayListingTable,
                    'id = listing_id',
                    array(
                        'template_payment_mode',
                        'template_payment_id',
                        'template_shipping_mode',
                        'template_shipping_id',
                        'template_return_policy_mode',
                        'template_return_policy_id',
                        'template_description_mode',
                        'template_description_id',
                        'template_selling_format_mode',
                        'template_selling_format_id',
                        'template_synchronization_mode',
                        'template_synchronization_id',
                    )
                )
                ->where('component_mode = ?', 'ebay')
                ->query();

            while ($row = $query->fetch()) {
                if ($row['template_payment_mode'] == 1) {
                    $this->switchTemplateToNotCustom($ebayTemplatePaymentTable, $row['template_payment_id']);
                    $this->setTemplateTitle($ebayTemplatePaymentTable, $row['template_payment_id'], $row['title']);
                }

                if ($row['template_shipping_mode'] == 1) {
                    $this->switchTemplateToNotCustom($ebayTemplateShippingTable, $row['template_shipping_id']);
                    $this->setTemplateTitle($ebayTemplateShippingTable, $row['template_shipping_id'], $row['title']);
                }

                if ($row['template_return_policy_mode'] == 1) {
                    $this->switchTemplateToNotCustom($ebayTemplateReturnPolicyTable, $row['template_return_policy_id']);
                    $this->setTemplateTitle(
                        $ebayTemplateReturnPolicyTable, $row['template_return_policy_id'], $row['title']
                    );
                }

                if ($row['template_description_mode'] == 1) {
                    $this->switchTemplateToNotCustom(
                        $ebayTemplateDescriptionTable,
                        $row['template_description_id'],
                        'template_description_id'
                    );
                    $this->setTemplateTitle($templateDescriptionTable, $row['template_description_id'], $row['title']);
                }

                if ($row['template_selling_format_mode'] == 1) {
                    $this->switchTemplateToNotCustom(
                        $ebayTemplateSellingFormatTable,
                        $row['template_selling_format_id'],
                        'template_selling_format_id'
                    );
                    $this->setTemplateTitle(
                        $templateSellingFormatTable, $row['template_selling_format_id'], $row['title']
                    );
                }

                if ($row['template_synchronization_mode'] == 1) {
                    $this->switchTemplateToNotCustom(
                        $ebayTemplateSynchronizationTable,
                        $row['template_synchronization_id'],
                        'template_synchronization_id'
                    );
                    $this->setTemplateTitle(
                        $templateSynchronizationTable, $row['template_synchronization_id'], $row['title']
                    );
                }
            }

            $this->_installer->getTableModifier('ebay_listing')
                ->dropColumn('template_payment_mode', true, false)
                ->dropColumn('template_shipping_mode', true, false)
                ->dropColumn('template_return_policy_mode', true, false)
                ->dropColumn('template_description_mode', true, false)
                ->dropColumn('template_selling_format_mode', true, false)
                ->dropColumn('template_synchronization_mode', true, false)
                ->commit();
        }
    }

    //########################################

    protected function switchTemplateToNotCustom($table, $templateId, $idField = 'id')
    {
        $this->_installer->getConnection()->update(
            $table,
            array(
                'is_custom_template' => 0
            ),
            array(
                $idField . ' = ?'        => (int)$templateId,
                'is_custom_template = ?' => 1
            )
        );
    }

    protected function setTemplateTitle($table, $templateId, $title)
    {
        $this->_installer->getConnection()->update(
            $table,
            array(
                'title' => $title
            ),
            array(
                'id = ?'    => (int)$templateId,
                'title = ?' => ''
            )
        );
    }

    //########################################
}
