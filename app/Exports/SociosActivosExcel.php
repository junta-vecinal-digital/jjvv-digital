<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use ZipArchive;

/** XLSX con celdas de texto: conserva el RUT y evita interpretar nombres como fórmulas. */
class SociosActivosExcel
{
    public static function descargar(Collection $socios)
    {
        abort_unless(class_exists(ZipArchive::class), 503, 'La exportación Excel requiere habilitar la extensión ZIP de PHP.');
        $filas = [['Nombre', 'RUT', 'Estado', 'Fecha de ingreso']];
        foreach ($socios as $socio) $filas[] = [$socio->nombre_completo, $socio->rut, 'Activo', $socio->fecha_ingreso?->format('d/m/Y') ?? ''];
        $hoja = '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><cols><col min="1" max="1" width="38" customWidth="1"/><col min="2" max="4" width="20" customWidth="1"/></cols><sheetData>';
        foreach ($filas as $indice => $fila) {
            $numero = $indice + 1;
            $hoja .= '<row r="'.$numero.'">';
            foreach ($fila as $columna => $valor) {
                $texto = htmlspecialchars((string) $valor, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED, 'UTF-8');
                $hoja .= '<c r="'.chr(65 + $columna).$numero.'" t="inlineStr"><is><t xml:space="preserve">'.$texto.'</t></is></c>';
            }
            $hoja .= '</row>';
        }
        $hoja .= '</sheetData></worksheet>';
        $archivos = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Socios activos" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>',
            'xl/worksheets/sheet1.xml' => $hoja,
        ];
        $ruta = tempnam(sys_get_temp_dir(), 'socios-');
        if ($ruta === false) throw new \RuntimeException('No se pudo preparar el archivo Excel.');
        $zip = new ZipArchive;
        try {
            if ($zip->open($ruta, ZipArchive::OVERWRITE) !== true) throw new \RuntimeException('No se pudo crear el archivo Excel.');
            foreach ($archivos as $nombre => $contenido) $zip->addFromString($nombre, $contenido);
            if (! $zip->close()) throw new \RuntimeException('No se pudo completar el archivo Excel.');
            clearstatcache(true, $ruta);
            return response()->download($ruta, 'socios-activos-'.now()->format('Y-m-d').'.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'private, no-store',
            ])->setPrivate()->deleteFileAfterSend(true);
        } catch (\Throwable $error) {
            if (is_file($ruta)) unlink($ruta);
            throw $error;
        }
    }
}
