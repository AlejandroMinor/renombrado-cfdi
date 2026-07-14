<?php

class CfdiRenaming
{
    public function getFileData($file_path){
        if (!file_exists($file_path)) {
            throw new Exception("El archivo no existe: {$file_path}");
        }
        return file_get_contents($file_path);
    }

    public function showFileData($file_path){
        $data = $this->getFileData($file_path);
        echo $data;
    }

    public function getDom($xmlContent){
        $dom = new DOMDocument();
        $dom->loadXML($xmlContent);
        return $dom;
    }

    public function extractDataFromDom($dom)
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace("cfdi", "http://www.sat.gob.mx/cfd/4");

        return [
            'emisor' => $xpath->evaluate("string(//cfdi:Comprobante/cfdi:Emisor/@Nombre)"),
            'fecha'  => $xpath->evaluate("string(//cfdi:Comprobante/@Fecha)"),
            'total'  => $xpath->evaluate("string(//cfdi:Comprobante/@Total)"),
        ];
    }

    public function buildNewFileName($emisor, $fecha, $total)
    {
        $emisorLimpio = preg_replace('/\s+/', '_', trim($emisor));
        $fechaLimpia = str_replace(':', '-', $fecha);
        $montoLimpio = str_replace('.', '-', $total);

        return "{$emisorLimpio}__{$fechaLimpia}__{$montoLimpio}.xml";
    }

    public function extractFileData($file){
        
        $xmlContent = $this->getFileData($file);
        $dom = $this->getDom($xmlContent);
        return $this->extractDataFromDom($dom);
    }

    public function processDirectory($dirPath){
        $dirPath = rtrim($dirPath, '/');
        $xmlFiles = glob($dirPath . '/*.xml');
        
        if (empty($xmlFiles)){
            throw new Exception("No se encontraron archivos XML en el directorio especificado: {$dirPath}");
        }
        
        foreach ($xmlFiles as $xmlFile){
            try {
                $data = $this->extractFileData($xmlFile);
                $newName = $this->buildNewFileName($data['emisor'], $data['fecha'], $data['total']);
                $newPath = $this->copyWithNewName($xmlFile, $newName);
                echo "Procesado: {$xmlFile} -> {$newPath}\n";
            } catch (Exception $e) {
                echo "Error al procesar {$xmlFile}: " . $e->getMessage() . "\n";
            }
        }        

    }

    public function copyWithNewName($originalPath, $newFileName){
        $directory = dirname($originalPath);
        $destinationPath = $directory . '/' . $newFileName;

        if ($originalPath === $destinationPath) {
            throw new Exception("Este archivo ya tiene el nombre deseado: {$originalPath}");
        }

        if (!copy($originalPath, $destinationPath)) {
            throw new Exception("Error al copiar el archivo de {$originalPath} a {$destinationPath}");
        }

        return $destinationPath;
    }
    
        
}