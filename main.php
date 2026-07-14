<?php

require 'CfdiRenaming.php';

if ($argc < 2) {
    echo "Uso: php main.php <archivo_o_carpeta_xml> [carpeta_de_salida]\n";
    echo "  archivo_o_carpeta_xml   Ruta de un CFDI (.xml) o de una carpeta con varios CFDIs\n";
    echo "  carpeta_de_salida       (Opcional) Carpeta donde guardar los archivos renombrados\n";
    exit(1);
}

$filePath = $argv[1];
$outputDir = $argv[2] ?? null;

if ($outputDir === null) {
    echo "No se especificó carpeta de salida, se usará la misma carpeta del archivo original.\n";
}

$cfdi = new CfdiRenaming();

if (is_file($filePath)) {
    $cfdi->processFile($filePath, $outputDir);
} elseif (is_dir($filePath)) {
    $cfdi->processDirectory($filePath, $outputDir);
} else {
    echo "Error: '{$filePath}' no es un archivo ni una carpeta válida.\n";
    exit(1);
}