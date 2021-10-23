<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


//require_once __DIR__ . '/session_verify.php';
require_once __DIR__ . '/config.php';
require_once __DIR__.'/../query_helper.php';

/**
 * Created by PhpStorm.
 * User: Mac
 * Date: 15/08/2020
 * Time: 9:27 AM
 */
class DB
{

    public $host = null;
    public $user = 'root';
    public $pass = 'root';
    public $db = 'appsdev';
    public $port = '8889';
    public $purchase_code = null;
    private static $_instance = null;
    private $_connection = null;


    private static function getInstance()
    {

        if (is_null(self::$_instance)) {

            self::$_instance = new self(new DB_KEYS());

        }
        return self::$_instance;

    }


    private function __construct(DB_KEYS $DB_KEYS)
    {

        $this->host = $DB_KEYS::HOST;
        $this->db = $DB_KEYS::DATABASE_NAME;
        $this->user = $DB_KEYS::DATABASE_USER_NAME;
        $this->pass = $DB_KEYS::DATABASE_PASSWORD;
        $this->port = $DB_KEYS::PORT;

        $this->_connection = $this->connect_now();

    }


    private function connect_now()
    {
        try {

            $link = null;


            if (!($link = mysqli_connect($this->host, $this->user, $this->pass, $this->db, $this->port))) {
                echo "Error: Unable to connect to MySQL." . PHP_EOL;
                echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
                echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
                exit;
            }


            //echo "Success: A proper connection to MySQL was made! The my_db database is great." . PHP_EOL;
            //echo "Host information: " . mysqli_get_host_info($link) . PHP_EOL;

            return $link;


        } catch (Exception $exception) {

            var_dump($exception->getMessage());

        }

    }


    public function getCon()
    {

        return $this->_connection;
    }


    public static function getConnection()
    {

        return self::getInstance()->getCon();


    }


    public static function run_mysql_query($qry, $info = __FILE__ . ' ' . __LINE__)
    {

        $res = mysqli_query(self::getConnection(),
            $qry) or die(mysqli_error(self::getConnection()) . ' Error Info: (FileName/LineNumber) === ' . $info);

        if( stristr($qry, 'insert') !== false) {
            return mysqli_insert_id(self::getConnection());
        }

        return $res;

    }


}


/*$res = DB::run_mysql_query('select * from metas', __FILE__);


if($res->num_rows > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        echo '<br>';
        echo $row['title'];
    }
}*/


/*
function get_results($qry) {

    $con = DB::getConnection();

    $res = mysqli_query($con, 'select * from metas') or die(mysqli_error($con));

    print_r($res);

}


get_results('a');*/


function sanitize($data)
{
    return $data = stripslashes(trim(htmlspecialchars($data)));
}

