<?php
class discordAPI
{
    private string $discordToken;
    private MySQLCon $database;


    public function __construct(string $configfile, MySQLCon $db)
    {
        $token = NULL;
        if(Utils::is_cli()){
            try {
                require sprintf("%s/%s", getenv('PWD'), Utils::buildCleanString($configfile));
            } catch (Exception $ex)
            {
                die(sprintf("Configfile could not be found! <br> Error-Message: <b>%s</b>", $ex->getMessage()));
            }
        }else{
            try{
                require sprintf("%s/%s", filter_input(INPUT_SERVER, 'DOCUMENT_ROOT'), Utils::buildCleanString($configfile));
            }catch (Exception $ex)
            {
                die(sprintf("Configfile could not be found! <br> Error-Message: <b>%s</b>", $ex->getMessage()));
            }
        }

        $this->discordToken = $token;
        $this->database = $db;
    }

    private function generateLogin()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, 'DiscordBot (Noobs-Inc, 1)');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type" => "multipart/form-data", 'Authorization: Bot '.$this->discordToken));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_VERBOSE,        1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        return $ch;
    }

    public function ping(string $content):void
    {
        printf("Discord API call: %s", $content);
    }
    public function sendMessage($channelid, $message):string
    {
        $url=sprintf("https://discord.com/api/v6/channels/%s/messages", $channelid);
        $data = array('content' => $message);
        $ch = $this->generateLogin();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = $this->pCurl_exec($ch);

        if (curl_error($ch))
            echo curl_error($ch);

        curl_close($ch);
        return $response;
    }

    public function deleteDiscordMessage($channelID, $messageID):bool
    {

        sleep(10);
        $ch = $this->generateLogin();
        $url=sprintf("https://discord.com/api/channels/%s/messages/%s", $channelID, $messageID);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  "DELETE");
        $response = $this->pCurl_exec($ch);
        sleep(5);
        if (curl_error($ch))
        {
            $response = $response."----".curl_error($ch);
        }
        curl_close($ch);
        if(isset(json_decode($response, true)['code']))
        {
            return false;
        }
        return true;
    }

    private function pCurl_exec($ch)
    {
        if((time() - $this->database->getMysqlArray("settings", array("lastMessageTimer"))[0]['lastMessageTimer']) >= 5)
        {
            if($this->database->updateRow("settings", array("lastMessageTimer" => time()), "settingsID", "1"))
                return curl_exec($ch);
            else {
                sleep(1);
                return $this->pCurl_exec($ch);
            }
        }
        else
        {
            sleep(3);
            return $this->pCurl_exec($ch);
        }
    }


}