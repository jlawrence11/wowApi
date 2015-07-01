<?php
namespace jlawrence\wowApi;

/*
 * This file will have at least two classes.  wowItems and wowItemCache.
 * wowItems will extend wowItemCache, and call the parent method before
 * processing the request.  If the cache exists and is new enough (cache class
 * will decide), will return the cached item in full rather than calling
 * the Blizzard API.
 */

class Item {
    protected $data;
    private $wowKeys;

    public function __construct() {
        // array containing accepted keys
        $this->wowKeys = Array('id',
            'description',
            'name',
            'icon',
            'stackable',
            'itemBind',
            'buyPrice',
            'itemClass',
            'itemSubClass',
            'containerSlots',
            'inventoryType',
            'equippable',
            'itemLevel',
            'maxCount',
            'maxDurability',
            'minFactionId',
            'minReputation',
            'quality',
            'sellPrice',
            'requiredSkill',
            'requiredLevel',
            'requiredSkillRank',
            'baseArmor',
            'hasSockets',
            'isAuctionable',
            'armor',
            'displayInfoId',
            'nameDescription',
            'nameDescriptionColor',
            'upgradable'
        );
        //initialize the data array with blank values
        $this->_empty();
    }

    public function __set($name, $value) {
        if(in_array($name, $this->wowKeys)) {
            $this->data[$name] = $value;
        }
    }

    public function __get($name) {
        if(in_array($name, $this->data)) {
            return $this->data[$name];
        } else {
            return false;
        }
    }

    protected function _empty() {
        foreach($this->wowKeys as $key) {
            $this->data[$key] = "";
        }
    }

}