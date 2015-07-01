<?php
/**
 * Created by: Jon Lawrence on 2015-06-23 6:58 AM
 */

namespace jlawrence\wowApi;
use jlawrence\lw;

class FactoryLw extends lw\Factory
{
    /** @var \Curl\Curl  */
    public $curl;

    public function __construct($iniFile)
    {
        $this->loadBaseMods();
        $this->loadIni($iniFile);
        $this->pdo = new lw\Pdo($this, $this->configArray['Pdo']);
        $this->curl = new \Curl\Curl();
        //Set this option because WAMP sucks
        $this->curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        //Set this because all the returns are expecting JSON string, and Curl updated to do it automatically.
        $this->curl->setJsonDecoder(function ($response) {
                return $response;
            }
        );
        $this->url = $this->configArray['Site']['url'];
        $this->title = $this->configArray['Site']['title'];
        Core::setApiKey($this->configArray['Site']['APIKEY']);
    }
} 