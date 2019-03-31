<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Order_Item_OptionsFinder
{
    private $channelOptions = array();

    private $magentoOptions = array();

    private $magentoValue   = array();

    private $channelLabels  = array();
    /** @var Ess_M2ePro_Model_Magento_Product */
    private $magentoProduct;

    private $failedOptions  = array();

    private $optionsData    = array('associated_options'  => array(), 'associated_products' => array());
    /** @var bool */
    private $isNeedToReturnFirstOptionValues;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return $this
     */
    public function setProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setChannelOptions(array $options = array())
    {
        $this->channelOptions = $options;
        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function addChannelOptions(array $options = array())
    {
        $this->channelOptions = array_merge_recursive($this->channelOptions, $options);
        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setMagentoOptions(array $options = array())
    {
        $this->magentoOptions = $options;
        return $this;
    }

    //########################################

    /**
     * @throws \InvalidArgumentException
     */
    public function find()
    {
        $this->failedOptions = array();
        $this->optionsData   = array('associated_options'  => array(), 'associated_products' => array());

        if (is_null($this->getProductType()) || empty($this->magentoOptions)) {
            return;
        }

        if ($this->magentoProduct->isGroupedType()) {

            $associatedProduct = $this->getGroupedAssociatedProduct();

            if (is_null($associatedProduct)) {
                return;
            }

            $this->optionsData['associated_products'] = array($associatedProduct->getId());
            return;
        }

        $this->channelOptions = Mage::helper('M2ePro')->toLowerCaseRecursive($this->channelOptions);

        if (empty($this->channelOptions)) {
            $this->isNeedToReturnFirstOptionValues() && $this->matchFirstOptions();
            return;
        }

        $this->matchOptions();
    }

    //########################################

    /**
     * @return array
     */
    public function getOptionsData()
    {
        if (isset($this->optionsData['associated_products'])) {
            $this->optionsData['associated_products'] = Mage::helper('M2ePro/Magento_Product')
                 ->prepareAssociatedProducts(
                    $this->optionsData['associated_products'],
                    $this->magentoProduct
                 );
        }

        return $this->optionsData;
    }

    //########################################

    /**
     * @return bool
     */
    public function hasFailedOptions()
    {
        return count($this->failedOptions) > 0;
    }

    /**
     * @return array
     */
    public function getFailedOptions()
    {
        return $this->failedOptions;
    }

    /**
     * @return string
     */
    public function getOptionsNotFoundMessage()
    {
        if ($this->magentoProduct->isConfigurableType()) {
            $message = 'There is no associated Product found for Configurable Product.';
        } elseif ($this->magentoProduct->isGroupedType()) {
            $message = 'There is no associated Product found for Grouped Product.';
        } else {
            $message = sprintf(
                'Product Option(s) "%s" not found.',
                implode(', ', $this->failedOptions)
            );
        }

        return $message;
    }

    //########################################

    /**
     * @return array|null|string
     * @throws \InvalidArgumentException
     */
    private function getProductType()
    {
        if (is_null($this->magentoProduct)) {
            throw new \InvalidArgumentException('Magento Product was not set.');
        }

        $type = $this->magentoProduct->getTypeId();
        if (!in_array($type, $this->getAllowedProductTypes())) {
            throw new \InvalidArgumentException(sprintf('Product type "%s" is not supported.', $type));
        }

        return $type;
    }

    private function matchFirstOptions()
    {
        $options  = array();
        $products = array();

        foreach ($this->magentoOptions as $magentoOption) {
            $optionId = $magentoOption['option_id'];
            $valueId  = $magentoOption['values'][0]['value_id'];

            $options[$optionId] = $valueId;
            $products["{$optionId}::{$valueId}"] = $magentoOption['values'][0]['product_ids'];
        }

        $this->optionsData = array(
            'associated_options'  => $options,
            'associated_products' => $products
        );
    }

    private function matchOptions()
    {
        $options  = array();
        $products = array();

        foreach ($this->magentoOptions as $magentoOption) {
            $this->channelLabels = array();
            $this->magentoValue  = array();

            $magentoOption['labels'] = array_filter($magentoOption['labels']);
            if ($this->isOptionFailed($magentoOption)) {
                continue;
            }

            $this->appendOption($magentoOption, $options);
            $this->appendProduct($magentoOption, $products);
        }

        $this->optionsData = array(
            'associated_options'  => $options,
            'associated_products' => $products
        );
    }

    //########################################

    /**
     * @param array $magentoOption
     * @return bool
     */
    private function isOptionFailed(array $magentoOption)
    {
        $this->findChannelLabels($magentoOption['labels']);

        if (empty($this->channelLabels)) {
            $this->failedOptions[] = array_shift($magentoOption['labels']);
            return true;
        }

        $this->findMagentoValue($magentoOption['values']);

        if (empty($this->magentoValue) ||
            !isset($this->magentoValue['value_id']) ||
            !isset($this->magentoValue['product_ids'])) {

            $this->failedOptions[] = array_shift($magentoOption['labels']);
            return true;
        }

        return false;
    }

    /**
     * @param array $optionLabels
     */
    private function findChannelLabels(array $optionLabels)
    {
        $optionLabels = Mage::helper('M2ePro')->toLowerCaseRecursive($optionLabels);

        foreach ($optionLabels as $label) {
            if (isset($this->channelOptions[$label])) {
                $this->channelLabels = array('labels' => $this->channelOptions[$label]);
                return;
            }
        }
    }

    /**
     * @param array $magentoOptionValues
     */
    private function findMagentoValue(array $magentoOptionValues)
    {
        foreach ($magentoOptionValues as $optionValue) {
            $valueLabels = Mage::helper('M2ePro')->toLowerCaseRecursive($optionValue['labels']);

            foreach ((array)$this->channelLabels['labels'] as $channelOptionLabel) {
                if (in_array($channelOptionLabel, $valueLabels, true)) {
                    $this->magentoValue = $optionValue;
                    return;
                }
            }
        }
    }

    //########################################

    /**
     * @param array $magentoOption
     * @param array $options
     */
    private function appendOption(array $magentoOption, array &$options)
    {
        $optionId = $magentoOption['option_id'];
        $valueId  = $this->magentoValue['value_id'];

        $options[$optionId] = $valueId;
    }

    /**
     * @param array $magentoOption
     * @param array $products
     */
    private function appendProduct(array $magentoOption, array &$products)
    {
        $optionId = $magentoOption['option_id'];
        $valueId  = $this->magentoValue['value_id'];

        $products["{$optionId}::{$valueId}"] = $this->magentoValue['product_ids'];
    }

    //########################################

    private function getGroupedAssociatedProduct()
    {
        $variationName = array_shift($this->channelOptions);

        if ((is_null($variationName) || strlen(trim($variationName)) == 0) &&
            !$this->isNeedToReturnFirstOptionValues()) {

            return null;
        }

        foreach ($this->magentoOptions as $option) {
            // return product if it's name is equal to variation name
            if (is_null($variationName) || trim(strtolower($option->getName())) == trim(strtolower($variationName))) {
                return $option;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    private function isNeedToReturnFirstOptionValues()
    {
        if (!is_null($this->isNeedToReturnFirstOptionValues)) {
            return $this->isNeedToReturnFirstOptionValues;
        }

        $configGroup = '/order/magento/settings/';
        $configKey   = 'create_with_first_product_options_when_variation_unavailable';
        $configValue = (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue($configGroup, $configKey);

        return $this->isNeedToReturnFirstOptionValues = $configValue;
    }

    private function getAllowedProductTypes()
    {
        return array(
            Ess_M2ePro_Model_Magento_Product::TYPE_SIMPLE,
            Ess_M2ePro_Model_Magento_Product::TYPE_CONFIGURABLE,
            Ess_M2ePro_Model_Magento_Product::TYPE_BUNDLE,
            Ess_M2ePro_Model_Magento_Product::TYPE_GROUPED,
            Ess_M2ePro_Model_Magento_Product::TYPE_DOWNLOADABLE,
        );
    }

    //########################################
}
