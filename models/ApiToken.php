<?php

class ApiToken{
    private $conn;
    private $table_name = "api_tokens";

    public $id;
    public $user_id;
    public $token;
    public $expires_at;
    public $revoked;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

public function create() 
{
    $query = "INSERT INTO " . $this->table_name . " 
              SET user_id=:user_id, token=:token, expires_at=:expires_at, revoked=:revoked, created_at=:created_at";

    $stmt = $this->conn->prepare($query);

    // user_id y revoked pueden sanearse (vienen de tu app)
    $this->user_id = (int)($this->user_id);
    $this->revoked = (int)($this->revoked ?? 0);

    // token y expires_at NO se sanitizan
    $this->created_at = date('Y-m-d H:i:s');

    $stmt->bindParam(":user_id", $this->user_id);
    $stmt->bindParam(":token", $this->token);
    $stmt->bindParam(":expires_at", $this->expires_at);
    $stmt->bindParam(":revoked", $this->revoked);
    $stmt->bindParam(":created_at", $this->created_at);

    if ($stmt->execute()) {
        $this->id = $this->conn->lastInsertId();
        return [
            'token'      => $this->token,
            'expires_at' => $this->expires_at
        ];
    }
    return false;
}

public function findValidToken(string $token)
{
    $query = "SELECT user_id, expires_at 
              FROM " . $this->table_name . "
              WHERE token = :token
                AND revoked = 0
                AND expires_at > NOW()
              LIMIT 1";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}


    // public function create()
    // {
    //     $query = "INSERT INTO " . $this->table_name . " 
    //               SET user_id=:user_id, token=:token, expires_at=:expires_at, revoked=:revoked, created_at=:created_at";

    //     $stmt = $this->conn->prepare($query);

    //     $this->user_id = htmlspecialchars(strip_tags($this->user_id));
    //     $this->token = ($this->token);
    //     $this->expires_at = htmlspecialchars(strip_tags($this->expires_at));
    //     $this->revoked = htmlspecialchars(strip_tags($this->revoked ?? 0));
    //     $this->created_at = date('Y-m-d H:i:s');

    //     $stmt->bindParam(":user_id", $this->user_id);
    //     $stmt->bindParam(":token", $this->token);
    //     $stmt->bindParam(":expires_at", $this->expires_at);
    //     $stmt->bindParam(":revoked", $this->revoked);
    //     $stmt->bindParam(":created_at", $this->created_at);

    //     if ($stmt->execute()) {
    //         $this->id = $this->conn->lastInsertId();
    //         return true;
    //     }
    //     return false;
    // }

    public function read()
    {
        $query = "SELECT id, user_id, token, expires_at, revoked, created_at 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne()
    {
        $query = "SELECT id, user_id, token, expires_at, revoked, created_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->user_id = $row['user_id'];
            $this->token = $row['token'];
            $this->expires_at = $row['expires_at'];
            $this->revoked = $row['revoked'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET expires_at=:expires_at, revoked=:revoked 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->expires_at = htmlspecialchars(strip_tags($this->expires_at));
        $this->revoked = htmlspecialchars(strip_tags($this->revoked));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":expires_at", $this->expires_at);
        $stmt->bindParam(":revoked", $this->revoked);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
