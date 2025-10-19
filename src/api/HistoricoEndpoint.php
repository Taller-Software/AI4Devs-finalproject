<?php
namespace App\Api;

use App\Controllers\HistoricoController;
use App\DTO\ResponseDTO;

class HistoricoEndpoint {
    private $controller;

    public function __construct() {
        $this->controller = new HistoricoController();
    }

    public function index(): ResponseDTO {
        return $this->controller->index();
    }
}
