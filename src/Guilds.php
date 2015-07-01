<?php
namespace jlawrence\wowApi;

class Guilds {
    private $site;
    private $curl;
    
    public function __construct(FactoryLw $site) {
        $this->curl = $site->curl;
        $this->site = $site;
    }
    
    public function getApi($guildName) {
        $url = Core::$API_HOST . "guild/". Core::$SERVER ."/". rawurlencode($guildName);
        $url = Core::urlAddKey($url);
        //echo $url;
        $this->curl->get($url);
        //echo "<pre>". print_r($this->curl, true) . "</pre>";
        $json = $this->curl->response;
        $jArray = json_decode($json, true);
        if(isset($jArray['status']) && $jArray['status'] == 'nok') {
            //Status error, update current item to reflect, and return
            //$this->_empty();
            $this->site->debug->notice("wowGuild($guildName) does not exist");
            return null;
        }
        $id = strtolower(Core::$REGION .'-'. $jArray['realm'] .'-'. str_replace(' ', '_', $jArray['name']));
        $data = array(
            'guildId' => strtolower(Core::$REGION .'-'. $jArray['realm'] .'-'. str_replace(' ', '_', $jArray['name'])),
            'realmId' => strtolower(Core::$REGION .'-'. $jArray['realm']),
            'name' => $jArray['name'],
            'realm' => $jArray['realm'],
            'battlegroup' => $jArray['battlegroup'],
            'level' => $jArray['level'],
            'side' => $jArray['side'],
            'achievementPoints' => $jArray['achievementPoints'],
            'lastUpdate' => time(),
            'fullJSON' => $json
        );
        $this->saveGuild($data);
        $this->site->debug->notice("Updated wowGuild($id) from API");
        return $data;
    }
    
    public function fullUpdate($guildName) {
        $char = new Characters($this->site);
        $members = $this->getMembers($guildName);
        foreach($members as $member) {
            $char->getCharacter($member['name']);
        }
    }
    
    public function getDB($guildName) {
        $id = strtolower(Core::$REGION .'-'. Core::$SERVER .'-'. str_replace(' ', '_', $guildName));
        //echo $id; exit;
        $query = "SELECT * FROM ". $this->site->pdo->TP ."wowGuilds where guildId = ?";
        $data = $this->site->pdo->query($query, array($id), true);
        
        if(!isset($data['guildId']) || $data['lastUpdate'] < strtotime("-1 day")) {
            $ret = $this->getApi($guildName);
        } else {
            $this->site->debug->notice("Got wowGuild($id) from DB");
            $ret = $data;
        }
        return $ret;
    }
    
    public function get($guildName) {
        return $this->getDB($guildName);
    }
    
    public function deleteGuild($id){
        return $this->site->pdo->query("DELETE from ". $this->site->pdo->TP ."wowGuilds WHERE guildId = ?", array($id));
    }
    
    protected function saveGuild($data) {
        $query = "INSERT INTO ". $this->site->pdo->TP ."wowGuilds (guildId, realmId, name, realm, battlegroup, level, side, achievementPoints, lastUpdate, fullJSON)".
                " VALUES (:guildId, :realmId, :name, :realm, :battlegroup, :level, :side, :achievementPoints, :lastUpdate, :fullJSON) ".
                " ON DUPLICATE KEY UPDATE ".
                "battlegroup = :battlegroup, level = :level, side = :side, achievementPoints = :achievementPoints, fullJSON = :fullJSON, lastUpdate = :lastUpdate";
        $this->site->pdo->query($query, $data);
        //echo "<pre>$query \n". print_r($data, true). "</pre>";
        //$this->deleteGuild($data['guildId']);
        //$this->site->pdo->insert("wowGuilds", $data);
    }
    
    public function getGuildIcon ($guildName, $showLevel = true, $width=215) {
        $id = Core::getID($guildName);
        $fName = $this->site->baseDir . "imgCache/{$id}_{$width}_". intval($showLevel) .".png";
        if (file_exists($fName) AND (filemtime($fName)+86400) > time()) {
            return "imgCache/{$id}_{$width}_". intval($showLevel) .".png";
        }
        $data = $this->get($guildName);
        if($data == null) {
            return false;
        }
        $guildData = json_decode($data['fullJSON'], true);
        return $this->createIcon($guildData, $showLevel, $width);
        
    }
    
