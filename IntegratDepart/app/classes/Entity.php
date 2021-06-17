<?php
namespace App\Classes\Network\Entity;

class Entity {
    protected function parseClassName($fullClassName):String{
         $array = explode('\\', $fullClassName);
         return end($array);
    }
}
