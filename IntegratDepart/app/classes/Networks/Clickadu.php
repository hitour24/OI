<?php
namespace App\Classes\Network;;

use App\Classes\Network\Entity\Balance;
use App\Classes\Network\Entity\CampaignNetwork;
use App\Classes\Network\Entity\DataSetPlacement;
use App\Classes\Network\Entity\StatusCampaign;
use App\Helpers\Logger;
use App\Models\Minbid;

class Clickadu extends Network{
    /**
     * Clickadu constructor.
     */
    public function __construct(UserNetwork $loginData){
        $this->baseUrl = API_BASE_URL_CLICKADU;
        $this->apiKey = API_KEY_CLICKADU;
        parent::__construct($loginData);
    }

    protected function auth(){
        $externalUrl = 'v1.0/login_check';
        $dataLogin = [
            "gRecaptchaResponse" => null,
            "password" =>  $this->loginData->password,
            "type" => "ROLE_ADVERTISER",
            "username" => $this->loginData->login,
        ];
        $curl = new \Curl\Curl();
        $curl->post($this->clientData->baseUrl.$externalUrl,$dataLogin);
        $responseToken = $curl->response;
        $curl->close();
        $this->clientData->token = $responseToken ? $responseToken->result->accessToken ?: false : false;
    }

    public function getBalance():int{
        $externalUrl = 'api/v2/client/balance/';
        $curl = new \Curl\Curl();
        $curl->setHeader('Authorization', $this->clientData->apiKey);
        $curl->get($this->clientData->baseUrl.$externalUrl);
        $curl->close();
        $responseBalance = $curl->response;
        return $responseBalance ?
            isset($responseBalance->result) ?
                isset($responseBalance->result->advertiser) ?
                    $responseBalance->result->advertiser->balance? : false
                    :false
                :false
            : false;
    }

    public function getStatusCampaign(string $campaignId){
        $externalUrl = 'v1.0/api/client/campaigns/'.$campaignId.'/';
        $curl = new \Curl\Curl();
        $curl->setHeader('Authorization', $this->clientData->apiKey);
        $curl->get($this->clientData->baseUrl.$externalUrl);
        $curl->close();
        $responseStatus = $curl->response;
        return $responseStatus ?
                $responseStatus->status ? (new StatusCampaign($responseStatus->status))->validStatus : false
            : false;
    }

    public function resumeCampaign(string $campaignId):bool{
        $externalUrl = 'api/v2/campaign/'.$campaignId.'/change_status/start/';
        //Получаем статус кампании
        $oldStatusCampaign = $this->getStatusCampaign($campaignId);
        if ($oldStatusCampaign !== STATUS_CAMPAIGN_RESUME && $oldStatusCampaign !== STATUS_CAMPAIGN_REJECT) {
            $curl = new \Curl\Curl();
            $curl->setHeader('Authorization', $this->clientData->apiKey);
            $curl->post($this->clientData->baseUrl.$externalUrl);
            $curl->close();
            $responseResume = $curl->response;
            return $responseResume && isset($responseResume->result) && $responseResume->result === 'success';
        }
        return true;
    }

    public function stoppedCampaign(string $campaignId):bool{
        $externalUrl = 'api/v2/campaign/'.$campaignId.'/change_status/stop/';
        //Получаем статус кампании
        $oldStatusCampaign = $this->getStatusCampaign($campaignId);
        if ($oldStatusCampaign !== STATUS_CAMPAIGN_STOPPED && $oldStatusCampaign !== STATUS_CAMPAIGN_REJECT) {
            $curl = new \Curl\Curl();
            $curl->setHeader('Authorization', $this->clientData->apiKey);
            $curl->post($this->clientData->baseUrl.$externalUrl);
            $curl->close();
            $responseStopped = $curl->response;
            return $responseStopped && isset($responseStopped->result) && $responseStopped->result === 'success';
        }
        return true;
    }

