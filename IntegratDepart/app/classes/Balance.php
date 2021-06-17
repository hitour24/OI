<?php
namespace App\Classes\Network\Entity;
use \App\Classes\Network\Entity\Entity;
require_once dirname(__DIR__).'/classes/Entity.php';

class Balance extends Entity {
    public string $network;
    public string $value;

    /**
     * Balance constructor.
     * @param string $network
     * @param string $value
     */
    public function __construct(string $network, string $value){
        $this->network = $this->parseClassName($network);
        $this->value = (int)$value;
    }




}