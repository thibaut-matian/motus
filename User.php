<?php
class User {
    private $db;
    private $table = "users";

    public function __construct($database) {
        $this->db = $database;
    }

    // Inscription d'un utilisateur
    public function register($username, $password) {
        
        $query = "SELECT id FROM " . $this->table . " WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return ["success" => false, "message" => "Ce pseudo est déjà pris."];
        }

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $query = "INSERT INTO " . $this->table . " (username, password) VALUES (:username, :password)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $hashed_password);

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Inscription réussie !"];
        }
        return ["success" => false, "message" => "Une erreur est survenue."];
    }

    
    public function login($username, $password) {
        $query = "SELECT id, username, password FROM " . $this->table . " WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $row['password'])) {
                session_start();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                return ["success" => true, "message" => "Connexion réussie."];
            }
        }
        return ["success" => false, "message" => "Pseudo ou mot de passe incorrect."];
    }
}
?>