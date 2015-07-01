<?php
namespace jlawrence\wowApi;

/**
 * The base class for the WoW API classes.
 *
 * @author Jon Lawrence
 * @copyright Copyright �2012-2015, Jon Lawrence
 * @license http://opensource.org/licenses/LGPL-2.1 LGPL 2.1 License
 * @package wowApi
 * @version 0.1
 */
class Core
{
    public static $REGION = 'US';
    public static $SERVER = 'Thrall';
    public static $HOST = '.api.batttle.net';
    public static $API_HOST = 'https://us.api.battle.net/wow/';
    protected static $API_KEY = '';

    public $site;
    public static $sSite;
    public $wowItems;
    public $wowItem;
    public $auctions;
    public $wowGuilds;
    public $wowCharacters;

    public function __construct($iniFile, $region = null, $server = null)
    {
        if(!is_null($region)) {
            $this->setRegion($region);
        }
        if(!is_null($server)) {
            $this->setServer($server);
        }

        $this->site = new FactoryLw($iniFile);
        self::$sSite = $this->site;
        //$this->site->debug->setDebug(true);

        $this->wowItem = new ItemCache($this->site);
        $this->wowItems = new Item();
        $this->auctions = new Auctions($this->site);
        $this->wowGuilds = new Guilds($this->site);
        $this->wowCharacters = new Characters($this->site);
    }
    
    public static function setServer($name)
    {
        self::$SERVER = $name;
    }
    
    public static function setRegion($name)
    {
        $name = strtoupper($name);
        self::$REGION = $name;
        self::$API_HOST = "https://". self::$REGION . self::$HOST ."/wow/";
    }

    public static function setApiKey($apiKey)
    {
        self::$API_KEY = $apiKey;
    }

    public static function urlAddKey($url)
    {
        if(stripos($url, '?') === false) {
            $url .= '?apikey='. self::$API_KEY;
        } else {
            $url .= '&apikey='. self::$API_KEY;
        }
        return $url;
    }
    
    public static function getID($name, $server=null, $region=null)
    {
        $server = !is_null($server) ? $server : self::$SERVER;
        $region = !is_null($region) ? $region : self::$REGION;
        $id = strtolower($region .'-'. $server .'-'. str_replace(' ', '_', $name));
        return $id;
    }
    
    public static function getRealmId($realm, $region = null)
    {
        $server = !is_null($realm) ? $realm : self::$SERVER;
        $region = !is_null($region) ? $region : self::$REGION;
        $id = strtolower($region .'-'. $server);
        return $id;
    }
}
