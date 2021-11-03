<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract
    extends Ess_M2ePro_Model_Template_ChangeProcessorAbstract
{
    //########################################

    const INSTRUCTION_TYPE_QTY_DATA_CHANGED              = 'template_qty_data_changed';
    const INSTRUCTION_TYPE_PRICE_DATA_CHANGED            = 'template_price_data_changed';
    const INSTRUCTION_TYPE_TITLE_DATA_CHANGED            = 'template_title_data_changed';
    const INSTRUCTION_TYPE_SUBTITLE_DATA_CHANGED         = 'template_subtitle_data_changed';
    const INSTRUCTION_TYPE_DESCRIPTION_DATA_CHANGED      = 'template_description_data_changed';
    const INSTRUCTION_TYPE_IMAGES_DATA_CHANGED           = 'template_images_data_changed';
    const INSTRUCTION_TYPE_VARIATION_IMAGES_DATA_CHANGED = 'template_variation_images_data_changed';
    const INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED       = 'template_categories_data_changed';
    const INSTRUCTION_TYPE_PARTS_DATA_CHANGED            = 'template_parts_data_changed';
    const INSTRUCTION_TYPE_PAYMENT_DATA_CHANGED          = 'template_payment_data_changed';
    const INSTRUCTION_TYPE_SHIPPING_DATA_CHANGED         = 'template_shipping_data_changed';
    const INSTRUCTION_TYPE_RETURN_DATA_CHANGED           = 'template_return_data_changed';
    const INSTRUCTION_TYPE_OTHER_DATA_CHANGED            = 'template_other_data_changed';

    //########################################
}
