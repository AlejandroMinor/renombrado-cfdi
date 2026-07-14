<?php

require 'CfdiRenaming.php';

if ($argc < 2) {
    echo "Uso: php main.php <ruta_del_archivo_o_directorio>\n";
    exit(1);
}

$filePath = $argv[1];
$cfdi = new CfdiRenaming();

if (is_file($filePath)) {
    $cfdi->processFile($filePath);
} elseif (is_dir($filePath)) {
    $cfdi->processDirectory($filePath);
} else {
    echo "Error: '{$filePath}' no es un archivo ni una carpeta válida.\n";
    exit(1);
}