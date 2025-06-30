<?php

namespace DFT\SilverStripe\GridFieldAddOns;

use SilverStripe\GridFieldAddOns\GridFieldUserColumns;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;

class GridFieldConfig_ExtendedRelationEditor extends GridFieldConfig_RelationEditor
{

    function __construct($itemsPerPage = null)
    {
        parent::__construct($itemsPerPage);
        $this->addComponent(new GridFieldUserColumns());
    }
}
