<?php
/**
 * PDF Report Generator
 * Requires TCPDF library
 * Install via: composer require tecnickcom/tcpdf
 * Or download from: https://github.com/tecnickcom/TCPDF
 */

class PDFReportGenerator {
    private $pdf;
    private $title;
    
    public function __construct($title = 'Отчёт') {
        $this->title = $title;
        $this->initPDF();
    }
    
    private function initPDF() {
        // Check if TCPDF class already exists
        if (!class_exists('TCPDF')) {
            // Try to use TCPDF if available via Composer
            if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
                require_once __DIR__ . '/../vendor/autoload.php';
            }
            // Or use TCPDF from includes folder
            elseif (file_exists(__DIR__ . '/tcpdf/tcpdf.php')) {
                require_once __DIR__ . '/tcpdf/tcpdf.php';
            }
            // Fallback: try to find TCPDF in common locations
            else {
                $tcpdf_paths = [
                    __DIR__ . '/../tcpdf/tcpdf.php',
                    __DIR__ . '/../../tcpdf/tcpdf.php',
                ];
                $found = false;
                foreach ($tcpdf_paths as $path) {
                    if (file_exists($path)) {
                        require_once $path;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    die('TCPDF library not found. Please install it via Composer: composer require tecnickcom/tcpdf<br>Or see INSTALL_PDF.md for manual installation instructions.');
                }
            }
        }
        
        // Check if TCPDF class is now available
        if (!class_exists('TCPDF')) {
            die('TCPDF class not found after loading. Please check your installation.');
        }
        
        // Define constants if not defined
        if (!defined('PDF_PAGE_ORIENTATION')) {
            define('PDF_PAGE_ORIENTATION', 'P'); // Portrait
        }
        if (!defined('PDF_UNIT')) {
            define('PDF_UNIT', 'mm');
        }
        if (!defined('PDF_PAGE_FORMAT')) {
            define('PDF_PAGE_FORMAT', 'A4');
        }
        
        // Create PDF instance
        $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $this->pdf->SetCreator('DBGranit System');
        $this->pdf->SetAuthor('DBGranit');
        $this->pdf->SetTitle($this->title);
        $this->pdf->SetSubject($this->title);
        
        // Remove default header/footer
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        
        // Set default monospaced font
        $this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        // Set margins
        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetHeaderMargin(0);
        $this->pdf->SetFooterMargin(0);
        
        // Set auto page breaks
        $this->pdf->SetAutoPageBreak(TRUE, 15);
        
        // Set image scale
        $this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // Set font
        $this->pdf->SetFont('dejavusans', '', 10);
        
        // Add a page
        $this->pdf->AddPage();
    }
    
    public function addTitle($text) {
        $this->pdf->SetFont('dejavusans', 'B', 16);
        $this->pdf->Cell(0, 10, $text, 0, 1, 'C');
        $this->pdf->Ln(5);
    }
    
    public function addSubtitle($text) {
        $this->pdf->SetFont('dejavusans', 'B', 12);
        $this->pdf->Cell(0, 8, $text, 0, 1, 'L');
        $this->pdf->Ln(2);
    }
    
    public function addInfo($label, $value) {
        $this->pdf->SetFont('dejavusans', 'B', 10);
        $this->pdf->Cell(50, 6, $label . ':', 0, 0, 'L');
        $this->pdf->SetFont('dejavusans', '', 10);
        $this->pdf->Cell(0, 6, $value, 0, 1, 'L');
    }
    
    public function addTable($headers, $data, $colWidths = null) {
        // Calculate column widths if not provided
        if ($colWidths === null) {
            $colCount = count($headers);
            $totalWidth = 180; // A4 width minus margins
            $colWidth = $totalWidth / $colCount;
            $colWidths = array_fill(0, $colCount, $colWidth);
        }
        
        // Table header
        $this->pdf->SetFont('dejavusans', 'B', 10);
        $this->pdf->SetFillColor(230, 230, 230);
        foreach ($headers as $i => $header) {
            $this->pdf->Cell($colWidths[$i], 7, $header, 1, 0, 'C', true);
        }
        $this->pdf->Ln();
        
        // Table data
        $this->pdf->SetFont('dejavusans', '', 9);
        $this->pdf->SetFillColor(255, 255, 255);
        $fill = false;
        
        foreach ($data as $row) {
            foreach ($row as $i => $cell) {
                $this->pdf->Cell($colWidths[$i], 6, $this->cleanText($cell), 1, 0, 'L', $fill);
            }
            $this->pdf->Ln();
            $fill = !$fill;
        }
        
        $this->pdf->Ln(5);
    }
    
    public function addText($text, $align = 'L') {
        $this->pdf->SetFont('dejavusans', '', 10);
        $this->pdf->MultiCell(0, 5, $text, 0, $align);
        $this->pdf->Ln(2);
    }
    
    public function addLine() {
        $this->pdf->Line(15, $this->pdf->GetY(), 195, $this->pdf->GetY());
        $this->pdf->Ln(3);
    }
    
    private function cleanText($text) {
        // Remove HTML tags and decode entities
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        // Truncate long text
        if (mb_strlen($text) > 50) {
            $text = mb_substr($text, 0, 47) . '...';
        }
        return $text;
    }
    
    public function output($filename = 'report.pdf', $dest = 'I') {
        // I = send to browser
        // D = download
        // F = save to file
        $this->pdf->Output($filename, $dest);
    }
    
    public function getPDF() {
        return $this->pdf;
    }
}
