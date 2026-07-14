<?php

class CfdiRenaming
{
    private function getFileData($file_path)
    {
        if (!file_exists($file_path)) {
            throw new Exception("El archivo no existe: {$file_path}");
        }
        if (!is_readable($file_path)) {
            throw new Exception("El archivo no se puede leer: {$file_path}");
        }
        return file_get_contents($file_path);
    }

    private function getDom($xmlContent)
    {
        $dom = new DOMDocument();

        libxml_use_internal_errors(true);
        $isValid = $dom->loadXML($xmlContent);
        libxml_use_internal_errors(false);

        if (!$isValid) {
            throw new Exception("El contenido no es un XML válido");
        }

        return $dom;
    }

    private function extractDataFromDom($dom)
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace("cfdi", "http://www.sat.gob.mx/cfd/4");

        return [
            'emisor' => $xpath->evaluate("string(//cfdi:Comprobante/cfdi:Emisor/@Nombre)"),
            'fecha' => $xpath->evaluate("string(//cfdi:Comprobante/@Fecha)"),
            'total' => $xpath->evaluate("string(//cfdi:Comprobante/@Total)"),
        ];
    }

    private function buildNewFileName($emisor, $fecha, $total)
    {
        $emisorLimpio = preg_replace('/\s+/', '_', trim($emisor));
        $emisorLimpio = preg_replace('/[\/\\\\:*?"<>|]/', '', $emisorLimpio);
        $fechaLimpia = str_replace(':', '-', $fecha);
        $montoLimpio = str_replace('.', '-', $total);

        return "{$emisorLimpio}__{$fechaLimpia}__{$montoLimpio}.xml";
    }

    private function extractFileData($file)
    {

        $xmlContent = $this->getFileData($file);
        $dom = $this->getDom($xmlContent);
        return $this->extractDataFromDom($dom);
    }

    public function processDirectory($dirPath, $outputDir = null)
    {
        $dirPath = rtrim($dirPath, '/');
        $xmlFiles = glob($dirPath . '/*.xml');

        if (empty($xmlFiles)) {
            throw new Exception("No se encontraron archivos XML en el directorio especificado: {$dirPath}");
        }

        foreach ($xmlFiles as $xmlFile) {
            $this->processFile($xmlFile, $outputDir);
        }

    }

    public function processFile($filePath, $outputDir = null)
    {
        try {
            $data = $this->extractFileData($filePath);
            $newName = $this->buildNewFileName($data['emisor'], $data['fecha'], $data['total']);
            $newPath = $this->copyWithNewName($filePath, $newName, $outputDir);
            echo "Procesado: {$filePath} -> {$newPath}\n";
        } catch (Exception $e) {
            echo "Error al procesar {$filePath}: " . $e->getMessage() . "\n";
        }
    }

    private function copyWithNewName($originalPath, $newFileName, $outputDir = null)
    {
        if ($outputDir && !is_dir($outputDir)) {
            throw new Exception("El directorio de salida no existe: {$outputDir}");
        }

        $directory = $outputDir ? rtrim($outputDir, '/') : dirname($originalPath);
        $destinationPath = "{$directory}/{$newFileName}";

        if ($originalPath === $destinationPath) {
            throw new Exception("Este archivo ya tiene el nombre deseado: {$originalPath}");
        }

        if (file_exists($destinationPath)) {
            throw new Exception("Ya existe un archivo con ese nombre en el destino: {$destinationPath}");
        }

        if (!copy($originalPath, $destinationPath)) {
            throw new Exception("Error al copiar el archivo de {$originalPath} a {$destinationPath}");
        }

        return $destinationPath;
    }


}