<?php

function dbConnection(){
   $mysql_connect_str = "mysql:host=".HOST.";dbname=".DB_NAME;
   $connection = new PDO($mysql_connect_str, DB_USER, DB_PASS);
   $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   return $connection;
}

function luhnAlgorithm($num)
{
    $number = strrev(preg_replace('/[^\d]+/', '', $num));
    $sum = 0;
    for ($i = 0, $j = strlen($number); $i < $j; $i++) {
        if (($i % 2) == 0) {
            $val = $number[$i];
        } else {
            $val = $number[$i] * 2;
            if ($val > 9)  {
                $val -= 9;
            }
        }
        $sum += $val;
    }
    return (($sum % 10) === 0);
}