    protected function setPlacementsData(DataSetPlacement $dataSetPlacement){
        $typePlacements = $dataSetPlacement->typePlacements;
        $placements = $dataSetPlacement->placements ? explode(',',$dataSetPlacement->placements) : [];
        $typeList = !$typePlacements ? 'targeted' : 'blocked';
        $externalUrl = 'v1.0/api/client/campaigns/'.$dataSetPlacement->campaignId.'/'.$typeList.'/zone/';
        $curl = new \Curl\Curl();
        $curl->setHeader('Content-Type','application/json');
        $curl->setHeader('Authorization', $this->clientData->apiKey);
        $curl->put($this->clientData->baseUrl.$externalUrl,$placements);
        $curl->close();
        $responseSetPlacements = $curl->response;
        return isset($responseSetPlacements->id);
    }


    protected function getFullDataCampaign(string $campaignId):\stdClass{
        $externalUrl = 'v1.0/client/campaigns/'.$campaignId.'/';
        $curl = new \Curl\Curl();
        $curl->setHeader('Authorization', 'Bearer '.$this->clientData->token);
        $curl->get($this->clientData->baseUrl.$externalUrl);
        $curl->close();
        return $curl->response->result;
    }


    public function addCampaign(CampaignNetwork $dataCampaignNetwork){
        $bundleId = $dataCampaignNetwork->bundleId;
        $sourceId = $dataCampaignNetwork->sourceId;
        $stage = $dataCampaignNetwork->stage;
        $bid = $dataCampaignNetwork->bid;
        $countryCode = $dataCampaignNetwork->countryCode;
        $typePlacements = $dataCampaignNetwork->typePlacements;
        $placements = $dataCampaignNetwork->placements ? explode(',',$dataCampaignNetwork->placements) : [];

        $fullDataCampaign = $this->getFullDataCampaign($dataCampaignNetwork->templateId);
        $targetUrl = $dataCampaignNetwork->generateTargetUrl($fullDataCampaign->targetUrl,100,true);
        if (!$targetUrl) return false;

        $newCampName = $dataCampaignNetwork->name;

        $dataFromCreateCampaign = [
            "name"          => $newCampName,
            "budgetLimit"   => 0,
            "rates"         => [
                [
                    "amount"     => ceil($dataCampaignNetwork->bid * 100) / 100,
                    "countries"  => [mb_strtolower($countryCode)]
                ]
            ],
            "freqCapType"       => 'user',
            "targetUrl"         => $targetUrl,
            "frequency"         => $fullDataCampaign->frequency,
            "capping"           => $fullDataCampaign->capping,
            "impFrequency"      => $fullDataCampaign->impFrequency,
            "impCapping"        => $fullDataCampaign->impCapping,
            "rateModel"         => $fullDataCampaign->rateModel,
            "direction"         => $fullDataCampaign->direction,
            "status"            => 2,
            "evenlyLimitsUsage" => $fullDataCampaign->evenlyLimitsUsage,
            "trafficQuality"    => $fullDataCampaign->trafficQuality,
            "autoLinkNewZones"  => $fullDataCampaign->autoLinkNewZones,
            "isAdblockBuy"      => $fullDataCampaign->isAdblockBuy,
            "isDSP"             => $fullDataCampaign->isDSP,
            "trafficBoost"      => $fullDataCampaign->trafficBoost,
            "startedAt"         => $fullDataCampaign->startedAt,
            "feed"              => $fullDataCampaign->feed,
            "trafficVertical"   => $fullDataCampaign->trafficVertical,
            "targeting"         => [
                        "timeTable"     => $fullDataCampaign->targeting->timeTable,
                        "os"            => $fullDataCampaign->targeting->os,
                        "osType"        => $fullDataCampaign->targeting->osType,
                        "osVersion"     => isset($fullDataCampaign->targeting->osVersion) ? : ["list" => [], "isExcluded" => false],
                        "device"        => isset($fullDataCampaign->targeting->device) ? : ["list" => [], "isExcluded" => false],
                        "deviceType"    => isset($fullDataCampaign->targeting->deviceType) ? : ["list" => [], "isExcluded" => false],
                            "country"   => [
                                "list"  => [[mb_strtolower($countryCode)
//                                    "id"    => mb_strtolower($countryCode),
//                                    "code"  => strtoupper($countryCode),
                                ]],
                            "isExcluded"    => false
                            ],
                        "connection"    => $fullDataCampaign->targeting->connection,
                        "mobileIsp"     => isset($fullDataCampaign->targeting->mobileIsp) ? : ["list" => [], "isExcluded" => false],
                        "proxy"         => $fullDataCampaign->targeting->proxy,
                        "zone"          => [
                            "list"          =>  $placements,
                            "isExcluded"    =>  $typePlacements
                        ],
                        "browser"       =>  $fullDataCampaign->targeting->browser,
                        "language"      =>  isset($fullDataCampaign->targeting->language) ? : ["list" => [], "isExcluded" => false],
                        "vertical"      =>  isset($fullDataCampaign->targeting->vertical) ? : ["list" => [], "isExcluded" => false],
                        "campaignTrafficVertical"      =>  isset($fullDataCampaign->targeting->campaignTrafficVertical) ? : ["list" => [], "isExcluded" => false],
            ]
        ];

        $externalUrl = 'api/v2/campaigns/';
        $curl = new \Curl\Curl();
        $curl->setHeader('Authorization', $this->clientData->apiKey);
        $curl->setHeader('Content-Type', "application/json");
        $curl->post($this->clientData->baseUrl.$externalUrl,$dataFromCreateCampaign);
        $curl->close();
        $responseCreateCampaign = $curl->response;
        if ($responseCreateCampaign && isset($responseCreateCampaign->result) && $responseCreateCampaign->result === 'success') {
            //Дургаем ID новосозанной кампании
            $curl = new \Curl\Curl();
            $curl->setHeader('Authorization', $this->clientData->apiKey);
            $curl->setHeader('Content-Type', "application/json");
            $curl->get($this->clientData->baseUrl.'v1.0/api/client/campaigns/?limit=1&page=1');
            $curl->close();
            $newCampaignId = $curl->response;
            return $newCampaignId[0]->name === $newCampName ? $newCampaignId[0]->id : false;
        } else return false;
    }

