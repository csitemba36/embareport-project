<?php
define('ODBC_DRIVER','Adaptive Server Anywhere 9.0');
define('ODBC_USER','dba');
define('ODBC_PASS','m3nDHol1968');

class db_odbc {
    protected $database;
    public $site;
    protected $error_msg;
    protected $odbc;

    public function __construct($database){
        $this->database = strtolower($database);
        $this->connect($this->database);

        switch($this->database){
            case 'emba_jeans':    $this->site = '102'; break;
            case 'emba_casual':   $this->site = '202'; break;
            case 'emba_ladies':   $this->site = '302'; break;
            case 'used_jeans':    $this->site = '402'; break;
            case 'emba_smartcsl': $this->site = '602'; break;
            case 'emba_morphidae':$this->site = '702'; break;
            case 'bbg_twist':     $this->site = '502'; break;
        }
    }

    public function getConnection(){
        return $this->odbc;
    }

    public function commit(){
        return odbc_commit($this->odbc);
    }

    public function connect($database){
        // default host
        $commlinks = 'tcpip(Host=192.168.0.181)';

        if($database=='emba_casual'){
            $commlinks = 'tcpip(Host=192.168.0.182)';
        }

        // coba konek pakai DSN dulu
        $this->odbc = @odbc_connect($database, ODBC_USER, ODBC_PASS);

        // kalau gagal, fallback ke DSN-less connection
        if(!$this->odbc){
            $dsn = "Driver=".ODBC_DRIVER.";CommLinks=".$commlinks.";ServerName=".$database.";DatabaseName=".$database;
            $this->odbc = @odbc_connect($dsn, ODBC_USER, ODBC_PASS);
        }

        if(!$this->odbc){
            $this->error_msg = odbc_error()." - ".odbc_errormsg();
            die("Koneksi ODBC gagal: ".$this->error_msg);
        }

        odbc_autocommit($this->odbc, false);
    }
}
