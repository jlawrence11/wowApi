<?php
namespace jlawrence\wowApi;
use jlawrence\lw;

/**
 * wowItemCache
 *
 * @author Jon Lawrence
 */

class ItemCache extends Item {
    
    /**
     * @var FactoryLw  The site, for DB access
     */
    private $site;
    /** @var  \Curl\Curl */
    private $curl;
    
    public function __construct(FactoryLw $site) {
        parent::__construct();
        $this->site = $site;
        $this->curl = $site->curl;
    }
    
    public function __toString() {
        return print_r($this->data, true);
    }
    
    protected function getApi($id) {
        $url = Core::$API_HOST ."item/$id";
        $url = Core::urlAddKey($url);
        $this->curl->get($url);
        $json = $this->curl->response;
        $jArray = json_decode($json, true);
        if(isset($jArray['status']) && $jArray['status'] == 'nok') {
            //Status error, update current item to reflect, and return
            $this->_empty();
            $this->data['lastUpdate'] = null;
            $this->data['fullJSON'] = $json;
            $this->site->debug->notice("wowItem($id) does not exist");
            return null;
        }
        foreach($jArray as $key => $value) {
            parent::__set($key, $value);
        }
        $this->data['lastUpdate'] = time();
        $this->data['fullJSON'] = $json;
        $this->saveItem();
        //$this->saveIcon();
        $this->site->debug->notice("Updated Item($id) from API");
    }
    
    protected function getDB($id) {
        $query = "SELECT * FROM ". $this->site->pdo->TP ."itemCache WHERE id=?";
        $data = $this->site->pdo->query($query, array($id), true);
        //If not exists in DB, or is old, get from API
        if(!isset($data['id']) || $data['lastUpdate'] < strtotime("-2 weeks")) {
            $this->getApi($id);
        } else {
            $this->data = $data;
            $this->site->debug->notice("Got Item($id) from DB");
        }
    }

    //Bad idea to use this if you don't want to be banned
    public function updateAll() {
        //Will update all that are outdated but in the DB.
        //Get timestamp that is considered old
        $cTime = strtotime("-2 weeks");
        $query = "SELECT id FROM ". $this->site->pdo->TP ."itemCache WHERE lastUpdate < ?";
        $data = $this->site->pdo->query($query, array($cTime));
        foreach($data as $value) {
            $this->getApi($value['id']);
            set_time_limit(10);
        }
        //return print_r($data, true);
    }
    
    protected function deleteItem($id) {
        return $this->site->pdo->query("DELETE from ". $this->site->pdo->TP ."itemCache WHERE id=?", array($id));
    }
    
    protected function saveItem() {
        $this->deleteItem($this->data['id']);
        $this->site->pdo->insert("itemCache", $this->data);
    }
    
    public function get($id) {
        $this->getDB($id);
        //$this->getApi($id);
        $this->saveIcon();
        return $this->data;
    }
    
    public function getArray() {
        if($this->data['id'] == "") {
            return false;
        } else {
            return $this->data;
        }
    }
    
    public function getFriendly($id) {
        $this->get($id);
        if($this->data['id'] == "") {
            //Data doesn't exist on server or DB, don't continue
            $this->site->debug->error("Item($id) does not exist.");
            /** @noinspection PhpInconsistentReturnPointsInspection */
            return;
        }
        $selects = array(
            'i.id',
            'i.description',
            'i.name',
            'i.icon',
            'i.stackable',
            'i.itemBind',
            'i.buyPrice',
            'ic.className as className',
            'isc.subClassName as subClassName',
            'isc.subClassFullName as subClassFullName',
            'i.containerSlots',
            'i.inventoryType',
            'i.equippable',
            'i.itemLevel',
            'i.maxCount',
            'i.maxDurability',
            'i.minFactionId',
            'i.minReputation',
            'iq.name as qualityName',
            'iq.color as qualityColor',
            'i.sellPrice',
            'i.requiredSkill',
            'i.requiredLevel',
            'i.requiredSkillRank',
            'i.baseArmor',
            'i.hasSockets',
            'i.isAuctionable',
            'i.armor',
            'i.displayInfoId',
            'i.nameDescription',
            'i.nameDescriptionColor',
            'i.upgradable'
        );
        $select = implode(",", $selects);
        $query = "SELECT $select FROM ".$this->site->pdo->TP."itemCache as i, ".$this->site->pdo->TP."itemClass as ic, ".$this->site->pdo->TP."itemSubClass as isc, ".$this->site->pdo->TP."itemQuality as iq "
                . "WHERE i.itemClass = ic.itemClass AND (i.itemSubClass = isc.itemSubClass AND i.itemClass = isc.itemClass) AND i.quality = iq.id AND i.id = ?";
        $data = $this->site->pdo->query($query, array($id), true);
        return $data;
    }
    
    protected function downloadIcon()
    {
        if($this->data['icon'] != "") {
            $url = "http://us.media.blizzard.com/wow/icons/56/{$this->data['icon']}.jpg";
            $this->curl->get($url);
            return $this->curl->response;
        }
        return false;
    }
    
    protected function saveIcon() {
        $fName = $this->site->baseDir . "wowIcons".DS."{$this->data['icon']}.jpg";
        if(!file_exists($fName)){
            $handle = fopen($fName, "w");
            fwrite($handle, $this->downloadIcon());
            fclose($handle);
        }
    }
    
    public function iconUrl() {
        $this->saveIcon();
        return $this->site->url . "/wowIcons/{$this->data['icon']}.jpg";
    }
}
