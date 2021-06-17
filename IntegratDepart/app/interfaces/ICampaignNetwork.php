<?php
namespace App\Interfaces\Network;

interface ICampaignNetwork {
    public function generateTargetUrl(string $templateTargetUrl, int $difficulty, bool $oldVer = false):string;
}
