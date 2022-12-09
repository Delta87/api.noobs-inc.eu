<?php
class MySQLCon
{
    private $MYSQL_HOST = NULL;
    private $MYSQL_USER = NULL;
    private $MYSQK_PASS = NULL;
    private $MYSQL_BASE = NULL;

    private $MYSQL_CON = NULL;
    private $MYSQL_DATABSE = NULL;



    public function __construct($configfile)
    {
        $dbhost = $dbuser = $dbpass = $dbbase = NULL;

        if(Utils::is_cli()){
            require sprintf("%s/%s", getenv('PWD'), Utils::buildCleanString($configfile));
        }else{
            require sprintf("%s/%s", filter_input(INPUT_SERVER, 'DOCUMENT_ROOT'), Utils::buildCleanString($configfile));
        }

        $this->MYSQL_HOST = $dbhost;
        $this->MYSQL_USER = $dbuser;
        $this->MYSQK_PASS = $dbpass;
        $this->MYSQL_BASE = $dbbase;


        // Testing only!
        //$this->fields = array("twitchUsername" => "lusaca87", "twitchUserID" =>1687467, "APIToken" => "blablabla");
        //$this->insertInto("test" ,$this->fields );




    }
    public function printStatus()
    {
        //printf("Irgendeine Ausgabe die keinen Sinn ergibt im Moment");
    }

    /**
     * Get the mysqli connection or open a new one if needet.
     * @return false|mysqli the connection or false.
     */
    private function getMYSQLCon()
    {
        if($this->MYSQL_CON == NULL)
        {
            $this->MYSQL_CON = mysqli_connect($this->MYSQL_HOST, $this->MYSQL_USER, $this->MYSQK_PASS, $this->MYSQL_BASE);

        }
        return $this->MYSQL_CON;
    }

    /**
     * Insert data into a mysql table
     * @param $table MySQLCon The mysql-table where the data should be stored
     * @param $fields MySQLCon The data as array which match the table-structure!
     * @return boolean true/false for success after try to insert the data
     */
    public function insertInto($table, $fields)
    {
        $this->getMYSQLCon();

        $placeholders = array_fill(0, count($fields), '?');
        $keys   = array();
        $values = array();

        foreach($fields as $k => $v) {
            $keys[] = $k;
            $values[] = !empty($v) ? $v : null;
        }

        $stmt = $this->MYSQL_CON->prepare(sprintf("insert into %s(%s) values (%s)", $table, implode(', ', $keys), implode(', ', $placeholders)));

        $params = array();
        foreach ($fields as &$value) {
            $params[] = &$value;
        }
        $types  = array(str_repeat('s', count($params)));
        $values = array_merge($types, $params);

        call_user_func_array(array($stmt, 'bind_param'), $values);

        $success = $stmt->execute();
        return $success;
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
        return "";
    }

}