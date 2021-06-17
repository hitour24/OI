<?php

namespace App\Classes\Network;
require_once dirname(__DIR__).'/interfaces/INetwork.php';

use \App\Interfaces\Network\INetwork;
use App\Classes\Network\Entity\Balance;
use App\Classes\Network\Entity\CampaignNetwork;
use App\Classes\Network\Entity\DataSetPlacement;
use App\Models\Source;

class Network implements INetwork {
    protected \stdClass $clientData;
    protected \stdClass $loginData;
    protected string $baseUrl = '';
    protected string $apiKey = '';
    /**
     * Network constructor.
     */
    public function __construct(UserNetwork $loginData){
        $this->loginData = $loginData->login();
        $this->clientData = $this->createDefaultJson();
        $this->clientData->apiKey = $this->apiKey;
        $this->clientData->baseUrl = $this->baseUrl;
        $this->auth();
    }

    protected function createDefaultJson(){
        return json_decode('{}');
    }

    protected function auth(){}

    public function getBalance():int{}

    public function addCampaign(CampaignNetwork $dataCampaignNetwork){}

    public function removeCampaign(string $campaignId){}

    public function getMinBid(int $sourceId, string $countryCode){}

    public function resumeCampaign(string $campaignId):bool{}
    public function stoppedCampaign(string $campaignId):bool{}
    public function getStatusCampaign(string $campaignId){}
    protected function setPlacementsData(DataSetPlacement $dataSetPlacement){}
    public function setPlacements(DataSetPlacement $dataSetPlacement){
        // 1 - пустой вайт => стопаем кампанию
        if (!$dataSetPlacement->typePlacements && !$dataSetPlacement->placements){
            return $this->stoppedCampaign($dataSetPlacement->campaignId);
        }
        //2 - внесение площадок
        $responseSetPlacements =  $this->setPlacementsData($dataSetPlacement);
        if (!$responseSetPlacements) return false;
        //3 - запускаем кампанию
        return $this->resumeCampaign($dataSetPlacement->campaignId);
    }

    protected function getFullDataCampaign(string $campaignId):\stdClass{}
    public function getStatClicksByTargets(){}

}