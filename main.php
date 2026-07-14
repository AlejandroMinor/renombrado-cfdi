<?php

require 'CfdiRenaming.php';

if ($argc < 2) {
    echo "Uso: php main.php <ruta_carpeta_cfdis>\n";
    exit(1);
}

$filePath = $argv[1];

if (!is_dir($filePath)) {
    echo "Error: '{$filePath}' no es una carpeta válida.\n";
    exit(1);
}

$cfdi = new CfdiRenaming();
$cfdi->processDirectory($filePath);