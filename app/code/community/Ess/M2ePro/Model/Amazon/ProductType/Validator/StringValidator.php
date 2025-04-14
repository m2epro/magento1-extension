<?php

class Ess_M2ePro_Model_Amazon_ProductType_Validator_StringValidator
    implements Ess_M2ePro_Model_Amazon_ProductType_Validator_ValidatorInterface
{
    /** @var string */
    private $fieldTitle = '';
    /** @var string */
    private $fieldGroup = '';
    /** @var bool */
    private $isRequired = false;
    /** @var array */
    private $errors = array();

    /**
     * @param mixed $value
     */
    public function validate($value)
    {
        $value = $this->tryConvertToString($value);
        if ($value === null) {
            $this->errors[] = sprintf(
                '[%s] The value of "%s" is invalid.',
                $this->fieldGroup,
                $this->fieldTitle
            );

            return false;
        }

        if (empty($value)) {
            $this->errors[] = sprintf(
                '[%s] The value of "%s" is missing.',
                $this->fieldGroup,
                $this->fieldTitle
            );

            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function isRequiredSpecific()
    {
        return $this->isRequired;
    }

    /**
     * @param string $fieldTitle
     * @return void
     */
    public function setFieldTitle($fieldTitle)
    {
        $this->fieldTitle = $fieldTitle;
    }

    /**
     * @param string $fieldGroup
     * @return void
     */
    public function setFieldGroup($fieldGroup)
    {
        $this->fieldGroup = $fieldGroup;
    }

    /**
     * @param bool $isRequired
     * @return void
     */
    public function setIsRequired($isRequired)
    {
        $this->isRequired = $isRequired;
    }

    /**
     * @param mixed $value
     */
    private function tryConvertToString($value)
    {
        if (
            is_string($value)
            || is_numeric($value)
            || $value === null
        ) {
            $value = (string)$value;

            return trim($value);
        }

        return null;
    }
}
