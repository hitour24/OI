<?php
namespace App\Classes\Network\Entity;
class DataSetPlacement {
    public string $campaignId;
    public bool $typePlacements;
    public string $placements;

    /**
     * DataSetPlacement constructor.
     * @param string $campaignId
     * @param bool $typePlacements
     * @param string $placements
     */
    public function __construct(string $campaignId, bool $typePlacements = false, string $placements = '')
    {
        $this->campaignId = $campaignId;
        $this->typePlacements = $typePlacements;
        $this->placements = $placements;
    }

}
