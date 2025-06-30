<?php

namespace DFT\SilverStripe\GridFieldAddOns;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;

/**
 * Allow DataObjects to specify a custom GridFieldDetailForm_ItemRequest class
 * via SilverStripe config.
 *
 * This class will then be used to build the GridFieldDetailForm automatically
 */
class GridFieldDetailFormExtension extends Extension
{

    /**
     * @param string $class
     * @param GridField $gridField
     * @param DataObject $record
     * @param RequestHandler $requestHandler
     * @param string $assignedClass Name of class explicitly assigned to this component
     */
    public function updateItemRequestClass(&$class, $gridField, $record, $requestHandler, $assignedClass = null)
    {
        // Avoid overriding explicitly assigned class name if set using setItemRequestClass()
        if ($assignedClass) {
            return;
        }

        $custom = $record->config()->get('gridfield_request_class');

        // if custom is a valid class, switch item request class
        if (isset($custom)
            && is_subclass_of($custom, GridFieldDetailForm_ItemRequest::class)
        ) {
            $class = $custom;
        }
    }
}
