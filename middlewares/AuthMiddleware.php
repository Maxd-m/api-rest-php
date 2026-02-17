<?php

class AuthMiddleware
{
    private $api_token;

    public function __construct($api_token)
    {
        $this->api_token = $api_token;
    }

    public function handle()
    {
        $headers = getallheaders();

        if (empty($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(["message" => "Token no enviado"]);
            exit;
        }

        if (!preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            http_response_code(401);
            echo json_encode(["message" => "Formato de Authorization inválido"]);
            exit;
        }

        $token = $matches[1];

        $tokenData = $this->api_token->findValidToken($token);

        if (!$tokenData) {
            http_response_code(401);
            echo json_encode(["message" => "Token inválido o expirado"]);
            exit;
        }

        // Inyectar usuario autenticado en el contexto global
        $_REQUEST['auth_user_id'] = $tokenData['user_id'];
    }
}
