<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MysqlConnection{
	// pdodb::instance();  싱글톤
	private $is_new = false;
	private $host = null;
	private $port = null;
	private $user = null;
	private $pass = null;
	private $source = null;
	private $is_UTF8 = true;
	private $conn = null;
	private $dirver = "dblib";

	private $stmt = null;
	private $query = null;
	private $params = null;

	private $isconn = false;

	private $cTransID;
	private $childTrans = array();

	private $error = false;
	private $message = "";

	public function __construct($server = null, $database = null, $auto_connect = true){

	    if(is_array($database)){
            $this->host = $database['host'];
            $this->user = $database['user'];
            $this->pass = $database['pass'];
            $this->port = 3306;
            $this->source = $database['source'];
        }else{
            $this->host = "localhost";
            $this->user = "mediablockchain";
            $this->pass = "aleldjqmffhrcpdls#@!";
            $this->port = 3306;
            $this->source = "mediablockchain";
        }
		if($auto_connect){
			$this->conn = $this->connect();
		}else{
			$this->error = true;
			$this->message = "not run connect()\n";
		}
	}

	public function connect($is_new = false){
		$this->error = false;
		$this->message = "";

		if(!$this->conn || $is_new){
			try{
				if($this->is_UTF8 === true){
					$this->conn = new PDO("mysql:host=".$this->host.":".$this->port.";dbname=".$this->source,$this->user,$this->pass);
					$this->conn->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND,'SET NAMES UTF8');
				}else{
					$this->conn = new PDO("mysql:host=".$this->host.":".$this->port.";dbname=".$this->source,$this->user,$this->pass);
				}

				$this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}catch (PDOException $e){
				$this->error = true;
				$this->message = "Failed to get DB handle: " . $e->getMessage() . "\n";
			}

			if($this->conn){
				$this->isconn = true;
			}else{
				$this->isconn = false;
			}
		}
		return $this->conn;
	}

	public function isConnect(){
		return $this->isconn;
	}

	public function close(){
		$this->conn = null;
	}

	public function free_stmt($stmt){
		$stmt = null;
	}

	public function isError(){
		return $this->error;
	}

	public function getError(){
		return $this->message;
	}

	public function error(){
		$this->error = true;
		$this->message = "";
	}

	public function prepare($query, $params = null){
		$this->query = $query;
		$this->params = $params;

		try{
			$this->stmt = $this->conn->prepare($this->query, $this->params);
			$i=0;
			foreach($this->params as $param){
				if(count($param) == 2){
					$this->stmt->bindValue($i, $param[0], $param[1]);
					$i++;
				}else{
					$this->stmt->bindValue($param);
				}
			}
			//$this->stmt->execute();
		}catch (PDOException $e){
			$this->error = true;
			$this->message = "Failed to get DB handle: " . $e->getMessage() . "\n";
		}

		return $this->stmt;
	}

	public function cancel(){
		$this->stmt = null;
	}

	private function execute(){
		$this->stmt->execute();
	}

    public function query($query, $params = null, $options = null){
        if(!$this->isConnect()){
            return null;
        }
        $this->query = $query;
        $this->params = $params;

        try{
            $is_execute_params = false;
            $this->stmt = $this->conn->prepare($this->query);
            $i=1;
            if(is_array($this->params)){
                foreach($this->params as $key => $param){

                    if(count($param) >= 5){
                        throw new Exception('파라메터 갯수가 너무 많습니다.');
                    }elseif(count($param) == 4){
                        $this->stmt->bindValue($param[0], $param[1], $param[2], $param[3]);
                    }elseif(count($param) == 3){
                        $this->stmt->bindValue($param[0], $param[1], $param[2]);
                    }elseif(count($param) == 2){
                        $this->stmt->bindValue($i, $param[0], $param[1]);
                        $i++;
                    }elseif(!is_array($param)){
                        $is_execute_params = true;
                    }
                }


            }

            if(isset($options)){
                $this->sql_debug($this->query , $this->params);
            }

            if($is_execute_params){
                $this->stmt->execute($params);
            }else{
                $this->stmt->execute();
            }
        }catch (PDOException $e){
            $this->error = true;
            $this->message = "Failed to get DB handle: " . $e->getMessage() . "\n";
        }
        return $this->stmt;
    }

	public function nextResult(){
		return $this->stmt->nextRowset();
	}

	public function insertId(){
		return $this->conn->lastInsertId();
	}

	public function fetch($mode = 'object'){
		if(isset($this->stmt)){
			try{
				switch($mode){
					case 'array':
						return $this->stmt->fetch(PDO::FETCH_ASSOC);
						break;
					case 'object':
						return $this->stmt->fetch(PDO::FETCH_OBJ);
						break;
				}
			}catch (PDOException $e){
				$this->error = true;
				$this->message = "Failed to get DB handle: " . $e->getMessage() . "\n";
			}
		}

		return null;
	}

	public function fetchAll($mode = 'object'){
		if(isset($this->stmt)){
			try{
				switch($mode){
					case 'array':
						return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
						break;
					case 'object':
						return $this->stmt->fetchAll(PDO::FETCH_OBJ);
						break;
				}
			}catch (PDOException $e){
				$this->error = true;
				$this->message = "Failed to get DB handle: " . $e->getMessage() . "\n";
			}
		}
		return null;
	}

	public function begin_transaction(){
		$this->conn->beginTransaction();
	}

	public function commit(){
		$this->conn->commit();
	}

	public function rollback(){
		$this->conn->rollback();
	}

	public function is_commit($arr){
		$cnt = count($arr);
		foreach($arr as $val) if(!$val) $cnt--;
		return $cnt==0 ? 1 : 0;
	}

    private function sql_debug($sql_string, array $params = null)
    {
        if (!empty($params)) {
            $indexed = $params == array_values($params);
            foreach ($params as $k => $v) {
                if (is_object($v)) {
                    if ($v instanceof \DateTime) $v = $v->format('Y-m-d H:i:s');
                    else continue;
                } elseif (is_string($v)) $v = "'$v'";
                elseif ($v === null) $v = 'NULL';
                elseif (is_array($v)) $v = implode(',', $v);

                if ($indexed) {
                    $sql_string = preg_replace('/\?/', $v, $sql_string, 1);
                } else {
                    if ($k[0] != ':') $k = ':' . $k; //add leading colon if it was left out
                    $sql_string = str_replace($k, $v, $sql_string);
                }
            }
        }
        echo  "<br>------------------------------<br><strong>" .$sql_string ."</strong><br>--------------------------------<br>";
    }
}
?>