<?php

namespace DFT\SilverStripe\GridFieldAddOns;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Core\Config\Configurable;

/**
 * Component that converts all date columns in the existing GridField into the
 * chosen format (by default it uses DateTime::Nice()).
 */
class GridFieldColumnDateFormatter implements GridField_ColumnProvider
{
    use Configurable;

    /**
     * `GridField` we are working with
     *
     * @var GridField
     */
    protected $grid_field;

    /**
     * Overwrite the date format (provided by config)
     * for this instance
     *
     * @var string
     */
    protected $date_type;

    /**
     * The date formatting method to use (this corresponds
     * to a Date method on the Date/DateTime data type).
     *
     * @var string
     */
    private static $default_date_type = ".Nice";

    /**
     * Find the column/header provider for this gridfield and then augment the
     * columns so that any dates are re-formatted.
     *
     * @param GridField $gridField Current gridfield
     * @param array     $columns   List reference of all column names.
     *
     * @return null
     */
    public function augmentColumns($grid_field, &$columns)
    {
        $this->setGridField($grid_field);
        $config = $grid_field->getConfig();
        $db = Config::inst()->get($grid_field->getModelClass(), "db");
        $fields = $this->findDateFields();

        // Does the current grid have an action column?
        $has_actions = in_array('Actions', $columns);

        // First setup columns
        foreach ($config->getComponents() as $component) {
            $is_header = ($component instanceof GridFieldSortableHeader);
            $is_columns = $this->isColumnProvider($component);

            // If we are working with a set of data columns, look for
            // date/datetime columns
            if ($is_columns && method_exists($component, "getDisplayFields")) {
                $display_fields = $component->getDisplayFields($grid_field);
                foreach ($fields as $field) {
                    $display_fields = $this->changeKeys(
                        $field["Sort"],
                        $field["Column"],
                        $display_fields
                    );
                }
                $component->setDisplayFields($display_fields);
                $columns = array_keys($display_fields);

                // Ensure actions are added back in (if unset)
                if ($has_actions && !in_array('Actions', $columns)) {
                    $columns[] = 'Actions';
                }
            }

            // If we are working with sortable headers, look for
            // date/datetime columns
            if ($is_header) {
                $sort_fields = [];
                foreach ($fields as $field) {
                    $sort_fields[$field["Column"]] = $field["Sort"];
                }

                // Merge new sort options, retaining any existing defined custom sorting
                $component->setFieldSorting(
                    array_merge(
                        $sort_fields,
                        $component->getFieldSorting()
                    )
                );
            }
        }
    }

    /**
     * Create an array of fields, titles and values that we
     * use to setup sortable fields in the following format:
     *
     * - Title (the human readable name of the column)
     * - Column (the actual field used to display data)
     * - Sort (DB the column used to sort the data)
     *
     * @return array
     */
    protected function findDateFields()
    {
        $grid_field = $this->getGridField();
        $config = $grid_field->getConfig();
        $class = $grid_field->getModelClass();
        $obj = $class::singleton();
        $fields = [];

        // First setup columns
        foreach ($config->getComponents() as $component) {
            // If we are working with a set of data columns, look for
            // date/datetime columns
            if ($this->isColumnProvider($component) && method_exists($component, "getDisplayFields")) {
                foreach ($component->getDisplayFields($grid_field) as $k => $v) {
                    $field = $obj->dbObject($k);
                    if (isset($field) && $field instanceof DBDate) {
                        $fields[] = [
                            "Title" => $v,
                            "Column" => $k . $this->getDateType(),
                            "Sort" => $k
                        ];
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * Is the provided component a `GridField_ColumnProvider`?
     *
     * @param object $component The current component
     *
     * @return boolean
     */
    protected function isColumnProvider($component)
    {
        $class = is_object($component) ? get_class($component) : $component;
        return ClassInfo::classImplements(
            $class,
            GridField_ColumnProvider::class
        );
    }

    /**
     * Change the array keys on the provided array to the provided alternative
     * (thanks to: https://stackoverflow.com/a/14227644/4161644)
     *
     * @param string $original Original key
     * @param string $new      New key
     * @param array  $array    Haystack array
     *
     * @return array
     */
    protected function changeKeys($original, $new, &$array)
    {
        foreach ($array as $k => $v) {
            $res[$k === $original ? $new : $k] = $v;
        }
        return $res;
    }

    /**
     * Get `GridField` we are working with
     *
     * @return  GridField
     */
    public function getGridField()
    {
        return $this->grid_field;
    }

    /**
     * Set `GridField` we are working with
     *
     * @param GridField $grid_field `GridField` we are working with
     *
     * @return self
     */
    public function setGridField(GridField $grid_field)
    {
        $this->grid_field = $grid_field;

        return $this;
    }

    /**
     * Get type for this instance
     *
     * @return string
     */
    public function getDateType()
    {
        if (!empty($this->date_type)) {
            return $this->date_type;
        } else {
            return $this->config()->default_date_type;
        }
    }

    /**
     * Set type for this instance
     *
     * @param string $date_type for this instance
     *
     * @return self
     */
    public function setDateType(string $date_type)
    {
        $this->date_type = $date_type;
        return $this;
    }

    /**
     * Component doesn't provide columns
     *
     * @param GridField $gridField Current GridField
     *
     * @return array
     */
    public function getColumnsHandled($gridField)
    {
        return array();
    }

    /**
     * Component doesn't provide columns
     *
     * @param GridField $gridField Current GridField
     *
     * @return array
     */
    public function getColumnMetadata($gridField, $columnName)
    {
        return array();
    }

    /**
     * Component doesn't provide columns
     *
     * @param GridField $gridField Current GridField
     *
     * @return array
     */
    public function getColumnContent($gridField, $record, $columnName)
    {
        return false;
    }

    /**
     * Component doesn't provide columns
     *
     * @param GridField $gridField Current GridField
     *
     * @return array
     */
    public function getColumnAttributes($gridField, $record, $columnName)
    {
        return array();
    }
}
