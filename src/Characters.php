<?php
namespace jlawrence\wowApi;

class Characters {
    private $site;
    private $curl;

    public function __construct(FactoryLw $site) {
        $this->site = $site;
        $this->curl = $site->curl;
    }

    protected function _getSelectedSpec ($talents) {
        $sReturn = array(
            'specName' => '',
            'specRole' => '',
            'specIcon' => ''
        );
        foreach($talents as $talent) {
            if(isset($talent['selected'])) {
                $sReturn['specName'] = $talent['spec']['name'];
                $sReturn['specRole'] = $talent['spec']['role'];
                $sReturn['specIcon'] = $talent['spec']['icon'];
            }
        }
        //echo "<pre>". print_r($sReturn, true) ."</pre>";
        return $sReturn;
    }

    public static function getImagesUrl($tag) {
        $reg = strtolower(Core::$REGION);
        $avatar = "http://{$reg}.battle.net/static-render/{$reg}/{$tag}";
        $base = str_replace("-avatar.jpg", '', $avatar);
        $aRet = array(
            'avatar' => $avatar,
            'inset' => "{$base}-inset.jpg",
            'card' => "{$base}-card.jpg",
            'profilemain' => "{$base}-profilemain.jpg"
        );
        return $aRet;
    }

    protected function _deleteCharacter($id) {
        $query = "DELETE FROM ". $this->site->pdo->TP ."wowCharacters where id = ?";
        $this->site->pdo->query($query, array($id));
    }

    protected function _saveCharacter($data) {
        $query = "INSERT INTO ". $this->site->pdo->TP ."wowCharacters ".
            "(id, name, realmId, realm, battlegroup, withTitle, titleId, titleFormat, class, race, gender, level, achievementPoints, thumbnail, specName, specRole, specIcon, guildId, guildName, averageItemLevel, averageItemLevelEquipped, totalHonorableKills, lastUpdate, fullJSON) VALUES ".
            "(:id, :name, :realmId, :realm, :battlegroup, :withTitle, :titleId, :titleFormat, :class, :race, :gender, :level, :achievementPoints, :thumbnail, :specName, :specRole, :specIcon, :guildId, :guildName, :averageItemLevel, :averageItemLevelEquipped, :totalHonorableKills, :lastUpdate, :fullJSON)".
            " ON DUPLICATE KEY UPDATE ".
            "withTitle = :withTitle, titleId = :titleId, titleFormat = :titleFormat, class = :class, race = :race, gender = :gender, level = :level, achievementPoints = :achievementPoints, thumbnail = :thumbnail, specName = :specName, specRole = :specRole, specIcon = :specIcon, guildId = :guildId, guildName = :guildName, averageItemLevel = :averageItemLevel, averageItemLevelEquipped = :averageItemLevelEquipped, totalHonorableKills = :totalHonorableKills, lastUpdate = :lastUpdate, fullJSON = :fullJSON";
        $this->site->pdo->query($query, $data);
    }


    public function getCharacterDB($name) {
        $id = Core::getID($name);
        $query = "SELECT * FROM ". $this->site->pdo->TP ."wowCharacters WHERE id = ?";
        $data = $this->site->pdo->query($query, array($id), true);
        return $data;
    }

    public function getCharacter($name) {
        $data = $this->getCharacterDB($name);
        $id = Core::getID($name);
        if((isset($data['lastUpdate']) && ($data['lastUpdate'] < strtotime("-4 hours")) || !isset($data['id']))) {
            $this->site->debug->notice("Getting wowCharacter($id) from API");
            /*$data = */$this->getCharacterAPI($name);
            return $this->getCharacterDB($name);
        } else {
            $this->site->debug->notice("Got wowCharacter($id) from DB");
            return $data;
        }
    }

