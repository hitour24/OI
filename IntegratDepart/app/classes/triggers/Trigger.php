<?php
namespace App\Classes\Triggers;
require_once dirname(__DIR__).'/../interfaces/ITrigger.php';

use App\Classes\Network\Entity\CampaignNetwork;
use App\Helpers\Logger;
use \App\Interfaces\Network\ITrigger;
use App\Models\Campaign;

class Trigger implements ITrigger{

    public function actionCampaign(){
        try {
            $allCampaigns = Campaign::getCampaigns();
            for ($i = 0; $i < count($allCampaigns); $i++) {
                $current = $allCampaigns[$i];
                $id = $current->ID;
                $sourceId = (int)$current->SOURCE_ID;
                $bundleId = (int)$current->BUNDLE_ID;
                $countryCode = explode('_', $current->CODE_STAGE)[0];
                $stage = (int)explode('_', $current->CODE_STAGE)[1];
                $bid = (float)$current->BID;
                $placements = $current->PLACEMENTS;
                $typePlacements = (bool)$current->TYPE_PLACEMENTS;
                $note = $current->NOTE;
                $timeCreate = $current->CREATE_TIME;

                $status = $current->TS_ID;
                $network = $current->NETWORK_NAME;

                //Создается ....
                if ($status === STATUS_CREATE_CAMPAIGN_IN_PROCESS) {
                    Campaign::actionCreateInNetwork($network, $id, new CampaignNetwork($sourceId, $bundleId, $countryCode, $bid, $stage, $typePlacements, $placements));
                }

                //На модерации... (для некоторых сетей)
                if ($status === STATUS_CREATE_CAMPAIGN_MODERATION) {
                    $checkModer = Campaign::actionCheckModerationInNetwork($network, $note, $id, $placements, $typePlacements, $timeCreate);
                    if (!$checkModer) continue;
                }

                //Удаление...
                if ($status === STATUS_CREATE_CAMPAIGN_DELETED_REQUEST) {
                    Campaign::actionRemoveFromNetwork($network, $note, $id);
                }

//                return json_encode($status);
            }
        } catch (\Exception $error){
            Logger::error('Ошибка дийствий по кампаниям '.$error->getMessage());
        }
    }
}