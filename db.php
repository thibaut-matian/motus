<?php
class Database {
    private $host = "localhost";
    private $db_name = "motus";
    private $username = "root";
    private $password = "";
    public $conn; // Déclarée strictement au niveau de la classe

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "message" => "Erreur BDD : " . $exception->getMessage()]);
            exit();
        }
        return $this->conn;
    }
}
?>