    public function getMembersDB ($guildName) {
        $id = strtolower(Core::$REGION .'-'. Core::$SERVER .'-'. str_replace(' ', '_', $guildName));
        $query = "SELECT * FROM ". $this->site->pdo->TP ."wowCharacters WHERE guildId = ?";
        $data = $this->site->pdo->query($query, array($id));
        return $data;
    }
    
    public function getMembers($guildName) {
        $id = strtolower(Core::$REGION .'-'. Core::$SERVER .'-'. str_replace(' ', '_', $guildName));
        $url = Core::$API_HOST . "guild/". Core::$SERVER ."/". rawurlencode($guildName) ."?fields=members";
        $query = "SELECT guildId, lastUpdate FROM ". $this->site->pdo->TP ."wowGuilds WHERE guildId = ?";
        $data = $this->site->pdo->query($query, array($id), true);
        echo "<pre>". print_r($data, true) . "</pre>";
        if(!isset($data['guildId'])) {
            $guildData = $this->getApi($guildName);
        }
        if(isset($data['lastUpdate']) && ($data['lastUpdate'] > strtotime("-1 day"))) {
            return $this->getMembersDB($guildName);
        } elseif (!isset($guildData['guildId']) && !isset($data['guildId'])) {
            return null;
        }
        $permGuildId = $id;
/*        echo "<pre>". strtotime("-1 day") ."\n". print_r($data, true). "</pre>";*/
        //get the current members
        $cArray = $this->getMembersDB($guildName);
        $cIds = array();
        if(isset($cArray[0])) {
            foreach ($cArray as $char) {
                $cIds[] = $char['id'];
            }
        }
        $url = Core::urlAddKey($url);
        $this->curl->get($url);
        $json = $this->curl->response;
        $guildData = json_decode($json, true);
        $gIds = array();
        $gArray = null;
        //echo "<pre>". print_r($guildData, true) ."</pre>";
        if(isset($guildData['members'])) {
            foreach($guildData['members'] as $toon) {
                $realmId = strtolower(Core::$REGION .'-'. $toon['character']['realm']);
                $toonId = strtolower($realmId .'-'. str_replace(' ', '_', $toon['character']['name']));
                $guildId = strtolower(Core::$REGION .'-'. $toon['character']['guildRealm'] .'-'. str_replace(' ', '_', $toon['character']['guild']));
                $gIds[] = $toonId;
                $gArray[] = array(
                    'id' => $toonId,
                    'name' => $toon['character']['name'],
                    'realmId' => $realmId,
                    'realm' => $toon['character']['realm'],
                    'battlegroup' => $guildData['battlegroup'],
                    'class' => $toon['character']['class'],
                    'race' => $toon['character']['race'],
                    'gender' => $toon['character']['gender'],
                    'level' => $toon['character']['level'],
                    'achievementPoints' => $toon['character']['achievementPoints'],
                    'thumbnail' => $toon['character']['thumbnail'],
                    'specName' => (isset($toon['character']['spec']['name']) ? $toon['character']['spec']['name'] : ''),
                    'specRole' => (isset($toon['character']['spec']['role']) ? $toon['character']['spec']['role'] : ''),
                    'specIcon' => (isset($toon['character']['spec']['icon']) ? $toon['character']['spec']['icon'] : ''),
                    'guildId' => $guildId,
                    'guildName' => $toon['character']['guild'],
                    'guildRank' => $toon['rank']
                    
                );
            }
            $diff = array_diff($cIds, $gIds);
            if(count($diff) > 0) {
                $query = "UPDATE ". $this->site->pdo->TP ."wowCharacters SET guildId = '', guildName = '', guildRank = 0, lastUpdate =0 WHERE id = ?";
                $this->site->pdo->query($query, $diff, false, true);
            }
            
            if(!is_null($gArray)) {
                $query = "INSERT INTO ". $this->site->pdo->TP ."wowCharacters ". 
                        "(id, name, realmId, realm, battlegroup, class, race, gender, level, achievementPoints, thumbnail, specName, specRole, specIcon, guildId, guildName, guildRank) VALUES". 
                        "(:id, :name, :realmId, :realm, :battlegroup, :class, :race, :gender, :level, :achievementPoints, :thumbnail, :specName, :specRole, :specIcon, :guildId, :guildName, :guildRank)".
                        " ON DUPLICATE KEY UPDATE ".
                        "class = :class, race = :race, gender = :gender, level = :level, achievementPoints = :achievementPoints, thumbnail = :thumbnail, specName = :specName, specRole = :specRole, specIcon = :specIcon, guildId = :guildId, guildName = :guildName, guildRank = :guildRank";
                $this->site->pdo->query($query, $gArray, false, true);
            }
            $query = "UPDATE ". $this->site->pdo->TP ."wowGuilds SET lastUpdate = ? WHERE guildId = ?";
            $pAr = array(time(), $permGuildId);
/*            echo "<pre>". print_r($pAr, true) ."</pre>";*/
            $this->site->pdo->query($query, $pAr);
            //echo "<pre>$query \n". print_r($pAr, true). "</pre>";
            return $gArray;
        }
        return false;
    }
    
