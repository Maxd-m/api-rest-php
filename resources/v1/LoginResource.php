<?php

require_once '../config/database.php';
require_once '../models/ApiUser.php';
require_once '../models/ApiToken.php';

class LoginResource
{
    private $db;
    private $api_user;
    private $api_token;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->api_user = new ApiUser($this->db);
        $this->api_token = new ApiToken($this->db);
    }

    // POST /api/v1/login
    public function login()
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->username) && !empty($data->password)) {

            // echo password_hash('Admin123!', PASSWORD_DEFAULT);

            $userId = $this->api_user->login($data->username, $data->password);
            

            if ($userId !== false) {
                http_response_code(200);
                $this->api_token->user_id = $userId;
                $this->api_token->token = bin2hex(random_bytes(126)); // 252 caracteres hexadecimales
                $this->api_token->expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
                $this->api_token->revoked = 0;


                $response = $this->api_token->create();

                if ($response) {
                    echo json_encode(array(
                        "message" => "Usuario validado y token creado exitosamente",
                        "access_token" => $response['token'],
                        "expires_at" => $response['expires_at']
                        // "user_id" => $userId
                    ));
                } else {
                    http_response_code(500);
                    echo json_encode(array("message" => "Error al crear el token"));
                }

            } else {
                http_response_code(401);
                echo json_encode(array("message" => "Credenciales inválidas"));
            }

        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos"));
        }
    }

}
?>