<?php

interface Ess_M2ePro_Model_Amazon_ProductType_Validator_ValidatorInterface
{
    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function validate($value);

    public function getErrors();

    public function isRequiredSpecific();
}
