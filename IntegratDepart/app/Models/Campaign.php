<?php

namespace App\Models;

use App\Classes\Network\Clickadu;
use App\Classes\Network\Entity\CampaignNetwork;
use App\Classes\Network\Entity\StatusCampaign;
use App\Classes\Network\UserNetwork;
use App\Helpers\Helper;
use App\Helpers\Logger;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model{
    protected $table = 'campaigns_test';
    public $timestamps = false;
//    protected $dates = ['created_at', 'updated_at'];
//
//    protected $fillable = ['name','code_alpha2','code_alpha3'];


    /**
     * Фильтрация площадоёк от мусора
     * @param string $placements
     * @param string $nameNetwork
     * @return string
     */
    static private function filterPlacements(string $placements,string $nameNetwork):string{
        $errorZones = ['{zoneid}','{siteId}','{siteid}','{zone_id}','{site_id}','{site_id}'];
        if ($nameNetwork == "adsterra") array_push($errorZones,"143975671008baricant.com","143975671008baricant.com","pornforrelax.com",15783413,15443969,14558912,15733335,15280514,15766691,15256124,15324169,15310472,15439990,15242468,14375936,15176605,14187796,15745403,15749838,15143322,15136985,14917405,15242470,15405416,15329898,15002449,15159248,15672939,15379202,15538086,15768285);
        if ($nameNetwork == "clickadu") array_push($errorZones,662965,717610,563554,529242,609768,599781,510268,551904,511514,627729, 637242,521246,580779,567036,578797,651342,623646,550823,640711,511735, 544733,603740,523413,522816,715315,647126,668105,547956,603337,515912, 518051,605833,622016,632291,536263,699122,625637,603462,533234,632278, 558601,621694,621692,694612,543867,672073,710723,556719,567348,676076,546922,622265,586031,583591,697956,653345,678724,611977,688250,671287,704767,673470,577220,563921,607517,640634, 526420,527147,526017,687698,610800,535292,540746,636842,600380,623310,552683,587879,591166,523144,544913,653222,662726,513170,671014,565513,581347,721545,614250,567173,699858,517717,531276,702926,664224,513824,550792,635884,693177,510604,532715,605417,617801,630594,591402,511686,644516,587359,653045,585498,668577,528711,634476,712281,675316,511737,677741,617098,696374,652228,570028,628342,615251,691777,688524,710915,523834,528510,615628,629968,640160,630071,563080,511613,550161,532948,600327,574393,607493,575851,'462966xxxxxx','wXpbdsOVwnE',695432,584069,529211,625167,584796,609916,692808,543042,590913,684115,568200,681291,652970,649412,643073,693547,515956,711655,567837,563559,565847,588982,535713,531281,585098,3872564,71877227640,569522,689926,634486,676968,560214,634519,693801,592544,512692,568349);
        if ($nameNetwork == "exoclick") array_push($errorZones,"sasas");
        if ($nameNetwork == "admaven") array_push($errorZones,"244026.","244025.","243287.","268530.","260422.","243292.","243292.153187871050lonkem.com","143975671008baricant.com","243292.153187871050lonkem.com","243292.153187871050lonkem.com");
        if ($nameNetwork == "activerevenue") array_push($errorZones, "{source_subid}");
        $newPlacements = explode(",",$placements);
        $newPlacements = array_values(array_unique(array_diff($newPlacements,$errorZones)));
        return implode(",",$newPlacements);
    }

    /**
     * Проверка на дубли sourceId - codeStage
     * @param int $sourceId
     * @param string $codeStage
     */
    static public function checkDoublesCampaigns(int $sourceId, string $codeStage){
        $double = Campaign::query()->where([
            ['SOURCE_ID','=',$sourceId],
            ['CODE_STAGE','=',$codeStage],
            ['TS_ID','<>',STATUS_CREATE_CAMPAIGN_DELETED],
            ['TS_ID','<>',STATUS_CREATE_CAMPAIGN_DELETED_REQUEST],
        ])->get()->count();
        $response = json_decode('{}');
        $response->double = $double > 0;
        $response->name = $codeStage.' '.$sourceId;
        return $response;
    }

    static public function getTsId(int $id):string{
        return self::query()->find($id)->TS_ID;
    }

    static private function getFullDataByNetworkCampId(string $campaignId){

    }

    static public function add(\stdClass $data){
        $codeStage = $data->CountryCode."_".$data->Stage;
        $sourceId = $data->SourceID;
        $bundleId = isset($data->BundleID) ?? '';
        $status = !$data->Targets && !$data->Type_targets ? 2 : 1;
        $nameNetwork = Source::getNameNetwork($sourceId);
        $newBid = (int)$data->Stage < 0 ? 'get min bid from DB' : $data->Bid;
        $subBid = (int)$data->Stage < 0 ? $data->Bid : 0;
        //фильтрация площадок
        $zones = self::filterPlacements($data->Targets,$nameNetwork);

        //Проверка на дубли
        $checkDoublesCampaigns = self::checkDoublesCampaigns($sourceId,$codeStage);
        if ($checkDoublesCampaigns->double) {
            Logger::error('Обнаружен дубль кампании '.$checkDoublesCampaigns->name);
            return $checkDoublesCampaigns;
        }

        return  self::query()->insertGetId([
            'STATUS'                =>  $status,
            'BUNDLE_ID'             =>  $bundleId,
            'TS_ID'                 =>  STATUS_CREATE_CAMPAIGN_IN_PROCESS,
            'NETWORK_NAME'          =>  $nameNetwork,
            'NOTE'                  =>  '',
            'CREATE_TIME'           => 0,
            'RUN_TIME'              => 0,
            'SOURCE_ID'             => $sourceId,
            'CODE_STAGE'            => $codeStage,
            'BID'                   => $newBid,
            'TYPE_PLACEMENTS'       => (int)$data->Type_targets,
            'PLACEMENTS'            => $zones,
            'REVERSE_BLACK_LIST'    => '',
            'BID_SUB'               => $subBid
        ]);
    }

    static private function getStatus(int $campaignId){
        return Campaign::query()->where('ID','=',$campaignId)->get()[0]->TS_ID;
    }

    static public function remove(int $campaignId){
        $statusCampaign =  self::getStatus($campaignId);
        switch ($statusCampaign) {
            case STATUS_CREATE_CAMPAIGN_DELETED:
            case STATUS_CREATE_CAMPAIGN_DELETED_REQUEST:
                return false;
                break;
        }
        self::query()->where('ID', $campaignId)->update(['TS_ID' => STATUS_CREATE_CAMPAIGN_DELETED_REQUEST]);
        return true;
    }

    static public function getCampaigns(){
        return self::query()->where([
            ['TS_ID','<>',STATUS_CREATE_CAMPAIGN_DELETED],
        ])->get();
    }


    public static function actionCreateInNetwork(string $network, int $idDb, CampaignNetwork $dataCampaignNetwork){
        $classNetwork = 'App\Classes\Network\\' . $network;
        $class = new $classNetwork(new UserNetwork($network));
        $addCampaign = $class->addCampaign($dataCampaignNetwork);
        if (!$addCampaign) {
            Logger::error('Ошибка создания кампании '.$dataCampaignNetwork->sourceId.' '.$dataCampaignNetwork->countryCode.'_'.$dataCampaignNetwork->stage.' в сети '.$network.'!');
        } else {
            self::updateCreateCampaign($idDb, $addCampaign);
            Logger::debug('Кампания '.$idDb.' в сети '.$network.' создана и в БД успешно записана!');
        }
        return $addCampaign;
    }

    public static function actionRemoveFromNetwork(string $network, int $idCampaign, int $idDb){
        $classNetwork = 'App\Classes\Network\\' . $network;
        $class = new $classNetwork(new UserNetwork($network));
        $removeCampaign = $class->removeCampaign((string)$idCampaign);
        if (!$removeCampaign) {
            Logger::error('Ошибка удаления кампании '.$idDb.' в сети '.$network.'!');
        } else {
            self::updateRemoveCampaign($idDb);
            Logger::debug('Кампания '.$idDb.' в сети '.$network.' удалена успешно!');
        }
        return $removeCampaign;
    }

    public static function actionCheckModerationInNetwork(string $network, int $idCampaign, int $idDb, string $placements, bool $typePlacements, $timeCreate){
        $classNetwork = 'App\Classes\Network\\' . $network;
        $userNetwork = new UserNetwork($network);
        $class = new $classNetwork($userNetwork);
        $actualStatus = $class->getStatusCampaign((string)$idCampaign);
        Logger::debug('Статус кампании '.$idDb.' сети '.$network.' - '.$actualStatus);
        switch (mb_strtolower($network)){
            case 'clickadu':
                $newHour = Helper::timeDifference($timeCreate, 1)[1];
                switch ($actualStatus) {
                    //кампания со статусом "запущена"
                    case STATUS_CAMPAIGN_RESUME:
                        //Если пустой вайт, то стопаем кампанию и записываем в БД
                        if (empty($placements) && !$typePlacements) {
                            $class->stoppedCampaign($idCampaign);
                            Logger::debug('Кампания  '.$idDb.' сети '.$network.' остановлена т.r пришел пустой вайт!');
                            self::updateAfterModerationCampaign($idDb, $idCampaign);
                            return false;
                        // Иначе
                        } else {
                            //если наступил новый час записываем ID компании в таблицу
                            if (time() >= $newHour){
                                self::updateAfterModerationCampaign($idDb, $idCampaign);
                            } else {
                                //иначе стопаем компанию и записываем втаблицу нвый час
                                $class->stoppedCampaign($idCampaign);
                                self::updateAfterModerationRunTimeCampaign($idDb, $newHour);
                            }
                        }
                        break;
                    //кампания со статусом "запущена"
                    case STATUS_CAMPAIGN_STOPPED:
                        //если наступил новый час Запускаем компанию и записываем в таблица ID компании
                        if (time() >= $newHour){
                            $class->resumeCampaign($idCampaign);
                            Logger::debug('Кампания  '.$idDb.' сети '.$network.' запущена после модерации в новом часу!');
                            self::updateAfterModerationCampaign($idDb, $idCampaign);
                        }
                        break;
                }
                break;
        }
        return $actualStatus;

    }

    private static function updateCreateCampaign(int $idDb, int $idCampaign){
        self::query()->where('ID', $idDb)->update(
            ['TS_ID' => STATUS_CREATE_CAMPAIGN_MODERATION,
             'NOTE' => $idCampaign, 'CREATE_TIME'  => time()]
        );
    }

    private static function updateAfterModerationCampaign(int $idDb, int $idCampaign){
        self::query()->where('ID', $idDb)->update(['TS_ID' => $idCampaign]);
    }

    private static function updateAfterModerationRunTimeCampaign(int $idDb, $newHour){
        self::query()->where('ID', $idDb)->update(['RUN_TIME' => $newHour]);
    }

    private static function updateRemoveCampaign(int $idDb){
        self::query()->where('ID', $idDb)->update(['TS_ID' => STATUS_CREATE_CAMPAIGN_DELETED]);
    }


    static public function acionCampaigns(){
        $allCampaigns = self::getCampaigns();
        for ($i = 0; $i < count($allCampaigns); $i++){
            $current = $allCampaigns[$i];
            $id = $current->ID;
            $status = $current->TS_ID;
            $placements = $current->PLACEMENTS;
            $typePlacements = $current->TYPE_PLACEMENTS;
            $network = $current->NETWORK_NAME;

            //Создается ....
            if ($status === STATUS_CREATE_CAMPAIGN_IN_PROCESS) {
                $classNetwork = 'App\Classes\Network\\' . $network;
                $balance = new $classNetwork(new UserNetwork($network));
                $balance = $balance->getBalance();
            }

            //На модерации... (для некоторых сетей)
            if ($status === STATUS_CREATE_CAMPAIGN_MODERATION) {
                //2
            }

            //Удаление...
            if ($status === STATUS_CREATE_CAMPAIGN_DELETED_REQUEST) {
                //3
            }

            return json_encode($status);
        }
    }
}
