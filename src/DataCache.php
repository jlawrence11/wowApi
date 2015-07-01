<?php
namespace jlawrence\wowApi;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of wowdatacache
 *
 * @author Jon Lawrence
 */
abstract class DataCache {
    protected static $uInterval = "-1 month";



    static protected function _getDB() {
        if(!is_null(static::$json)) {
            return true;
        }
        $slug = static::$slug;
        $site = Core::$sSite;
        $query = "SELECT * FROM ". $site->pdo->TP ."wowCache WHERE slug=?";
        $data = $site->pdo->query($query, array($slug), true);
        if((isset($data['lastUpdate']) && $data['lastUpdate'] < strtotime(static::$uInterval)) || !isset($data['lastUpdate'])) {
            return static::_getAPI();
        }
        static::$json = $data['json'];
        static::$array = json_decode($data['json'], true);
        return true;
    }
    
    static protected function _getAPI() {
        $slug = static::$slug;
        $url = Core::$API_HOST ."data/$slug";
        
        $site = Core::$sSite;
        $curl = $site->curl;
        $url = Core::urlAddKey($url);
        $curl->get($url);
        $json = $curl->response;
        $save = array(
            'slug' => $slug,
            'lastUpdate' => time(),
            'json' => $json
        );
        $query = "INSERT INTO ". $site->pdo->TP ."wowCache (slug, lastUpdate, json) VALUES "
                . "(:slug, :lastUpdate, :json) "
                . " ON DUPLICATE KEY UPDATE "
                . "lastUpdate = :lastUpdate, json = :json";
        $site->pdo->query($query, $save);
        static::$json = $json;
        static::$array = json_decode($json, true);
        return true;
    }
    
    static public function _get() {
        return static::_getDB();
    }
    
    static public function getJSON() {
        static::_get();
        return static::$json;
    }
    
    static public function getArray() {
        static::_get();
        return static::$array;
    }
    
    static public function _toData() {}
}
