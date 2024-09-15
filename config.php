<?php
session_start();


header('Content-Type: application/javascript');

echo "const typeuser = " . json_encode($_SESSION['typeuser'] ?? null) . ";";
echo "const tables = " . json_encode($_SESSION['tables'] ?? []) . ";";
echo "const selectedEnterprise = " . json_encode($_SESSION['enterprise'] ?? null) . ";"; // Adiciona a empresa selecionada
?>
