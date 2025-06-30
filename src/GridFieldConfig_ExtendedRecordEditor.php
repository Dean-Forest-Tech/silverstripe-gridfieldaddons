<?php

namespace DFT\SilverStripe\GridFieldAddOns;

use SilverStripe\GridFieldAddOns\GridFieldUserColumns;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;

class GridFieldConfig_ExtendedRecordEditor extends GridFieldConfig_RecordEditor
{

    function __construct($itemsPerPage = null)
    {
        parent::__construct($itemsPerPage);
        $this->addComponent(new GridFieldUserColumns());
    }
}
