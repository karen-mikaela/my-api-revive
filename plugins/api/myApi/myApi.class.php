<?php
require_once LIB_PATH . '/Extension/api/Api.php';
require_once "pluginsApiMyApiCampaignXmlRpcService.php";
require_once MAX_PATH . '/www/api/v2/common/XmlRpcUtils.php';

class Plugins_Api_MyApi_MyApi extends Plugins_Api{
    function getDispatchMap(){
        return array(
            // Campaign functions
            'ox.getCampaign' => array(
                'function'  => array($this, 'getFullCampaign'),
                'signature' => array(
                  array('array', 'string', 'int'),
                ),
                'docstring' => 'Get Full Campaign Information'
            ),
            'ox.getCampaignListByAdvertiserId' => array(
                'function'  => array($this, 'getCampaignListByAdvertiserIdPagination'),
                'signature' => array(
                    array('array', 'string', 'int','int','int'),
                    array('array', 'string', 'int','int'),
                    array('array', 'string', 'int')
                ),
                'docstring' => 'Get Campaign List By Advertiser Id with limit and offset'
            ),
        );

    }

    public function getFullCampaign($message){
        $service = new Plugins_Api_MyApi_CampaignXmlRpcService();
        return $service->getFullCampaign($message);
    }

    public function getCampaignListByAdvertiserIdPagination($message){

        $service = new Plugins_Api_MyApi_CampaignXmlRpcService();

        return $service->getCampaignListByAdvertiserIdPagination($message);
    }



}
