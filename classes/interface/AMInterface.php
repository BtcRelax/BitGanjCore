<?php

namespace BtcRelax;

interface IAM
{
    //put your code here
    public function CreateNewUser();

    public function SignIn(\BtcRelax\Model\Identicator $pIdent);
    
    public function getUserById($pId);
    
    public function loginUserByToken($token);
}
