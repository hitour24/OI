<?php
namespace App\Classes\Network\Entity;
require_once dirname(__DIR__).'/interfaces/IStatusCampaign.php';
use App\Interfaces\Network\IStatusCampaign;

class StatusCampaign implements IStatusCampaign {
    private string $noValidStatus;
    public string $validStatus;

    /**
     * StatusCampaign constructor.
     * @param string $noValidStatus
     */
    public function __construct(string $noValidStatus){
        $this->noValidStatus = $noValidStatus;
        $this->validStatus = $this->check();
    }


    private function check():string{
        switch ($this->noValidStatus){
            case'moderation':
                return self::moderation;
                break;
            case 'working':
                return self::resume;
                break;
            case 'stopped':
                return self::stopped;
                break;
            case 'rejected':
                return self::reject;
                break;
        }
//        return match ($this->noValidStatus) {
//            "moderation" => self::moderation,
//            "working" => self::resume,
//            "stopped" => self::stopped,
//            "rejected" => self::reject,
//        };
    }
}