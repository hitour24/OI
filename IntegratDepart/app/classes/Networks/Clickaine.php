<?php

namespace App\Classes\Network;;

use App\Classes\Network\Entity\Balance;
use App\Classes\Network\Entity\CampaignNetwork;
use App\Classes\Network\Entity\DataSetPlacement;
use App\Models\Minbid;

class Clickaine extends Network {

    /**
     * Clickaine constructor.
     */
    public function __construct(UserNetwork $loginData){
        $this->baseUrl = API_BASE_URL_CLICKAINE;
        $this->apiKey = API_KEY_CLICKAINE;
        parent::__construct($loginData);
    }

    protected function auth(){
        $this->clientData->token = API_TOKEN_CLICKAINE;
    }

    public function getBalance():int{
        $externalUrl = 'v1/public/finance/balance';
        $curl = new \Curl\Curl();
        $curl->setHeader('X-Clickaine-API-Key', $this->clientData->apiKey);
        $curl->get($this->clientData->baseUrl.$externalUrl);
        $responseBalance = $curl->response;
        $curl->close();
        return $responseBalance ?
            isset($responseBalance->balanceCommon) ?
                    $responseBalance->balanceCommon? : false
                :false
            : false;
    }

    public function getStatusCampaign(string $campaignId):string{
        $externalUrl = 'v1/public/advertiser/campaigns/'.$campaignId.'/status';
        $curl = new \Curl\Curl();
        $curl->setHeader('X-Clickaine-API-Key', $this->clientData->apiKey);
        $curl->get($this->clientData->baseUrl.$externalUrl);
        $curl->close();
        $responseStatus = $curl->response;
        return $responseStatus ?
            $responseStatus->status ?: false
            : false;
    }

    public function resumeCampaign(string $campaignId):bool{
        $externalUrl = 'v1/public/advertiser/campaigns/'.$campaignId.'/start';
        //Получаем статус кампании
        $oldStatusCampaign = $this->getStatusCampaign($campaignId);
        if ($oldStatusCampaign !== "active") {
            $curl = new \Curl\Curl();
            $curl->setHeader('X-Clickaine-API-Key', $this->clientData->apiKey);
            $curl->get($this->clientData->baseUrl.$externalUrl);
            $curl->close();
            $responseResume = $curl->getHttpStatusCode();
            return $responseResume && $responseResume === 200;
        }
        return true;
    }

    public function stoppedCampaign(string $campaignId):bool{
        $externalUrl = 'v1/public/advertiser/campaigns/'.$campaignId.'/stop';
        //Получаем статус кампании
        $oldStatusCampaign = $this->getStatusCampaign($campaignId);
        if ($oldStatusCampaign !== "inactive") {
            $curl = new \Curl\Curl();
            $curl->setHeader('X-Clickaine-API-Key', $this->clientData->apiKey);
            $curl->get($this->clientData->baseUrl.$externalUrl);
            $curl->close();
            $responseResume = $curl->getHttpStatusCode();
            return $responseResume && $responseResume === 200;
        }
        return true;
    }

    private function getCampaigns(){
        $externalUrl = 'v1/public/advertiser/campaigns';
        $curl = new \Curl\Curl();
        $curl->setHeader('X-Clickaine-API-Key', $this->clientData->apiKey);
        $curl->get($this->clientData->baseUrl.$externalUrl);
        $curl->close();
        $responseCampaigns = $curl->response;
        return $responseCampaigns->campaigns ?? false;
    }

    private function getNameFilter(string $campaignId){
        $allCampaigns = $this->getCampaigns();
        return array_values(array_filter($allCampaigns,function ($campaign)use($campaignId){
            return $campaign->id === (int)$campaignId;
        }))[0]->name;
    }


    protected function setPlacementsData(DataSetPlacement $dataSetPlacement){
        $externalUrl = 'v1/public/advertiser/';
        $campaignId = $dataSetPlacement->campaignId;
        $typePlacements = $dataSetPlacement->typePlacements;
        $placements = json_encode(array_map('intval', explode(",",$dataSetPlacement->placements)));

        $filterName = $this->getNameFilter($campaignId);
        $typeList = !$typePlacements ? 'allowed' : 'disallowed';
        $requests = [
            [
                "url"       =>  $externalUrl.'targets/allowed?name='.$filterName,
                'targets'   =>  '{"sites": [],"publishers": [],"hosts": []}'
            ],
            [
                "url"       =>  $externalUrl.'targets/disallowed?name='.$filterName,
                'targets'   =>  '{"sites": [],"publishers": [],"hosts": []}'
            ],
            [
                "url"       =>  $externalUrl.'targets/'.$typeList.'?name='.$filterName,
                'targets'   =>  '{"sites": '.$placements.',"publishers": [],"hosts": []}'
            ]
        ];

        for ($i = 0; $i < count($requests); $i++){
            $request = $requests[$i];
            $curl = new \Curl\Curl();
            $curl->setHeader('X-Clickaine-API-Key', $this->clientData->apiKey);
            $curl->put($this->clientData->baseUrl.$request->url, $request->targets);
            $curl->close();
            $responseSetData = $curl->getHttpStatusCode();
            if (!$responseSetData || $responseSetData !== 200) return false;
        }
    }

    public function removeCampaign(string $campaignId){
        $externalUrl = 'v1/';
        //1 - удаляем кампанию
        $externalUrlDeleteCampaign = $externalUrl.'advertiser/campaigns/'.$campaignId;
        $curl = new \Curl\Curl();
        $curl->setHeader('Authorization', 'Bearer '.$this->clientData->token);
        $curl->delete($this->clientData->baseUrl.$externalUrlDeleteCampaign);
        $curl->close();
        $responseDeleteCampaign = $curl->response;

        $idFilter = $responseDeleteCampaign->targets[0];
        $idCreative = $responseDeleteCampaign->target->creatives[0]->id;

        //2 - удаляем фильтр
        $externalUrlDeleteFilter = $externalUrl.'advertiser/targets/'.$idFilter;
        $curl = new \Curl\Curl();
        $curl->setHeader('Authorization', 'Bearer '.$this->clientData->token);
        $curl->delete($this->clientData->baseUrl.$externalUrlDeleteFilter);
        $curl->close();
        $responseDeleteFilter = $curl->getHttpStatusCode();
        if (!$responseDeleteFilter || $responseDeleteFilter !== 200) return false;

        //3 - удаляем креатив
        $externalUrlDeleteCreative = $externalUrl.'creatives/'.$idCreative;
        $curl = new \Curl\Curl();
        $curl->setHeader('Authorization', 'Bearer '.$this->clientData->token);
        $curl->delete($this->clientData->baseUrl.$externalUrlDeleteCreative);
        $curl->close();
        $responseDeleteCreative = $curl->getHttpStatusCode();
        return $responseDeleteCreative && $responseDeleteCreative === 200;
    }


    public function getMinBid(int $sourceId, string $countryCode){
        return Minbid::query()->where([['network','=','clickaine'],
            ['code','=',(string)$countryCode]])->get()[0]->bid;
    }

}
