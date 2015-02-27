<?php
/**
 * Created by PhpStorm.
 * User: Cedric Wens
 * Date: 13/02/2015
 * Time: 18:08
 */

namespace AppBundle\Logic;


use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

class DefaultLogic {

    public function generatePassword(){
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $password = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $password[] = $alphabet[$n];
        }
        $password = implode($password);
        return $password;
    }


}