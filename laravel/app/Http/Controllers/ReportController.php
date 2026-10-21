<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use ZipArchive;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index', ['products' => Product::with(['category', 'supplier'])->orderBy('name')->get(), 'topProducts' => StockMovement::selectRaw('product_id, SUM(quantity) total')->where('type', 'saida')->groupBy('product_id')->orderByDesc('total')->with('product')->limit(10)->get()]);
    }

    public function stockXlsx()
    {
        $products = Product::with(['category', 'supplier'])->orderBy('name')->get();
        $rows = [['Produto', 'Categoria', 'Fornecedor', 'Estoque', 'Mínimo', 'Situação']];
        foreach ($products as $product) {
            $rows[] = [$product->name, $product->category->name, $product->supplier?->name ?? '', $product->stock, $product->minimum_stock, $product->isLowStock() ? 'Atenção' : 'Normal'];
        }

        $xml = fn (string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
        $sheetRows = '';
        foreach ($rows as $rowIndex => $row) {
            $sheetRows .= '<row r="'.($rowIndex + 1).'">';
            foreach ($row as $columnIndex => $value) {
                $cell = chr(65 + $columnIndex).($rowIndex + 1);
                $sheetRows .= is_int($value)
                    ? '<c r="'.$cell.'"><v>'.$value.'</v></c>'
                    : '<c r="'.$cell.'" t="inlineStr"><is><t>'.$xml((string) $value).'</t></is></c>';
            }
            $sheetRows .= '</row>';
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'clubestoque_').'.xlsx';
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Estoque" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$sheetRows.'</sheetData></worksheet>');
        $zip->close();

        return response()->download($tempFile, 'estoque.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->deleteFileAfterSend(true);
    }
}
