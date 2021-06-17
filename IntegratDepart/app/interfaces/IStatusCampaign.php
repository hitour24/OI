<?php
namespace App\Interfaces\Network;
interface IStatusCampaign {
    const moderation = STATUS_CAMPAIGN_MODERATION;
    const reject = STATUS_CAMPAIGN_REJECT;
    const stopped = STATUS_CAMPAIGN_STOPPED;
    const resume = STATUS_CAMPAIGN_RESUME;
}