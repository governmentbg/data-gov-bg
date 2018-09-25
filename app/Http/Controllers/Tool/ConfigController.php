<?php

namespace App\Http\Controllers\Tool;

use Illuminate\Http\Request;
use App\Http\Controllers\ToolController;

class ConfigController extends ToolController
{
    public function index()
    {
        return view('tool/configControl', ['class' => 'index']);
    }

    public function create(Request $request)
    {
        $result = false;
        $inputs = [];
        $errors = [];
        $foundData = [];

        $inputs = $request->all();

        if ($request->has('test')) {
            $username = $inputs['source_db_user'];
            $serverName = $inputs['source_db_host'];
            $dbName = $inputs['source_db_name'];
            $password = $inputs['source_db_pass'];
            $query = $inputs['query'];

            if ($driver = $this->testConnections($serverName, $dbName, $username, $password ? $password : false)) {
                $result = 'Connection worked!';

                if (!empty($query)) {
                    $foundData = $this->fetchData($query, $driver, $serverName, $dbName, $username, $password ? $password : false);
                }
            } else {
                $result = 'Connection failed';
            }
        }

        return view('config', compact('inputs', 'result', 'foundData'));
    }

    // $conn = new \PDO("mysql:host=$servername;dbname=$dbname", $username, $password); MySQL connection;
    // $conn = new \PDO("pgsql:host=$servername;dbname=$dbname", $username, $password); PostgreSQL connection;
    // $conn = new \PDO("oci:host=$servername;dbname=$dbname", $username, $password); Oracle connection;
    // $conn = new \PDO("informix:host=$servername;dbname=$dbname", $username, $password); Informix connection;
    // $conn = new \PDO("sqlsrv:host=$servername;dbname=$dbname", $username, $password); MS SQL connection;

    private function testConnections($servername, $dbname, $username, $password)
    {
        $drivers = ['pgsql', 'oci', 'informix', 'sqlsrv', 'mysql'];

        foreach ($drivers as $driver) {
            if ($this->checkConnection($driver, $servername, $dbname, $username, $password)) {
                return $driver;
            } else {
                error_log('driver failed ' .print_r($driver, true));
                continue;
            }
        }

        return false;
    }

    private function checkConnection($driver, $servername, $dbname, $username, $password = false)
    {
        try {
            $conn = new \PDO("$driver:host=$servername;dbname=$dbname", $username, $password ? $password : null); // add array PDO::ATTR_PERSISTENT => true for persistent connection
            // set the PDO error mode to exception
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            return true;
        } catch(\PDOException $e) {
            // $errors[$driver] = "Connection failed: " . $e->getMessage();
            return false;
        }
    }

    public function fetchData($query, $driver, $servername, $dbname, $username, $password = false)
    {
        try {
            $conn = new \PDO("$driver:host=$servername;dbname=$dbname", $username, $password ? $password : null); // add array PDO::ATTR_PERSISTENT => true for persistent connection
            // set the PDO error mode to exception
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $stmt = $conn->prepare($query);
            $stmt->execute();

            // set the resulting array to associative
            $result = $stmt->setFetchMode(\PDO::FETCH_ASSOC);

            return $stmt->fetchAll();
        } catch(\PDOException $e) {
            // $errors[$driver] = "Connection failed: " . $e->getMessage();
            return false;
        }

        return false;
    }
}
