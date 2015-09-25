<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Ebay_Template_Builder_Abstract
{
    // ########################################

    abstract public function build(array $data);

    // ########################################

    protected function validate(array $data)
    {
        if (!isset($data['is_custom_template'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Policy mode is empty.');
        }
    }

    protected function prepareData(array &$data)
    {
        $prepared = array();

        //------------------------------
        if (isset($data['id']) && (int)$data['id'] > 0) {
            $prepared['id'] = (int)$data['id'];
        }

        $prepared['is_custom_template'] = (int)(bool)$data['is_custom_template'];
        $prepared['title'] = $data['title'];
        //------------------------------

        //------------------------------
        unset($data['id']);
        unset($data['is_custom_template']);
        unset($data['title']);
        //------------------------------

        return $prepared;
    }

    // ########################################
}