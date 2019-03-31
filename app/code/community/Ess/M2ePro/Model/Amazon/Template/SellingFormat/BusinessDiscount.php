<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Template_SellingFormat_BusinessDiscount
    extends Ess_M2ePro_Model_Component_Abstract
{
    const MODE_PRODUCT   = 1;
    const MODE_SPECIAL   = 2;
    const MODE_ATTRIBUTE = 3;

    /**
     * @var Ess_M2ePro_Model_Template_SellingFormat
     */
    private $sellingFormatTemplateModel = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Template_SellingFormat_BusinessDiscount');
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->sellingFormatTemplateModel = NULL;
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        if (is_null($this->sellingFormatTemplateModel)) {
            $this->sellingFormatTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Amazon_Template_SellingFormat', $this->getTemplateSellingFormatId(), NULL, array('template')
            );
        }

        return $this->sellingFormatTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_SellingFormat $instance
     */
    public function setSellingFormatTemplate(Ess_M2ePro_Model_Template_SellingFormat $instance)
    {
        $this->sellingFormatTemplateModel = $instance;
    }

    //########################################

    /**
     * @return int
     */
    public function getTemplateSellingFormatId()
    {
        return (int)$this->getData('template_selling_format_id');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getQty()
    {
        return (int)$this->getData('qty');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getMode()
    {
        return (int)$this->getData('mode');
    }

    /**
     * @return bool
     */
    public function isModeProduct()
    {
        return $this->getMode() == self::MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isModeSpecial()
    {
        return $this->getMode() == self::MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isModeAttribute()
    {
        return $this->getMode() == self::MODE_ATTRIBUTE;
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getAttribute()
    {
        return (string)$this->getData('attribute');
    }

    /**
     * @return string
     */
    public function getCoefficient()
    {
        return (string)$this->getData('coefficient');
    }

    //########################################

    public function getSource()
    {
        return array(
            'mode'        => $this->getMode(),
            'coefficient' => $this->getCoefficient(),
            'attribute'   => $this->getAttribute(),
        );
    }

    //########################################

    /**
     * @return array
     */
    public function getAttributes()
    {
        $attributes = array();

        if ($this->isModeAttribute()) {
            $attributes[] = $this->getAttribute();
        }

        return $attributes;
    }

    //########################################
}