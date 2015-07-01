<?php
namespace jlawrence\wowApi;

class Auctions {
    
    private $curl;
    private $site;
    public $auctionJSON;
    
    public function __construct(FactoryLw $site) {
        $this->curl = $site->curl;
        $this->site = $site;
    }
    
    public function getUrl() {
        $this->curl->get(Core::urlAddKey(Core::$API_HOST ."auction/data/". Core::$SERVER));
        
        $this->auctionJSON = json_decode($this->curl->response, true);
        return $this->auctionJSON;
    }
    
    public function __toString() {
        return print_r($this->auctionJSON, true);
    }
    
    public function downloadData() {
        $this->getUrl();
        $url = $this->auctionJSON['files'][0]['url'];
        set_time_limit(120);
        ini_set('memory_limit', '-1');
        $this->curl->get($url);
        $json = $this->curl->response;
        $aArray = json_decode($json, true);
        $query = "DELETE FROM ". $this->site->pdo->TP ."wowAuctions WHERE ownerRealm like ?";
        //$this->site->pdo->query($query, array($aArray['realm']['slug']));
        $this->site->pdo->query($query, array($GLOBALS['WOW_SERVER']));
        
        $i=0;
        $itemList = array();
        //Go Through Alliance
        foreach($aArray['auctions']['auctions'] as $item) {
            $itemList[$i] = $item;
            $itemList[$i]['player'] = $itemList[$i]['owner'];
            $itemList[$i]['owner'] = strtolower($GLOBALS['WOW_REGION'] . "-". $itemList[$i]['ownerRealm'] ."-". $itemList[$i]['owner']);
            $itemList[$i]['petSpeciesId'] = (isset($itemList[$i]['petSpeciesId'])) ? $itemList[$i]['petSpeciesId'] : '';
            $itemList[$i]['petBreedId'] = (isset($itemList[$i]['petBreedId'])) ? $itemList[$i]['petBreedId'] : '';
            $itemList[$i]['petLevel'] = (isset($itemList[$i]['petLevel'])) ? $itemList[$i]['petLevel'] : '';
            $itemList[$i]['petQualityId'] = (isset($itemList[$i]['petQualityId'])) ? $itemList[$i]['petQualityId'] : '';
            $i++;
        }
        /*
        foreach($aArray['horde']['auctions'] as $item) {
            $itemList[$i] = $item;
            $itemList[$i]['player'] = $itemList[$i]['owner'];
            $itemList[$i]['owner'] = strtolower($GLOBALS['WOW_REGION'] . "-". $itemList[$i]['ownerRealm'] ."-". $itemList[$i]['owner']);
            $itemList[$i]['petSpeciesId'] = (isset($itemList[$i]['petSpeciesId'])) ? $itemList[$i]['petSpeciesId'] : '';
            $itemList[$i]['petBreedId'] = (isset($itemList[$i]['petBreedId'])) ? $itemList[$i]['petBreedId'] : '';
            $itemList[$i]['petLevel'] = (isset($itemList[$i]['petLevel'])) ? $itemList[$i]['petLevel'] : '';
            $itemList[$i]['petQualityId'] = (isset($itemList[$i]['petQualityId'])) ? $itemList[$i]['petQualityId'] : '';
            $i++;
        }
        foreach($aArray['neutral']['auctions'] as $item) {
            $itemList[$i] = $item;
            $itemList[$i]['player'] = $itemList[$i]['owner'];
            $itemList[$i]['owner'] = strtolower($GLOBALS['WOW_REGION'] . "-". $itemList[$i]['ownerRealm'] ."-". $itemList[$i]['owner']);
            $itemList[$i]['petSpeciesId'] = (isset($itemList[$i]['petSpeciesId'])) ? $itemList[$i]['petSpeciesId'] : '';
            $itemList[$i]['petBreedId'] = (isset($itemList[$i]['petBreedId'])) ? $itemList[$i]['petBreedId'] : '';
            $itemList[$i]['petLevel'] = (isset($itemList[$i]['petLevel'])) ? $itemList[$i]['petLevel'] : '';
            $itemList[$i]['petQualityId'] = (isset($itemList[$i]['petQualityId'])) ? $itemList[$i]['petQualityId'] : '';
            $i++;
        }
        */
        $query = "INSERT INTO ". $this->site->pdo->TP ."wowAuctions (auc, item, owner, ownerRealm, bid, buyout, quantity, timeLeft, rand, seed, petSpeciesId, petBreedId, petLevel, petQualityId, player) VALUES ".
                        "(:auc, :item, :owner, :ownerRealm, :bid, :buyout, :quantity, :timeLeft, :rand, :seed, :petSpeciesId, :petBreedId, :petLevel, :petQualityId, :player)";
        $this->site->pdo->query($query, $itemList, false, true);
        /*
        $auc = fopen("db_auctions.json", "w");
        fwrite($auc, $this->curl->response);
        fclose($auc);
         */
    }
    
    public function getByName($name) {
        $name = '%'. $name .'%';
        $query = "SELECT * FROM ". $this->site->pdo->TP ."wowAuctions where owner like ?";
        $res = $this->site->pdo->query($query, array($name));
        return $res;
    }
    
    public function getNameExact($name) {
        $query = "SELECT * FROM ". $this->site->pdo->TP ."wowAuctions where owner = ?";
        $res = $this->site->pdo->query($query, array($name));
        return $res;
    }
}
