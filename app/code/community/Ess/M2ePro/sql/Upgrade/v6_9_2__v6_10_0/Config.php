<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */
class Ess_M2ePro_Sql_Upgrade_v6_9_2__v6_10_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    //########################################

    public function getFeaturesList()
    {
        return array(
            '@y20_m07/EbayTemplateCustomTemplateId',

            '@y20_m08/VCSLiteInvoices',

            '@y20_m10/ChangeSingleItemOption',
            '@y20_m10/AddInvoiceAndShipment',
            '@y20_m10/AddShipmentToAmazonListing',
            '@y20_m10/EbayRemoveCustomTemplates',
            '@y20_m10/AddGermanyInStorePickUp',
            '@y20_m10/AddITCAShippingRateTable',
            '@y20_m10/WalmartCustomCarrier',

            '@y20_m11/AddSkipEvtinSetting',
            '@y20_m11/RemoteFulfillmentProgram',
            '@y20_m11/SynchronizeInventoryConfigs',
            '@y20_m11/DisableVCSOnNL',
        );
    }

    //########################################
}
