<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Servicing_Task_ProductVariationVocabulary extends Ess_M2ePro_Model_Servicing_Task
{
    //########################################

    /**
     * @return string
     */
    public function getPublicNick()
    {
        return 'product_variation_vocabulary';
    }

    //########################################

    /**
     * @return array
     */
    public function getRequestData()
    {
        $metadata = Mage::helper('M2ePro/Module_Product_Variation_Vocabulary')->getServerMetaData();
        !isset($metadata['version']) && $metadata['version'] = null;

        return array(
            'metadata' => $metadata
        );
    }

    public function processResponseData(array $data)
    {
        $helper = Mage::helper('M2ePro/Module_Product_Variation_Vocabulary');

        if (isset($data['data']) && is_array($data['data'])) {
            $helper->setServerData($data['data']);
        }

        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $helper->setServerMetadata($data['metadata']);
        }
    }

    //########################################
}