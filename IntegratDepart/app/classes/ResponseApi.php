<?php

namespace App\Http\Controllers\Api\Response;

class ResponseApi{
    private int $code = SUCCESS_CODE;
    private string $message = 'success';
    private $data;
    public $response;

    /**
     * ResponseApi constructor.
     * @param int $code
     * @param  $data
     */
    public function __construct($data, int $code = SUCCESS_CODE, string $message = ''){
        $this->code = $code;
        $this->data = json_encode($data);
        $this->message = !$message ? ($code === 200 ? 'success' : 'error') : $message;
        $this->response = $this->createDefaultJson();
    }


    private function createDefaultJson(){
        return json_decode('{"code":'.$this->code.',"message":"'.$this->message.'","data":'.$this->data.'}');
    }
}