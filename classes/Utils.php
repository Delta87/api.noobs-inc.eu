<?php

class Utils
{
    /**
     * Check if this file runs on the commandline.
     * @return bool true/false for running on commandline.
     */
    public static function is_cli()
    {
        if( empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0)
        {
            return true;
        }
        return false;
    }

    /**
     * Remove first '.' and '/' for a clean include proccess.
     * Must start with '/' or './'
     * @param String $stringToCut the string to cut.
     * @return String the clean string
     * @throws Exception If string dont start with correct characters an exception will be thrown
     */
    public static function buildCleanString($stringToCut){
            if(self::startsWith($stringToCut, "./") || self::startsWith($stringToCut, "/"))
            {
                $returnVal = substr(strstr($stringToCut, "/"), strlen("/"));
                if (substr($returnVal, 0, 1) === "." || substr($returnVal, 0, 1) === "/") {
                    $returnVal = self::buildCleanString($returnVal);
                }
                return $returnVal;
            }else
            {
                echo "GOTO WRONG???? ->".$stringToCut;
                throw new Exception('Wrong start character for includestring');
            }
    }

    public static function startsWith ($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }




}