<?php

namespace DFT\SilverStripe\GridFieldAddOns;

use SilverStripe\View\Requirements;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;

class GridFieldExpandableForm implements GridField_URLHandler, GridField_HTMLProvider
{
    public $template = self::class;

    public $formorfields;

    public function __construct($formorfields = null)
    {
        $this->formorfields = $formorfields;
    }

    public function getURLHandlers($gridField)
    {
        return array(
            'expand/$ID' => 'handleItem',
        );
    }

    public function handleItem($gridField, $request)
    {
        $controller = $gridField->getForm()->getController();
        $record = $gridField->getList()->byId($request->param("ID"));
        $handler = Injector::inst()->create(
            GridFieldExpandableForm_ItemRequest::class,
            $gridField,
            $this,
            $record,
            $controller,
            'DetailForm',
            $this->formorfields
        );

        return $handler->handleRequest($request);
    }

    public function getHTMLFragments($gridField)
    {
        Requirements::javascript('dft/silverstripe-gridfield-addons:/javascript/GridFieldExpandableForm.js');
        Requirements::css('dft/silverstripe-gridfield-addons:/css/GridFieldExpandableForm.css');

        $gridField->addExtraClass('expandable-forms');
        $gridField->setAttribute('data-pseudo-form-url', $gridField->Link('expand'));

        return array();
    }
}
