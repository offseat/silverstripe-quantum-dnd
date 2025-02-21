<?php

namespace Silverstripe\Quantum\Admin;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\Requirements;
use Silverstripe\Quantum\Model\Collection;

class DashboardAdmin extends LeftAndMain
{
    private static $menu_title = "API Builder";

	private static $url_segment = "api-builder";

	private static $menu_priority = 1000;

	private static $url_priority = 30;

    private static $menu_icon_class = 'font-icon-happy';
    private static $allowed_actions = [
        'addCollection',
        'updateCollection',
    ];

    private static $tree_class = Collection::class;

    public function init() {
		parent::init();

        Requirements::javascriptTemplate("silverstripe/quantum-dnd: client/lib/index.js", [
            'getCollections' => $this->getCollections()
        ]);
        Requirements::css("silverstripe/quantum-dnd: client/dist/assets/index.css");
        Requirements::javascript("silverstripe/quantum-dnd: client/dist/assets/index.js");
        
        $this->extend('updateInit');
	}
    

    public function addCollection(): HTTPResponse
    {
        $newCol = json_decode($this->getRequest()->getBody(), true);

        $do = Collection::create();

        $replaceFields = [
            'name' => 'Name',
            'createdAt' => 'Created',
            'updatedAt' => 'LastEdited',
        ];

        foreach ($newCol as $key => $value) {
            if (array_key_exists($key, $replaceFields)) {
                $do->{$replaceFields[$key]} = Convert::raw2sql($value);
            }
        }

        $do->write();
        return HTTPResponse::create(json_encode($do->toMap()));
    
    }

    public function updateCollection(): HTTPResponse
    {
        $newColId = $this->request->param('ID');
        $newCol = json_decode($this->getRequest()->getBody(), true);

        if (!$newColId || (int) $newColId !== (int) $newCol['id']) {
            return $this->jsonError(401, 'Invalid ID');
        }

        $do = Collection::get()->byID($newColId);

        $replaceFields = [
            'name' => 'Name',
        ];

        foreach ($newCol as $key => $value) {
            if (array_key_exists($key, $replaceFields)) {
                $do->{$replaceFields[$key]} = Convert::raw2sql($value);
            }

            if ($key === 'fields') {
                // $do->Fields()->removeAll();
                // foreach ($value as $field) {
                //     $do->Fields()->add(DataObject::get_by_id($field['id']));
                // }
            }
        }

        $do->write();
        return HTTPResponse::create(json_encode($do->toMap()));
    }

    public function Content()
    {
        return $this->renderWith('Silverstripe/Quantum/Admin/DashboardAdmin_Content');
    }

    public function getCollections()
    {
        $collections = [];
        Collection::get()->each(function($col) use (&$collections) {
            array_push($collections, [
                'id' => $col->dbObject('ID')->raw(),
                'name' => $col->dbObject('Name')->raw(),
                'route' => $col->dbObject('Route')->raw(),
                'createdAt' => $col->dbObject('Created')->Raw(),
                'updatedAt' => $col->dbObject('LastEdited')->Raw(),
                'fields' => [],
            ]);
        });

        return json_encode($collections);
    }
}
