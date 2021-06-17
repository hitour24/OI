<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RemoveCampaignController extends Controller{
    public function remove(int $sourceId, string $campaignId){
    return $sourceId.' - '.$campaignId;
    }
}
