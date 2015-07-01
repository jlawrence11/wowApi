<?php
namespace jlawrence\wowApi;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of wowclasses
 *
 * @author Jon Lawrence
 */
class Classes extends DataCache {
    protected static $slug = 'character/classes';
    protected static $data = null;
    protected static $json = null;
    protected static $array = null;
    
    static public function getById($id) {
        static::_toData();
        return static::$data[$id];
        
    }
    
    static public function _toData() {
        if(is_null(static::$data)) {
            static::_get();
            $data = array();
            foreach(static::$array['classes'] as $class) {
                $data[$class['id']] = $class['name'];
            }
            static::$data = $data;
        }
    }
}