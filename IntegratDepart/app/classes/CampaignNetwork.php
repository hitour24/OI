<?php
namespace App\Classes\Network\Entity;
require_once dirname(__DIR__).'/interfaces/ICampaignNetwork.php';
require_once dirname(__DIR__).'/classes/NameCampaign.php';

use App\Classes\Network\NameCampaign;
use App\Classes\Network\TargetUrl;
use App\Interfaces\Network\ICampaignNetwork;
use App\Models\Source;


class CampaignNetwork implements ICampaignNetwork{
    public string $name;
    public string $templateId;
    public int $sourceId;
    public int $bundleId;
    public string $countryCode;
    public float $bid;
    public int $stage;
    public string $targetUrl;
    public bool $typePlacements;
    public string $placements;

    /**
     * CampaignNetwork constructor.
     * @param int $sourceId
     * @param int $bundleId
     * @param string $countryCode
     * @param float $bid
     * @param int $stage
     * @param bool $oldVer
     * @param bool $typePlacements
     * @param string $placements
     */
    public function __construct(int $sourceId, int $bundleId, string $countryCode, float $bid, int $stage,  bool $typePlacements = false, string $placements = ''){
        $this->sourceId = $sourceId;
        $this->bundleId = $bundleId;
        $this->countryCode = $countryCode;
        $this->bid = $bid;
        $this->stage = $stage;
        $this->typePlacements = $typePlacements;
        $this->placements = $placements;

        $newNameCampaign = new NameCampaign($sourceId,$countryCode,$stage);
        $this->name = $newNameCampaign->generate();

        $this->templateId = Source::query()->where('source_id','=',$sourceId)->get()[0]->template_id;
    }

    public function generateTargetUrl(string $templateTargetUrl, int $difficulty, bool $oldVer = false):string{
        $targetUrl =  new TargetUrl($templateTargetUrl, $this->sourceId, $this->countryCode, $this->stage, $this->bundleId, $difficulty, $oldVer, $this->bid);
        return $targetUrl->generate();
    }

}
