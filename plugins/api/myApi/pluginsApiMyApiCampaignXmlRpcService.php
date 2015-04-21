<?php
require_once MAX_PATH . '/www/api/v2/common/XmlRpcUtils.php';
require_once MAX_PATH . '/www/api/v2/common/BaseCampaignService.php';

class Plugins_Api_MyApi_CampaignXmlRpcService extends BaseCampaignService
{
    function Plugins_Api_MyApi_CampaignXmlRpcService()
    {
      $this->BaseCampaignService();
      $this->_oCampaignServiceImpl = new Plugins_Api_MyApi_CampaignServiceImpl();
    }

    function getFullCampaign(&$oParams)
    {
        $oResponseWithError = null;
        if (!XmlRpcUtils::getScalarValues(
                array(&$sessionId, &$campaignId),
                array(true, true), $oParams, $oResponseWithError)) {
           return $oResponseWithError;
        }

        $oCampaign = null;
        if ($this->_oCampaignServiceImpl->getFullCampaign($sessionId,
                $campaignId, $oCampaign)) {

            return XmlRpcUtils::getEntityResponse($oCampaign);
        } else {
            return XmlRpcUtils::generateError($this->_oCampaignServiceImpl->getLastError());
        }
    }
    /**
     * The getCampaignListByAdvertiserIdPagination method returns a list of campaigns
     * for an advertiser with limit and offset, or returns an error message.
     *
     * @access public
     *
     * @param XML_RPC_Message &$oParams
     *
     * @return generated result (data or error)
     */
    function getCampaignListByAdvertiserIdPagination(&$oParams) {
        $oResponseWithError = null;
        if (!XmlRpcUtils::getScalarValues(
                array(&$sessionId, &$advertiserId, &$limit, &$offset),
                array(true, true, false, false), $oParams, $oResponseWithError)) {
           return $oResponseWithError;
        }

        $aCampaignList = null;
        if ($this->_oCampaignServiceImpl->getCampaignListByAdvertiserIdPagination($sessionId,
                                            $advertiserId, $limit, $offset, $aCampaignList)) {

            return XmlRpcUtils::getArrayOfEntityResponse($aCampaignList);
        } else {

            return XmlRpcUtils::generateError($this->_oCampaignServiceImpl->getLastError());
        }
    }
}
// Require the base class, CampaignServiceImpl.
require_once MAX_PATH . '/www/api/v2/xmlrpc/CampaignServiceImpl.php';
class Plugins_Api_MyApi_CampaignServiceImpl extends CampaignServiceImpl{
    function Plugins_Api_MyApi_CampaignServiceImpl(){
        $this->BaseServiceImpl();
        $this->_dllCampaign = new Plugins_Api_MyApi_OA_Dll_Campaign();
    }

    function getFullCampaign($sessionId,  $campaignId, &$oCampaign){
        if ($this->verifySession($sessionId)) {

            return $this->_validateResult(
                $this->_dllCampaign->getFullCampaign($campaignId, $oCampaign));
        } else {

            return false;
        }
    }
    /**
     * The getCampaignListByAdvertiserIdPagination method returns a list of campaigns for
     * a specified advertiser.
     *
     * @access public
     *
     * @param string $sessionId
     * @param integer $advertiserId
     * @param integer $limit
     * @param integer $offset
     * @param array &$aCampaignList  Array of OA_Dll_CampaignInfo classes
     *
     * @return boolean
     */
    function getCampaignListByAdvertiserIdPagination($sessionId, $advertiserId, $limit, $offset, &$aCampaignList){
        if ($this->verifySession($sessionId)) {

            return $this->_validateResult(
                $this->_dllCampaign->getCampaignListByAdvertiserIdPagination($advertiserId, $limit, $offset,
                                                    $aCampaignList));
        } else {

            return false;
        }
    }
}

