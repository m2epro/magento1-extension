<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Description_Diff extends Ess_M2ePro_Model_Template_Diff_Abstract
{
    //########################################

    public function isDifferent()
    {
        return $this->isTitleDifferent() ||
               $this->isSubtitleDifferent() ||
               $this->isDescriptionDifferent() ||
               $this->isImagesDifferent() ||
               $this->isVariationImagesDifferent() ||
               $this->isOtherDifferent();
    }

    //########################################

    public function isTitleDifferent()
    {
        $keys = array(
            'title_mode',
            'title_template',
        );

        return $this->isSettingsDifferent($keys);
    }

    public function isSubtitleDifferent()
    {
        $keys = array(
            'subtitle_mode',
            'subtitle_template',
        );

        return $this->isSettingsDifferent($keys);
    }

    public function isDescriptionDifferent()
    {
        $keys = array(
            'description_mode',
            'description_template',
        );

        return $this->isSettingsDifferent($keys);
    }

    public function isImagesDifferent()
    {
        $keys = array(
            'gallery_type',
            'image_main_mode',
            'image_main_attribute',
            'gallery_images_mode',
            'gallery_images_attribute',
            'gallery_images_limit',
            'default_image_url',
            'use_supersize_images',

            'watermark_mode',
            'watermark_image',
            'watermark_settings'
        );

        return $this->isSettingsDifferent($keys);
    }

    public function isVariationImagesDifferent()
    {
        $keys = array(
            'variation_images_mode',
            'variation_images_attribute',
            'variation_images_limit',
            'variation_configurable_images',
        );

        return $this->isSettingsDifferent($keys);
    }

    public function isOtherDifferent()
    {
        $keys = array(
            'condition_mode',
            'condition_value',
            'condition_attribute',
            'condition_note_mode',
            'condition_note_template',
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################
}