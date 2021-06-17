<?php
namespace App\Interfaces\Network;
interface ITargetUrl{
    const paramPath = '/camp';
    const paramScheme = 'https://';
    const paramCampaignIdOld = 'campaignid';
    const paramCampaignId = 'campaign_id';
    const paramFullId = 'full_id';
    const paramBundle = 'bundle';
    const paramCountry = 'country';
    const paramSiteId = 'site_id';
    const paramSource = 'source_id';
    const paramDifficulty = 'difficulty';
    const paramCost = 'cost';
    const excludesVariantsParamsQuery = ['campaignid','campaign_id','id','full_id','country','bundle','difficulty','campaign_Id', 'campaing_id'];
    public function generate();
}
