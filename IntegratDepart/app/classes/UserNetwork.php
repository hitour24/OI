<?php
namespace App\Classes\Network;


require_once dirname(__DIR__).'/interfaces/IUserNetwork.php';
use \App\Interfaces\Network\IUserNetwork;

class UserNetwork implements IUserNetwork{
    private string $login = NETWORK_USER_LOGIN;
    private string $password = NETWORK_USER_PASSWORD;
    private string $userAgent = NETWORK_USER_AGENT;
    /**
     * UserNetwork constructor.
     */
    public function __construct($network = null){
        if ($network === 'mpay') {
            $this->login = '1234';
            $this->password = '9999';
        }
    }

    public function login(){
        return (object)array(
            "login" => $this->login,
            "password"  => $this->password,
            "userAgent" => $this->userAgent,
        );
    }
}