    public function getCharacterList($whereArray = null, $orderByArray = null) {
        $whereA = null;
        $orderByA = null;
        $query = "SELECT * FROM ". $this->site->pdo->TP ."wowCharacters";
        if(!is_null($whereArray) && is_array($whereArray)) {
            foreach($whereArray as $name => $value){
                $whereA[] = "$name = :{$name}";
            }
            $query .= " WHERE ". implode($whereA, " AND ");
        }
        if(!is_null($orderByArray) && is_array($orderByArray)) {
            foreach($orderByArray as $name => $value) {
                $orderByA[] = "$name $value";
            }
            $query .= " ORDER BY ". implode($orderByA, ', ');
        }


        $retData = $this->site->pdo->query($query, $whereArray);
        return $retData;
    }

    public function guessAlts($name) {
        $id = Core::getID($name);
        $query = "SELECT DISTINCT wa.* "
            . "FROM "
            . "LW_wowCharacters as wc "
            . "LEFT JOIN LW_wowCharacters AS wa "
            . "ON wc.achievementPoints = wa.achievementPoints "
            . "WHERE wc.id=? "
            . "ORDER BY wa.averageItemLevel DESC";
        $toons = $this->site->pdo->query($query, array($id));
        $main = array_shift($toons);
        $alts = $toons;
        $retData = array('gmain' => $main, 'galts' => $alts);
        return $retData;
    }

    public function getCharacterAPI($name) {
        $id = Core::getID($name);
        $url = Core::$API_HOST . "character/". Core::$SERVER ."/". rawurlencode($name) ."?fields=items,appearance,guild,reputation,stats,titles,talents";
        $url = Core::urlAddKey($url);
        $this->curl->get($url);
        $json = $this->curl->response;
        $cData = json_decode($json, true);
        if((isset($cData['status']) && $cData['status'] == 'nok') || !isset($cData['name'])) {
            //Status error, update current item to reflect, and return
            //$this->_empty();
            $this->site->debug->notice("wowCharacter ($id) does not exist");
            //In case the character moved servers, etc, delete character on realm
            $this->_deleteCharacter($id);
            return null;
        }
        if(!isset($cData['guild'])) {
            $guildInfo = array(
                'guildId' => '',
                'guildName' => ''
            );
        } else {
            $guildInfo = array();
            $guildInfo['guildId'] = Core::getID($cData['guild']['name'], $cData['guild']['realm']);
            $guildInfo['guildName'] = $cData['guild']['name'];
        }
        $title = $this->getTitle($cData);
        //print_r($title);
        $specInfo = $this->_getSelectedSpec($cData['talents']);
        $toSave = array(
            'id' => $id,
            'name' => $cData['name'],
            'realmId' => Core::getRealmId($cData['realm']),
            'realm' => $cData['realm'],
            'battlegroup' => $cData['battlegroup'],
            'withTitle' => sprintf($title['name'], $cData['name']),
            'titleId' => $title['id'],
            'titleFormat' => $title['name'],
            'class' => $cData['class'],
            'race' => $cData['race'],
            'gender' => $cData['gender'],
            'level' => $cData['level'],
            'achievementPoints' => $cData['achievementPoints'],
            'thumbnail' => $cData['thumbnail'],
            'specName' => $specInfo['specName'],
            'specRole' => $specInfo['specRole'],
            'specIcon' => $specInfo['specIcon'],
            'guildId' => $guildInfo['guildId'],
            'guildName' => $guildInfo['guildName'],
            'averageItemLevel' => $cData['items']['averageItemLevel'],
            'averageItemLevelEquipped' => $cData['items']['averageItemLevelEquipped'],
            'totalHonorableKills' => $cData['totalHonorableKills'],
            'lastUpdate' => time(),
            'fullJSON' => $json
        );
        $this->_saveCharacter($toSave);
        return $toSave;
    }

    public static function replaceIds($data) {
        $data['race'] = Races::getById($data['race']);
        $data['class'] = Classes::getById($data['class']);
        $data['images'] = self::getImagesUrl($data['thumbnail']);

        return $data;
    }

    public function getTitle($jsonArray) {
        foreach($jsonArray['titles'] as $cTitle) {
            if(isset($cTitle['selected'])) {
                return array('name' => $cTitle['name'], 'id' => $cTitle['id']);
            }
        }
        return array('name' => '%s', 'id' => 0);
    }
}
