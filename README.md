# SilverStripe GridFieldAddOns

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/i-lateral/silverstripe-GridFieldAddOns/badges/quality-score.png?b=2)](https://scrutinizer-ci.com/g/i-lateral/silverstripe-GridFieldAddOns/?branch=2)

## Introduction

GridFieldAddOns is a collection of 5 components to extend the Silverstripe GridField:

### [GridFieldExpandableForm](docs/en/GridFieldExpandableForm.md)

GridFieldExpandableForm is a GridField component to display a form for a GridField item like GridFieldDetailForm does, but within the GridField. It expands the item in the fashion of a jQueryUI accordion effect instead of opening the form in the main part of the UI.

### [GridFieldRecordHighlighter](docs/en/GridFieldRecordHighlighter.md)

GridFieldRecordHighlighter highlights records in a GridField.

### [GridFieldUserColumns](docs/en/GridFieldUserColumns.md)

GridFieldUserColumns gives users control over the columns of the GridField.

### [GridFieldColumnDateFormatter](docs/en/GridFieldColumnDateFormatter.md)

Allows you to re-format any dates on the GridField column provider while retaining sorting.

### [GridfieldCustomDetailForm](docs/en/GridFieldCustomDetailForm.md)

Allows you to define custom `GridFieldDetailForm_ItemRequest` for your `DataObject` via config.

## Requirements

SilverStripe Framework 5+

**NOTE** For SilverStripe 3 support use the `1` branch, for SilverStripe 4, use branch `2`.

## Installation

Instalation is done via composer: `composer require dft/silverstripe-gridfieldaddons`

## Maintainers

* Andreas Piening <piening at 3online dot de>
* Mark Anderson <mark at ilateral dot co dot uk>
* Morven Lewis-Everley <morven at dean-forest-tech dot uk>