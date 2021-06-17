<?php
namespace App\Classes\Network\Entity;
class DataAddCampaign {
    public int $bundleId;
    public int $sourceId;
    public int $stage;
    public float $bid;
    public string $countryCode;
    public bool $typePlacements;
    public string $placements;

    /**
     * DataAddCampaign constructor.
     * @param string $templateId
     * @param int $bundleId
     * @param int $sourceId
     * @param int $stage
     * @param float $bid
     * @param string $countryCode
     * @param bool $typePlacements
     * @param string $placements
     */
    public function __construct(string $templateId, int $bundleId, int $sourceId, int $stage, float $bid, string $countryCode, bool $typePlacements, string $placements)
    {
        $this->templateId = $templateId;
        $this->bundleId = $bundleId;
        $this->sourceId = $sourceId;
        $this->stage = $stage;
        $this->bid = $bid;
        $this->countryCode = $countryCode;
        $this->typePlacements = $typePlacements;
        $this->placements = $placements;
    }


}
