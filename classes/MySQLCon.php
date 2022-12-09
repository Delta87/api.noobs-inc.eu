<?php
class MySQLCon extends mysqli
{
    private $MYSQL_HOST = NULL;
    private $MYSQL_USER = NULL;
    private $MYSQK_PASS = NULL;
    private $MYSQL_BASE = NULL;

    public function __construct($configfile)
    {
        if(Utils::is_cli()){
            require sprintf("%s/%s", getenv('PWD'), Utils::buildCleanString($configfile));
        }else{
            require sprintf("%s/%s", filter_input(INPUT_SERVER, 'DOCUMENT_ROOT'), Utils::buildCleanString($configfile));
        }
    }
    public function printStatus()
    {
        printf("Irgendeine Ausgabe die keinen Sinn ergibt im Moment");
    }

}