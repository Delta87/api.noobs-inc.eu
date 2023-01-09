<?php

class Utils
{
    /**
     * Check if this file runs on the commandline.
     * @return bool true/false for running on commandline.
     */
    public static function is_cli():bool
    {
        if( empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0)
        {
            return true;
        }
        return false;
    }

    /**
     * Remove first '.' and '/' for a clean include process.
     * Must start with '/' or './' ('./' for local directory, '/' for absolute path. Relative Path will not be supported
     * @param String $stringToCut the string to cut.
     * @return String the clean string
     * @throws Exception If string don't start with correct characters an exception will be thrown
     */
    public static function buildCleanString(string $stringToCut):string
    {
            if(self::startsWith($stringToCut, "./") || self::startsWith($stringToCut, "/"))
            {
                $returnVal = substr(strstr($stringToCut, "/"), strlen("/"));
                if (substr($returnVal, 0, 1) === "." || substr($returnVal, 0, 1) === "/") {
                    $returnVal = self::buildCleanString($returnVal);
                }
                return $returnVal;
            }else
            {
                throw new Exception(sprintf('Wrong start character for include-string<br>String: %s <br>Hint: Relative path will not be supported! Only local "./{path}" or absolut path "/{path}"</br>',$stringToCut));
            }
    }

    public static function startsWith ($string, $startString):bool
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    public static function send404(string $message= ""):void{
        http_response_code(404);
        die($message);
    }

    public static function isTokenValid(string $token, MySQLCon $db):bool
    {
        if($db->getNumRows("token",array("*"), "token", "s", array($token)) !== 1)
        {
            Utils::send404();
        }

        $data = $db->getMysqlArray("token", array("*"), "token", "s", array($token));

        if($data[0]['validUntil']<time())
        {
            Utils::send404();
            return false; //Useless but yeah.. to be correct..
        }
        else{
            $db->updateRow("token", array("validUntil" => self::getTimeInDays(14)), "token", $token);
            return true;
        }
    }

    /**
     * Get a unix timestamp in the future for given days
     * @param int $days The amount of days for calculation
     * @return int Timestamp in the future
     *
     */
    public static function getTimeInDays($days):int
    {
        return time() + (3600*24*$days);
    }
}