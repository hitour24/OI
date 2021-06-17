<?php

namespace App\Http\Controllers\Api;

use App\Classes\Network\Clickadu;
use App\Classes\Network\Entity\CampaignNetwork;
use App\Classes\Network\Entity\DataSetPlacement;
use App\Classes\Network\TargetUrl;
use App\Classes\Network\UserNetwork;
use App\Classes\Triggers\Trigger;
use Logger;
use App\Http\Controllers\Api\Response\ResponseApi;
use App\Models\Campaign;
use App\Models\Source;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use mysql_xdevapi\Exception;

require_once dirname(__DIR__) . '/../../classes/Network.php';
require_once dirname(__DIR__) . '/../../classes/Networks/Clickadu.php';
require_once dirname(__DIR__) . '/../../classes/Networks/Clickaine.php';
require_once dirname(__DIR__) . '/../../classes/UserNetwork.php';
require_once dirname(__DIR__) . '/../../classes/ResponseApi.php';

class NetworkController extends Controller{

    public function __construct(){
        $this->middleware('auth.apikey');
    }


    public function removeCampaign(int $campaignId){
        try {
            if (isset($campaignId)) {
                $responseRemoveCampaign = Campaign::remove($campaignId);
                return $responseRemoveCampaign ? response()->json(new ResponseApi(['result' => STATUS_SUCCESS_DELETE_CAMPAIGN]))
                    :   response()->json(new ResponseApi(false,ERROR_CODE,STATUS_CREATE_CAMPAIGN_DELETED_EN),ERROR_CODE);
            } else
                return response()->json(new ResponseApi(ERROR_PARAMS_TITLE,ERROR_CODE),ERROR_CODE);
        } catch (\Exception $error) {
            return response()->json(new ResponseApi($error->getMessage(),ERROR_CODE), ERROR_CODE);;
        }
    }


    public function addCampaign(Request $data){
        try {
//            Log::channel('stack')->debug('Something happened!');
//            Logger::error('ОШибка созания!');
//            return ;
            return (new Trigger())->actionCampaign();
            $data = json_decode(json_encode($data->json()->all()));
            if (isset($data->SourceID) && isset($data->Bid) && isset($data->CountryCode) && isset($data->Stage) && isset($data->Targets) && isset($data->Type_targets)){
                $newIdCampaign = Campaign::add($data);
                return gettype($newIdCampaign) === 'integer' ? response()->json(new ResponseApi(['campaign_id' => (int)$newIdCampaign]))
                                                    :   response()->json(new ResponseApi($newIdCampaign,ERROR_CODE),ERROR_CODE);
            } else
                return response()->json(new ResponseApi(ERROR_PARAMS_TITLE,ERROR_CODE),ERROR_CODE);
        } catch (\Exception $error) {
            return response()->json(new ResponseApi($error->getMessage(),ERROR_CODE), ERROR_CODE);;
        }
    }

    public function addCampaigns(Request $data){
        try {
            $data = json_decode(json_encode($data->json()->all()));
            if (is_array($data)){
                //ПРоверяем состав пакета на нужные свойства обьекта
                $checkParams = array_values((array)array_filter($data, function ($el) {
                    return !isset($el->SourceID) || !isset($el->Bid) || !isset($el->CountryCode) || !isset($el->Stage) || !isset($el->Targets) || !isset($el->Type_targets);
                }));
                if ($checkParams) return response()->json(new ResponseApi(json_encode($checkParams),ERROR_CODE,ERROR_PARAMS_TITLE),ERROR_CODE);

                //ПРоверяем состав пакета на дубли
                $checkParams = array_values((array)array_filter($data, function ($el) {
                    return Campaign::checkDoublesCampaigns($el->SourceID,$el->CountryCode.'_'.$el->Stage)->double;
                }));
                if ($checkParams) return response()->json(new ResponseApi(json_encode($checkParams),ERROR_CODE,STATUS_CREATE_CAMPAIGN_DOUBLE_EN),ERROR_CODE);

                $newIdsCampaigns = array_map(function ($el){
                    return Campaign::add($el);
                }, $data);
                return response()->json(new ResponseApi($newIdsCampaigns));
            } else
                return response()->json(new ResponseApi(ERROR_TYPEDATA_TITLE,ERROR_CODE),ERROR_CODE);
        } catch (\Exception $error) {
            return response()->json(new ResponseApi($error->getMessage(),ERROR_CODE), ERROR_CODE);;
        }
    }


    public function getBalance(string $network){
        try {
            $classNetwork = 'App\Classes\Network\\' . $network;
            $balance = new $classNetwork(new UserNetwork($network));
            $balance = $balance->getBalance();
            $balanceJson = ["network" => $network, "value" => $balance];
            return $balance ? response()->json(new ResponseApi( $balanceJson),)
                : response()->json(new ResponseApi( $balanceJson,ERROR_CODE), ERROR_CODE);;
        } catch (\Exception $error){
            return response()->json(new ResponseApi($error->getMessage(),ERROR_CODE), ERROR_CODE);;
        }
    }

    public function setPlacements(Request $data){
        try {
            $data = json_decode(json_encode($data->json()->all()));
            $campId =  Campaign::getTsId((int)$data->CampaignID);
            $network = Source::getNameNetwork((int)$data->SourceID);
            $classNetwork = 'App\Classes\Network\\' . $network;
            $responseSetPlacements = new $classNetwork(new UserNetwork($network));
            $responseSetPlacements = $responseSetPlacements->setPlacements(new DataSetPlacement($campId,$data->Type_targets,$data->Targets));
            return $responseSetPlacements ? response()->json(New ResponseApi(['result'  =>  SUCCESS_SETPLACEMENTS]))
                                            : response()->json(New ResponseApi(['result'  =>  ERROR_SETPLACEMENTS], ERROR_CODE),ERROR_CODE);
        } catch (\Exception $error){
            return response()->json(new ResponseApi($error->getMessage(),ERROR_CODE), ERROR_CODE);;
        }
    }


    public function getMinBid(int $sourceId, string $countryCode){
        try {
            $network = Source::getNameNetwork((int)$sourceId);
            $classNetwork = 'App\Classes\Network\\' . $network;
            $minBid = new $classNetwork(new UserNetwork($network));
            $minBid = $minBid->getMinBid($sourceId,$countryCode);
            return  $minBid ? response()->json(New ResponseApi($minBid))
                            : response()->json(new ResponseApi('',ERROR_CODE), ERROR_CODE);;
        } catch (\Exception $error){
            return response()->json(new ResponseApi($error->getMessage(),ERROR_CODE), ERROR_CODE);;
        }
    }
}