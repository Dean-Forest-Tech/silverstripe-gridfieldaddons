<?php

namespace DFT\SilverStripe\GridFieldAddOns;

use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Control\Controller;
use SilverStripe\Control\RequestHandler;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Control\PjaxResponseNegotiator;
use SilverStripe\GridFieldAddOns\GridFieldExpandableForm;

class GridFieldExpandableForm_ItemRequest extends RequestHandler
{

    private static $url_handlers = array(
        '$Action!' => '$Action',
        '' => 'edit',
    );

    private static $allowed_actions = array(
        'edit',
        'ExpandableForm'
    );

    protected $gridfield;
    protected $component;
    protected $record;
    protected $controller;
    protected $name;
    protected $formorfields;
    protected $template = GridFieldExpandableForm::class;

    public function __construct($gridfield, $component, $record, $controller, $name, $formorfields)
    {
        $this->gridfield = $gridfield;
        $this->component = $component;
        $this->record = $record;
        $this->controller = $controller;
        $this->name = $name;
        $this->formorfields = $formorfields;
        parent::__construct();
    }

    public function edit($request)
    {
        $form = $this->ExpandableForm($this->gridField, $request);

        return $this
            ->customise(['ExpandableForm' => $form])
            ->renderWith($this->template);
    }

    /**
     * Generate a form to allow editing of a reord
     *
     * @return \SilverStripe\Forms\Form
     */
    public function ExpandableForm()
    {
        $record = $this->record;

        if (!$record->canView()) {
            $controller = $this->getToplevelController();
            return $controller->httpError(403);
        }

        if ($this->formorfields instanceof FieldList) {
            $fields = $this->formorfields;
        } elseif ($this->formorfields instanceof ViewableData) {
            $form = $this->formorfields;
        } elseif ($this->record->hasMethod('getExandableForm')) {
            $form = $this->record->getExandableForm($this, __FUNCTION__);
            $this->record->extend('updateExandableForm', $form);
        } elseif ($this->record->hasMethod('getExandableFormFields')) {
            $fields = $this->record->getExandableFormFields();
            $this->record->extend('updateExandableFormFields', $fields);
        } else {
            $fields = $this->record->scaffoldFormFields();
            $this->record->extend('updateExandableFormFields', $fields);
        }

        if (empty($form)) {
            $actions = new FieldList();
            $actions->push(
                FormAction::create('doSave', _t('GridFieldDetailForm.Save', 'Save'))
                    ->setUseButtonTag(true)
                    ->addExtraClass('ss-ui-action-constructive btn-primary font-icon-save')
                    ->setAttribute('data-icon', 'accept')
                    ->setAttribute('data-action-type', 'default')
            );

            $form = new Form(
                $this,
                'ExpandableForm',
                $fields,
                $actions
            );
        }

        if ($this->validator) {
            $form->setValidator($this->validator);
        }

        $form->loadDataFrom($this->record, Form::MERGE_DEFAULT);

        $form->IncludeFormTag = false;

        // Ensure form is made readonly if editing not allowed
        if (!$record->canEdit()) {
            $form->makeReadonly();
        }

        return $form;
    }

    public function doSave($data, $form)
    {
        // Check permission
        if (!$this->record->canEdit()) {
            $controller = $this->getToplevelController();
            return $controller->httpError(403);
        }

        try {
            $form->saveInto($this->record);
            $this->record->write();
            $list = $this->gridfield->getList();
            if ($list instanceof ManyManyList) {
                $extradata = array_intersect_key($data, $list->getField('extraFields'));
                $list->add($this->record, $extradata);
            } else {
                $list->add($this->record);
            }
        } catch (ValidationException $e) {
            $form->sessionMessage($e->getResult()->message(), 'bad');
            $responseNegotiator = new PjaxResponseNegotiator(array(
                'CurrentForm' => function () use (&$form) {
                    return $form->forTemplate();
                },
                'default' => function () use (&$controller) {
                    return $controller->redirectBack();
                }
            ));
            if ($controller->getRequest()->isAjax()) {
                $controller->getRequest()->addHeader('X-Pjax', 'CurrentForm');
            }
            return $responseNegotiator->respond($controller->getRequest());
        }
        return $this->customise(array('ExpandableForm' => $form))->renderWith($this->template);
    }

    public function doDelete($data, $form)
    {
        try {
            if (!$this->record->canDelete()) {
                throw new ValidationException(
                    _t('GridFieldDetailForm.DeletePermissionsFailure', "No delete permissions"),
                    0
                );
            }

            $this->record->delete();
        } catch (ValidationException $e) {
            $form->sessionMessage($e->getResult()->message(), 'bad');
            return Controller::curr()->redirectBack();
        }
        return 'deleted';
    }

    protected function getToplevelController()
    {
        $c = $this->popupController;
        while ($c && $c instanceof GridFieldExpandableForm_ItemRequest) {
            $c = $c->getController();
        }
        return $c;
    }
    
    public function Link($action = null)
    {
        return Controller::join_links(
            $this->gridfield->Link('expand'),
            $this->record->ID ? $this->record->ID : 'new',
            $action
        );
    }
}