    public function createIcon($guildData, $showLevel = true, $width=215) {
        //$id = strtolower(Core::$REGION .'-'. $guildData['realm'] .'-'. str_replace(' ', '_', $guildData['name']));
        $id = Core::getID($guildData['name'], $guildData['realm']);
        $fName = $this->site->baseDir . "imgCache/{$id}_{$width}_". intval($showLevel) .".png";
        /*
        if(file_exists($fName)) {
            return "imgCache/{$id}_{$width}.png";
        }
         */
        
        //Start copy-code

   		$imgfile = $fName;
   		#print $imgfile;
   		if (file_exists($imgfile) AND (filemtime($imgfile)+86400) > time()) {
   			/*
            $finalimg = imagecreatefrompng($imgfile);
			imagesavealpha($finalimg,true);
			imagealphablending($finalimg, true);
             */
            return "imgCache/{$id}_{$width}_". intval($showLevel) .".png";
            
   		} else {
	   		if ($width > 1 AND $width < 215){
				$height = ($width/215)*230;
				$finalimg = imagecreatetruecolor($width, $height);
				$trans_colour = imagecolorallocatealpha($finalimg, 0, 0, 0, 127);
				imagefill($finalimg, 0, 0, $trans_colour);
				imagesavealpha($finalimg,true);
				imagealphablending($finalimg, true);
	   		}
			
	   		if ($guildData['side'] == 0){
	   			$ring = 'alliance';
	   		} else {
	   			$ring = 'horde';
	   		}
	   		
			$imgOut = imagecreatetruecolor(215, 230);
			
			$emblemURL = $this->site->baseDir."wowIcons/guildImg/emblems/emblem_".sprintf("%02s",$guildData['emblem']['icon']).".png";
			$borderURL = $this->site->baseDir."wowIcons/guildImg/borders/border_".sprintf("%02s",$guildData['emblem']['border']).".png";
			$ringURL = $this->site->baseDir."wowIcons/guildImg/static/ring-".$ring.".png";
			$shadowURL = $this->site->baseDir."wowIcons/guildImg/static/shadow_00.png";
			$bgURL = $this->site->baseDir."wowIcons/guildImg/static/bg_00.png";
			$overlayURL = $this->site->baseDir."wowIcons/guildImg/static/overlay_00.png";
			$hooksURL = $this->site->baseDir."wowIcons/guildImg/static/hooks.png";
			$levelURL = $this->site->baseDir."wowIcons/guildImg/static/";
			
			imagesavealpha($imgOut,true);
			imagealphablending($imgOut, true);
			$trans_colour = imagecolorallocatealpha($imgOut, 0, 0, 0, 127);
			imagefill($imgOut, 0, 0, $trans_colour);
			
			$ring = imagecreatefrompng($ringURL);
			$ring_size = getimagesize($ringURL);
			
			$emblem = imagecreatefrompng($emblemURL);
			$emblem_size = getimagesize($emblemURL);
			imagelayereffect($emblem, IMG_EFFECT_OVERLAY);
			$emblemcolor = preg_replace('/^ff/i','',$guildData['emblem']['iconColor']);
			$color_r = hexdec(substr($emblemcolor,0,2));
			$color_g = hexdec(substr($emblemcolor,2,2));
			$color_b = hexdec(substr($emblemcolor,4,2));
			imagefilledrectangle($emblem,0,0,$emblem_size[0],$emblem_size[1],imagecolorallocatealpha($emblem, $color_r, $color_g, $color_b,0));
			
			
			$border = imagecreatefrompng($borderURL);
			$border_size = getimagesize($borderURL);
			imagelayereffect($border, IMG_EFFECT_OVERLAY);
			$bordercolor = preg_replace('/^ff/i','',$guildData['emblem']['borderColor']);
            //print_r($bordercolor);
			$color_r = hexdec(substr($bordercolor,0,2));
			$color_g = hexdec(substr($bordercolor,2,2));
			$color_b = hexdec(substr($bordercolor,4,2));
			imagefilledrectangle($border,0,0,$border_size[0]+100,$border_size[1]+100,imagecolorallocatealpha($border, $color_r, $color_g, $color_b,0));
			
			$shadow = imagecreatefrompng($shadowURL);
			
			$bg = imagecreatefrompng($bgURL);
			$bg_size = getimagesize($bgURL);
			imagelayereffect($bg, IMG_EFFECT_OVERLAY);
			$bgcolor = preg_replace('/^ff/i','',$guildData['emblem']['backgroundColor']);
			$color_r = hexdec(substr($bgcolor,0,2));
			$color_g = hexdec(substr($bgcolor,2,2));
			$color_b = hexdec(substr($bgcolor,4,2));
			imagefilledrectangle($bg,0,0,$bg_size[0]+100,$bg_size[0]+100,imagecolorallocatealpha($bg, $color_r, $color_g, $color_b,0));
			
			
			$overlay = imagecreatefrompng($overlayURL);
			$hooks = imagecreatefrompng($hooksURL);
			
			$x = 20;
			$y = 23;
			
			//if (!$this->emblemHideRing){
				imagecopy($imgOut,$ring,0,0,0,0, $ring_size[0],$ring_size[1]);
			//}
			$size = getimagesize($shadowURL);
			imagecopy($imgOut,$shadow,$x,$y,0,0, $size[0],$size[1]);
			imagecopy($imgOut,$bg,$x,$y,0,0, $bg_size[0],$bg_size[1]);
			imagecopy($imgOut,$emblem,$x+17,$y+30,0,0, $emblem_size[0],$emblem_size[1]);
			imagecopy($imgOut,$border,$x+13,$y+15,0,0, $border_size[0],$border_size[1]);
			$size = getimagesize($overlayURL);
			imagecopy($imgOut,$overlay,$x,$y+2,0,0, $size[0],$size[1]);
			$size = getimagesize($hooksURL);
			imagecopy($imgOut,$hooks,$x-2,$y,0,0, $size[0],$size[1]);
			
			if ($showLevel){
				$level = $guildData['level'];
				if ($level < 10){
					$levelIMG = imagecreatefrompng($levelURL.$level.".png");
                    //echo $levelURL.$level.".png";
				} else {
                    //$levelURL.$level.".png";
					$digit[1] = substr($level,0,1);
					$digit[2] = substr($level,1,1);
					$digit1 = imagecreatefrompng($levelURL.$digit[1].".png");
					$digit2 = imagecreatefrompng($levelURL.$digit[2].".png");
					$digitwidth = imagesx($digit1);
					$digitheight = imagesy($digit1);
					$levelIMG = imagecreatetruecolor($digitwidth*2,$digitheight);
					$trans_colour = imagecolorallocatealpha($levelIMG, 0, 0, 0, 127);
					imagefill($levelIMG, 0, 0, $trans_colour);
					imagesavealpha($levelIMG,true);
					imagealphablending($levelIMG, true);
					// Last image added first because of the shadow need to be behind first digit
					imagecopy($levelIMG,$digit2,$digitwidth-12,0,0,0, $digitwidth, $digitheight);
					imagecopy($levelIMG,$digit1,12,0,0,0, $digitwidth, $digitheight);
				}
				$size[0] = imagesx($levelIMG);
				$size[1] = imagesy($levelIMG);
				$levelemblem = imagecreatefrompng($ringURL);
				imagesavealpha($levelemblem,true);
				imagealphablending($levelemblem, true);
				imagecopy($levelemblem,$levelIMG,(215/2)-($size[0]/2),(215/2)-($size[1]/2),0,0,$size[0],$size[1]);
				imagecopyresampled($imgOut, $levelemblem, 143, 150,0,0, 215/3, 215/3, 215, 215);
			}
			
			if ($width > 1 AND $width < 215){
                imagecopyresampled($finalimg, $imgOut, 0, 0, 0, 0, $width, $height, 215, 230);
			} else {
				$finalimg = $imgOut;
			}
			imagepng($finalimg,$imgfile);
   		}
   		return "imgCache/{$id}_{$width}_". intval($showLevel) .".png";
    }
}
