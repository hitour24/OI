<?php

namespace App\Interfaces\Network;
require_once dirname(__DIR__).'/classes/Balance.php';
require_once dirname(__DIR__).'/classes/TargetUrl.php';
require_once dirname(__DIR__).'/classes/DataSetPlacement.php';
require_once dirname(__DIR__).'/classes/CampaignNetwork.php';
require_once dirname(__DIR__).'/classes/StatusCampaign.php';

use App\Classes\Network\Entity\Balance;
use App\Classes\Network\Entity\CampaignNetwork;
use App\Classes\Network\Entity\DataSetPlacement;

interface INetwork {
    public function getBalance():int;
    public function addCampaign(CampaignNetwork $dataCampaignNetwork);
    public function removeCampaign(string $campaignId);
    public function setPlacements(DataSetPlacement $dataSetPlacement);
    public function getStatClicksByTargets();
    public function getMinBid(int $sourceId, string $countryCode);
    public function resumeCampaign(string $campaignId):bool;
    public function stoppedCampaign(string $campaignId):bool;
    public function getStatusCampaign(string $campaignId);
}