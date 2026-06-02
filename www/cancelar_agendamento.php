<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once("conexao.php");

if (!isset($_SESSION['cod_usuario'])) {
    http_response_code(401);
    echo json_encode(array('sucesso' => false, 'mensagem' => 'Sessao expirada. Faça login novamente.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('sucesso' => false, 'mensagem' => 'Metodo nao permitido.'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(array('sucesso' => false, 'mensagem' => 'ID invalido.'));
    exit;
}

$sql = "UPDATE agendamentos SET status = 'Cancelado' WHERE id = $id";
$ok = mysqli_query($conexao_bd, $sql);

if (!$ok) {
    http_response_code(500);
    echo json_encode(array('sucesso' => false, 'mensagem' => 'Erro ao cancelar o agendamento.'));
    exit;
}

if (mysqli_affected_rows($conexao_bd) === 0) {
    http_response_code(404);
    echo json_encode(array('sucesso' => false, 'mensagem' => 'Agendamento nao encontrado.'));
    exit;
}

echo json_encode(array('sucesso' => true));
