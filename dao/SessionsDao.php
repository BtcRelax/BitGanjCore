<?php
namespace BtcRelax\Dao;

final class SessionsDao extends BaseDao{

    
    public function getSessions($myId)
    {
        $result = [];
        $query = "SELECT `sid`, `user_agent` , `netinfo`, `forced_expire`, `is_signed`, `lapsed_min` FROM `vwSessionsInfo`;";        
        foreach ($this->query($query) as $row) {
            if ($row['sid'] !== $myId ) {
                array_push($result, ["id"=> $row['sid'], "data" => [ $row['sid'] , $row['user_agent'] ,  $row['netinfo'] , $row['is_signed'],  $row['lapsed_min'] ] ]);
            }
        }
        return $result;
    }
    //put your code here
}