    private function cancelModeration(string $campaignId):bool{
        $externalUrl = 'v1.0/client/campaigns/cancel/';
        $listCampaignsFromCancelModeration = ["campaignIds"  =>  [$campaignId]];
        $curl = new \Curl\Curl();
        $curl->setHeader('Authorization', 'Bearer '.$this->clientData->token);
        $curl->setHeader('Content-Type', "application/json");
        $curl->put($this->clientData->baseUrl.$externalUrl,$listCampaignsFromCancelModeration);
        $curl->close();
        $responseCancelModeration = $curl->response;
        return $responseCancelModeration && isset($responseCancelModeration->result) && $responseCancelModeration->result === 'success';
    }


    public function removeCampaign(string $campaignId){
        $externalUrl = 'v1.0/client/campaigns/to_archive/';
        //Получаем статус кампании
        $oldStatusCampaign = $this->getStatusCampaign($campaignId);
        if ($oldStatusCampaign != "removeCampaignmoderation")
            $this->stoppedCampaign($campaignId);
         else
            $this->cancelModeration($campaignId);
        //Удаляем кампанию
        $listCampaignsFromRemove = ["campaignIds"  =>  [$campaignId]];
        $curl = new \Curl\Curl();
        $curl->setHeader('Authorization', 'Bearer '.$this->clientData->token);
        $curl->setHeader('Content-Type', "application/json");
        $curl->put($this->clientData->baseUrl.$externalUrl,$listCampaignsFromRemove);
        $curl->close();
        $responseRemove = $curl->response;
        return $responseRemove && isset($responseRemove->result) && $responseRemove->result === 'success';
    }

    public function getMinBid(int $sourceId, string $countryCode){
        return Minbid::query()->where([['network','=',($sourceId == 117 ? 'clickaduSkim' : 'clickadu')],
                                        ['code','=',(string)$countryCode]])->get()[0]->bid;
    }

    public function getStatClicks(){
        parent::getStatClicks(); // TODO: Change the autogenerated stub
    }

}
