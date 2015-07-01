<?php
/**
 * Created by: Jon Lawrence on 2015-06-30 1:37 PM
 */

require_once "vendor/autoload.php";
$wowApi = new jlawrence\wowApi\Core("cnf/site.ini");
$wowApi->site->debug->setDebug(true);

$gName = 'Retaliation';
$mem = $wowApi->wowCharacters->getCharacter("Archnemisis");
$mem = jlawrence\wowApi\Characters::replaceIds($mem);

echo "<pre>". print_r($mem, true) . "</pre>";

$nothing->here();