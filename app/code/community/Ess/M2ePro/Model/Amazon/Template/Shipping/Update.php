<?php

class Ess_M2ePro_Model_Amazon_Template_Shipping_Update
{
    /**
     * @return void
     */
    public function process(Ess_M2ePro_Model_Account $account)
    {
        /** @var Ess_M2ePro_Model_Amazon_Connector_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');

        /** @var Ess_M2ePro_Model_Amazon_Connector_Template_Get_EntityRequester $connectorObj */
        $connectorObj = $dispatcher->getConnector(
            'template',
            'get',
            'entityRequester',
            array(),
            $account->getId()
        );

        $dispatcher->process($connectorObj);
        $data = $connectorObj->getResponseData();

        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        /** @var Varien_Db_Adapter_Pdo_Mysql $connection */
        $connection = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);

        $tableDictionaryTemplateShipping = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_dictionary_template_shipping');

        $connection->delete(
            $tableDictionaryTemplateShipping,
            array('account_id = ?' => $account->getId())
        );

        if (empty($data['templates'])) {
            return;
        }

        foreach ($data['templates'] as $template) {
            $connection->insert($tableDictionaryTemplateShipping, $template);
        }
    }
}
