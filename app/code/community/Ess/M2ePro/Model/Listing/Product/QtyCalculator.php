<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Listing_Product_QtyCalculator
{
    /**
     * @var null|array
     */
    protected $_source = null;

    /**
     * @var null|Ess_M2ePro_Model_Listing_Product
     */
    protected $_product = null;

    /**
     * @var null|int
     */
    protected $_productValueCache = null;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $product
     * @return Ess_M2ePro_Model_Listing_Product_PriceCalculator
     */
    public function setProduct(Ess_M2ePro_Model_Listing_Product $product)
    {
        $this->_product = $product;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getProduct()
    {
        if ($this->_product === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Initialize all parameters first.');
        }

        return $this->_product;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    protected function getListing()
    {
        return $this->getProduct()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Component_Child_Abstract
     */
    protected function getComponentListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    protected function getSellingFormatTemplate()
    {
        return $this->getComponentProduct()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Component_Child_Abstract
     */
    protected function getComponentSellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @param null|string $key
     * @return array|mixed
     */
    protected function getSource($key = null)
    {
        if ($this->_source === null) {
            $this->_source = $this->getComponentSellingFormatTemplate()->getQtySource();
        }

        return ($key !== null && isset($this->_source[$key])) ?
            $this->_source[$key] : $this->_source;
    }

    /**
     * @return Ess_M2ePro_Model_Component_Child_Abstract
     */
    protected function getComponentProduct()
    {
        return $this->getProduct()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    protected function getMagentoProduct()
    {
        return $this->getProduct()->getMagentoProduct();
    }

    //########################################

    public function getProductValue()
    {
        if ($this->_productValueCache !== null) {
            return $this->_productValueCache;
        }

        $value = $this->getClearProductValue();

        $value = $this->applySellingFormatTemplateModifications($value);
        $value < 0 && $value = 0;

        return $this->_productValueCache = (int)floor($value);
    }

    public function getVariationValue(Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        $value = $this->getClearVariationValue($variation);

        $value = $this->applySellingFormatTemplateModifications($value);
        $value < 0 && $value = 0;

        return (int)floor($value);
    }

    //########################################

    protected function getClearProductValue()
    {
        switch ($this->getSource('mode')) {
            case Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_NUMBER:
                $value = (int)$this->getSource('value');
                break;

            case Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_ATTRIBUTE:
                $value = (int)$this->getMagentoProduct()->getAttributeValue($this->getSource('attribute'));
                break;

            case Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED:
                $value = (int)$this->getMagentoProduct()->getQty(false);
                break;

            case Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT:
                $value = (int)$this->getMagentoProduct()->getQty(true);
                break;

            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown Mode in Database.');
        }

        return $value;
    }

    protected function getClearVariationValue(Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        $value = 0;

        if ($this->getMagentoProduct()->isConfigurableType() ||
            $this->getMagentoProduct()->isSimpleTypeWithCustomOptions() ||
            $this->getMagentoProduct()->isGroupedType() ||
            $this->getMagentoProduct()->isDownloadableTypeWithSeparatedLinks()
        ) {
            $options = $variation->getOptions(true);
            $value = $this->getOptionBaseValue(reset($options));
        } else if ($this->getMagentoProduct()->isBundleType()) {
            $optionsQtyList = array();
            $optionsQtyArray = array();

            // grouping qty by product id
            foreach ($variation->getOptions(true) as $option) {
                if (!$option->getProductId()) {
                    continue;
                }

                $qty = $this->getOptionBaseValue($option);
                $bundleDefaultQty = $this
                    ->getMagentoProduct()
                    ->getBundleDefaultQty($option->getProductId());

                $qty /= $bundleDefaultQty;

                $optionsQtyArray[$option->getProductId()][] = $qty;
            }

            foreach ($optionsQtyArray as $optionQty) {
                $optionsQtyList[] = floor($optionQty[0]/count($optionQty));
            }

            !empty($optionsQtyArray) && $value = min($optionsQtyList);
        } else {
            throw new Ess_M2ePro_Model_Exception_Logic(
                'Unknown Product type.',
                array(
                                                           'listing_product_id' => $this->getProduct()->getId(),
                                                           'product_id' => $this->getMagentoProduct()->getProductId(),
                                                           'type'       => $this->getMagentoProduct()->getTypeId()
                )
            );
        }

        return $value;
    }

    //########################################

    protected function getOptionBaseValue(Ess_M2ePro_Model_Listing_Product_Variation_Option $option)
    {
        switch ($this->getSource('mode')) {
            case Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_NUMBER:
                $value = (int)$this->getSource('value');
                break;

            case Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_ATTRIBUTE:
                $value = (int)$option->getMagentoProduct()->getAttributeValue($this->getSource('attribute'));
                break;

            case Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED:
                $value = (int)$option->getMagentoProduct()->getQty(false);
                break;

            case Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT:
                $value = (int)$option->getMagentoProduct()->getQty(true);
                break;

            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown Mode in Database.');
        }

        return $value;
    }

    //########################################

    protected function applySellingFormatTemplateModifications($value)
    {
        $mode = $this->getSource('mode');

        if ($mode != Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_ATTRIBUTE &&
            $mode != Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED &&
            $mode != Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT) {
            return $value;
        }

        $value = $this->applyValuePercentageModifications($value);
        $value = $this->applyValueMinMaxModifications($value);

        return $value;
    }

    //########################################

    protected function applyValuePercentageModifications($value)
    {
        $percents = $this->getSource('qty_percentage');

        if ($value <= 0 || $percents < 0 || $percents == 100) {
            return $value;
        }

        $roundingFunction = (bool)Mage::helper('M2ePro/Module_Configuration')
            ->getQtyPercentageRoundingGreater() ? 'ceil' : 'floor';

        return (int)$roundingFunction(($value/100) * $percents);
    }

    //########################################

    protected function applyValueMinMaxModifications($value)
    {
        if ($value <= 0 || !$this->getSource('qty_modification_mode')) {
            return $value;
        }

        $minValue = $this->getSource('qty_min_posted_value');
        $value < $minValue && $value = 0;

        $maxValue = $this->getSource('qty_max_posted_value');
        $value > $maxValue && $value = $maxValue;

        return $value;
    }

    //########################################
}
