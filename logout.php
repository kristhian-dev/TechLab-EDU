<?php

require __DIR__ . '/configuracoes/app.php';
require __DIR__ . '/nucleo/Seguranca.php';
require __DIR__ . '/controllers/AuthController.php';

AuthController::logout();