// Require the Campaign Dll class.
require_once MAX_PATH . '/lib/OA/Dll/Campaign.php';
require_once MAX_PATH . '/lib/OA/Dll/CampaignInfo.php';
class Plugins_Api_MyApi_OA_Dll_Campaign extends OA_Dll_Campaign{
    function getFullCampaign($campaignId, &$oCampaign){
        if ($this->checkIdExistence('campaigns', $campaignId)) {
            if (!$this->checkPermissions(null, 'campaigns', $campaignId)) {
                return false;
            }
            $doCampaign = OA_Dal::factoryDO('campaigns');
            $doCampaign->get($campaignId);
            $campaignData = $doCampaign->toArray();

            $oCampaign = new OA_Dll_CampaignInfo();

            $this->_setCampaignDataFromArrayFull($oCampaign, $campaignData);
            return true;

        } else {

            $this->raiseError('Unknown campaignId Error');
            return false;
        }
    }
    function _setCampaignDataFromArrayFull(&$oCampaign, $campaignData){
        $campaignData['campaignId']         = $campaignData['campaignid'];
        $campaignData['campaignName']       = $campaignData['campaignname'];
        $campaignData['advertiserId']       = $campaignData['clientid'];
        $campaignData['startDate']          = $campaignData['activate_time'];
        $campaignData['endDate']            = $campaignData['expire_time'];
        $campaignData['impressions']        = $campaignData['views'];
        $campaignData['targetImpressions']  = $campaignData['target_impression'];
        $campaignData['targetClicks']       = $campaignData['target_click'];
        $campaignData['targetConversions']  = $campaignData['target_conversion'];
        $campaignData['capping']            = $campaignData['capping'];
        $campaignData['sessionCapping']     = $campaignData['session_capping'];
        $campaignData['block']              = $campaignData['block'];
        $campaignData['viewWindow']         = $campaignData['viewwindow'];
        $campaignData['clickWindow']        = $campaignData['clickwindow'];

        // Don't send revenue & revenueType if the are null values in DB
        if (!is_numeric($campaignData['revenue'])) {
            $campaignData['revenue']  = 0;
            $campaignData['revenueType']  = $campaignData['revenue_type'];
        } else {
            $campaignData['revenueType']  = $campaignData['revenue_type'];
        }

        $oCampaign->readDataFromArray($campaignData);

        // Convert UTC timestamps to dates
        if (!empty($oCampaign->startDate)) {
            $oTz = $oCampaign->startDate->tz;
            $oCampaign->startDate->setTZByID('UTC');
            $oCampaign->startDate->convertTZ($oTz);
        }
        if (!empty($oCampaign->endDate)) {
            $oTz = $oCampaign->endDate->tz;
            $oCampaign->endDate->setTZByID('UTC');
            $oCampaign->endDate->convertTZ($oTz);
        }

        return  true;
    }

    /**
     * This method returns a list of campaigns for a specified advertiser.
     *
     * @access public
     *
     * @param int $advertiserId
     * @param int $limit
     * @param int $offset
     * @param array &$aCampaignList
     *
     * @return boolean
     */
    function getCampaignListByAdvertiserIdPagination($advertiserId, $limit, $offset, &$aCampaignList)
    {
        $aCampaignList = array();

        if (!$this->checkIdExistence('clients', $advertiserId)) {
                return false;
        }

        if (!$this->checkPermissions(null, 'clients', $advertiserId, null, $operationAccessType = OA_Permission::OPERATION_VIEW)) {
            return false;
        }

        $doCampaign = OA_Dal::factoryDO('campaigns');
        $doCampaign->clientid = $advertiserId;
         if (isset($limit) && is_numeric($limit)  && is_numeric($offset)) {
            $doCampaign->limit($offset,$limit);
        } elseif (!empty($limit)) {
            $doCampaign->limit(0,$limit);
        }
        $doCampaign->find();

        while ($doCampaign->fetch()) {
            $campaignData = $doCampaign->toArray();

            $oCampaign = new OA_Dll_CampaignInfo();
            $this->_setCampaignDataFromArray($oCampaign, $campaignData);

            $aCampaignList[] = $oCampaign;
        }
        return true;
    }
}
