<?php

namespace App\Classes\Network;;
require_once dirname(__DIR__).'/interfaces/ITargetUrl.php';
require_once dirname(__DIR__).'/interfaces/INameCampaign.php';
use \App\Interfaces\Network\INameCampaign;
use App\Models\Source;

class NameCampaign implements INameCampaign {
    protected int $sourceId;
    protected string $countryCode;
    protected int $stage;

    /**
     * NameCampaign constructor.
     * @param int $sourceId
     * @param string $countryCode
     * @param int $stage
     */
    public function __construct(int $sourceId, string $countryCode, int $stage){
        $this->sourceId = $sourceId;
        $this->countryCode = $countryCode;
        $this->stage = $stage;
    }


    public function generate():string{
        $countryCode = $this->countryCode;
        $stage = $this->stage;
        $nameSource = Source::query()->where('source_id','=',$this->sourceId)->get()[0]->name;
        $nameCampaign = $nameSource;
        if(strpos($nameSource,"(")){
            $nameCampaign = substr($nameSource,strpos($nameSource,"("),strlen($nameSource));
        }
        return $countryCode.'_'.$stage.' '.$nameCampaign;
    }
}