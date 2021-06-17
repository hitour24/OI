<?php

namespace App\Classes\Network;;
use \App\Interfaces\Network\ITargetUrl;
use App\Models\Domain;

require_once dirname(__DIR__).'/interfaces/ITargetUrl.php';

class TargetUrl implements ITargetUrl {
    private string $templateUrl;
    private int $sourceId;
    private string $countryCode;
    private int $stage;
    private string $bundleId;
    private bool $oldVer; //old => campaignid
    private string $extra;
    private int $difficulty;
    private float $bid;

    /**
     * TargetUrl constructor.
     * @param string $templateUrl
     * @param string $sourceId
     * @param string $countryCode
     * @param int $stage
     * @param string $bundleId
     * @param int $difficulty
     * @param bool $oldVer
     * @param string $extra
     */
    public function __construct(string $templateUrl, string $sourceId, string $countryCode, int $stage, string $bundleId = '', int $difficulty = 100, bool $oldVer = false, float $bid = -1, string $extra = '')
    {
        $this->templateUrl = $templateUrl;
        $this->sourceId = $sourceId;
        $this->countryCode = $countryCode;
        $this->stage = $stage;
        $this->bundleId = $bundleId;
        $this->oldVer = $oldVer;
        $this->extra = $extra;
        $this->difficulty = $difficulty;
        $this->bid = $bid;
    }


    private function generateDomain():string{
        $responseDomain = Domain::query()->where('source_id','=',$this->sourceId)->get()[0];
        return in_array($this->countryCode, explode(',',$responseDomain->exclusive_countries)) ? $responseDomain->exclusive : $responseDomain->common;
    }


    private function initQueryUrl(\stdClass $parseTemplateUrl):array{
        $variantsCampaign = self::excludesVariantsParamsQuery;
        $querySpit = explode('&',$parseTemplateUrl->query);
        $newQuery = array_values(array_filter($querySpit, function ($param)use($variantsCampaign){
            $nameParam = explode('=',$param)[0];
            return !in_array($nameParam,$variantsCampaign);
        }));
        if ($this->bid >= 0)
            $newQuery = array_map(function ($el){
                $explodeEl = explode('=',$el);
                return $explodeEl[0] === 'cost' ? 'cost='.$this->bid : $el;
            }, $newQuery);
        return $newQuery;
    }


    private function generateCampaignId():string{
        $newCampaignId = $this->countryCode.'_'.$this->stage;
        return ($this->oldVer ? self::paramCampaignIdOld : self::paramCampaignId).'='.$newCampaignId;
    }

    private function generateSourceId():string{
        $newSourceId = $this->sourceId;
        return self::paramSource.'='.$newSourceId;
    }

    private function generateDifficulty():string{
        $newDifficulty = $this->difficulty;
        return self::paramDifficulty.'='.$newDifficulty;
    }


    private function generateFullId(array $findSiteId):string{
        $siteId = array_values(array_filter($findSiteId, function ($el){
            $explodeElems = explode('=',$el)[0];
            return $explodeElems === 'site_id' || $explodeElems === 'placementid';
        }));
        if (count($siteId)) $siteId = explode('=',$siteId[0])[1];
        else return false;
        $newFullId = $this->countryCode.'_'.$this->stage.($this->bundleId ? '_'.$this->bundleId : '').'_'.$siteId;
        return self::paramFullId.'='.$newFullId;
    }

    private function generateCountry():string{
        return self::paramCountry.'='.$this->countryCode;
    }

    private function generateBundle():string{
        return self::paramBundle.'='.$this->bundleId;
    }

    private function agregateParseUrl(\stdClass $urlParce){
        $newObjUrlParse = json_decode('{}');
        if (isset($urlParce->fragment))
            $newObjUrlParse->query = $urlParce->query.'#'.$urlParce->fragment;
        else
            $newObjUrlParse->query = $urlParce->query;
        return $newObjUrlParse;
    }

    public function generate():string{
        $parseTemplateUrl = $this->agregateParseUrl(json_decode(json_encode(parse_url($this->templateUrl))));
//        return json_encode($parseTemplateUrl);
        $queryUrl = $this->initQueryUrl($parseTemplateUrl);
//        return json_encode($queryUrl);
        $sourceId = $this->generateSourceId();
        $campaignId = $this->generateCampaignId();
        $fullId = $this->generateFullId($queryUrl);
        if (!$fullId) return false;
        $countryCode = $this->generateCountry();
        $bundleId = $this->generateBundle();
        $difficulty = $this->generateDifficulty();
        array_push($queryUrl, $sourceId);
        array_push($queryUrl, $campaignId);
        array_push($queryUrl, $fullId);
        array_push($queryUrl, $countryCode);
        array_push($queryUrl, $bundleId);
        array_push($queryUrl, $difficulty);
//        if ($this->extra) array_push($queryUrl, )
        $queryUrl = implode('&',$queryUrl);

        $domain = $this->generateDomain();
        return self::paramScheme.$domain.self::paramPath.'?'.$queryUrl;
    }
}