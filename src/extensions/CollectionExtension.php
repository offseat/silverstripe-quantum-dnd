<?php

namespace Silverstripe\Quantum\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;

class CollectionExtension extends Extension
{
    private static $db = [
        'Fields' => 'Text',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.Main', TextareaField::create('Fields', 'Fields'));
    }
}
