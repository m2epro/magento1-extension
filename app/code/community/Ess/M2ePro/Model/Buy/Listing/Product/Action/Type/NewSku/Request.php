<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_NewSku_Request
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Request
{
    // ########################################

    protected function getActionData()
    {
        return $this->getRequestNewProduct()->getData();
    }

    // -----------------------------------------

    protected function prepareFinalData(array $data)
    {
        if (!empty($data['core']['main_image'])) {
            $data['core']['main_image'] = str_replace('https://', 'http://', $data['core']['main_image']);
        }

        if (!empty($data['core']['additional_messages'])) {
            if (strpos($data['core']['additional_messages'], '|') === false) {
                $data['core']['additional_messages'] = str_replace(
                    'https://', 'http://', $data['core']['additional_messages']
                );
            } else {
                $images = explode('|', $data['core']['additional_messages']);

                foreach ($images as &$imageUrl) {
                    $imageUrl = str_replace('https://', 'http://', $imageUrl);
                }

                $data['core']['additional_messages'] = implode('|', $images);
            }
        }

        return $data;
    }

    // ########################################
}