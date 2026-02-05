<?php
require_once 'includes/config.php';

// Alternar tema
if ($_SESSION['tema'] === 'claro') {
    $_SESSION['tema'] = 'escuro';
} else {
    $_SESSION['tema'] = 'claro';
}

// Redirecionar de volta para a página anterior
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
?>