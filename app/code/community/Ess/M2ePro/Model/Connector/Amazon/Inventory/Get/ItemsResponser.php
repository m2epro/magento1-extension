<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Amazon_Inventory_Get_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Amazon_Responser
{
    // ########################################

    protected function validateResponseData($response)
    {
        if (!isset($response['data'])) {
            return false;
        }

        return true;
    }

    protected function prepareResponseData($response)
    {
        $preparedData = array(
            'data'      => array(),
            'next_part' => $response['next_part'],
        );

        foreach ($response['data'] as $receivedItem) {
            if (empty($receivedItem['identifiers']['sku'])) {
                continue;
            }

            $preparedData['data'][$receivedItem['identifiers']['sku']] = $receivedItem;
        }

        return $preparedData;
    }

    // ########################################
}