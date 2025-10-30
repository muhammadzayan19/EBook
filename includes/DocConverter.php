<?php
/**
 * Document Converter Library
 * Converts HTML content to various document formats
 */

class DocConverter {
    
    /**
     * Convert HTML content to RTF format (compatible with MS Word)
     * 
     * @param string $html HTML content
     * @param string $title Document title
     * @return string RTF formatted content
     */
    public static function htmlToRtf($html, $title = 'Document') {
        // Strip HTML tags and decode entities
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Convert special characters to RTF equivalents
        $text = str_replace(["\r\n", "\n", "\r"], "\\par\n", $text);
        
        // Build RTF document
        $rtf = "{\\rtf1\\ansi\\deff0\n";
        $rtf .= "{\\fonttbl{\\f0\\fswiss\\fcharset0 Arial;}{\\f1\\fmodern\\fcharset0 Courier New;}}\n";
        $rtf .= "{\\colortbl;\\red0\\green0\\blue0;\\red0\\green0\\blue255;}\n";
        $rtf .= "\\viewkind4\\uc1\\pard\\f0\\fs24\n";
        
        // Title
        $rtf .= "\\b\\fs32 " . self::escapeRtf($title) . "\\b0\\fs24\\par\n";
        $rtf .= "\\par\n";
        
        // Content
        $rtf .= self::escapeRtf($text);
        
        $rtf .= "\n}";
        
        return $rtf;
    }
    
    /**
     * Convert HTML content to simple DOC format
     * 
     * @param string $html HTML content
     * @param string $title Document title
     * @return string DOC formatted content
     */
    public static function htmlToDoc($html, $title = 'Document') {
        // Create simple HTML wrapper that Word can read
        $doc = "<!DOCTYPE html>\n";
        $doc .= "<html xmlns:o='urn:schemas-microsoft-com:office:office' ";
        $doc .= "xmlns:w='urn:schemas-microsoft-com:office:word' ";
        $doc .= "xmlns='http://www.w3.org/TR/REC-html40'>\n";
        $doc .= "<head>\n";
        $doc .= "<meta charset='utf-8'>\n";
        $doc .= "<title>" . htmlspecialchars($title) . "</title>\n";
        $doc .= "<style>\n";
        $doc .= "body { font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.6; }\n";
        $doc .= "h1 { font-size: 18pt; font-weight: bold; margin-bottom: 12pt; }\n";
        $doc .= "h2 { font-size: 16pt; font-weight: bold; margin-bottom: 10pt; }\n";
        $doc .= "h3 { font-size: 14pt; font-weight: bold; margin-bottom: 8pt; }\n";
        $doc .= "p { margin-bottom: 12pt; }\n";
        $doc .= "strong, b { font-weight: bold; }\n";
        $doc .= "em, i { font-style: italic; }\n";
        $doc .= "u { text-decoration: underline; }\n";
        $doc .= "</style>\n";
        $doc .= "</head>\n";
        $doc .= "<body>\n";
        $doc .= "<h1>" . htmlspecialchars($title) . "</h1>\n";
        $doc .= $html;
        $doc .= "</body>\n";
        $doc .= "</html>";
        
        return $doc;
    }
    
    /**
     * Save content as RTF file
     * 
     * @param string $content RTF content
     * @param string $filepath Full path where to save the file
     * @return bool Success status
     */
    public static function saveAsRtf($html, $filepath, $title = 'Document') {
        $rtf = self::htmlToRtf($html, $title);
        return file_put_contents($filepath, $rtf) !== false;
    }
    
    /**
     * Save content as DOC file
     * 
     * @param string $html HTML content
     * @param string $filepath Full path where to save the file
     * @param string $title Document title
     * @return bool Success status
     */
    public static function saveAsDoc($html, $filepath, $title = 'Document') {
        $doc = self::htmlToDoc($html, $title);
        return file_put_contents($filepath, $doc) !== false;
    }
    
    /**
     * Escape special characters for RTF
     * 
     * @param string $text Plain text
     * @return string Escaped text
     */
    private static function escapeRtf($text) {
        $text = str_replace("\\", "\\\\", $text);
        $text = str_replace("{", "\\{", $text);
        $text = str_replace("}", "\\}", $text);
        
        // Handle Unicode characters
        $escaped = '';
        $length = mb_strlen($text, 'UTF-8');
        
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            $code = self::uniord($char);
            
            if ($code > 127) {
                $escaped .= "\\u" . $code . "?";
            } else {
                $escaped .= $char;
            }
        }
        
        return $escaped;
    }
    
    /**
     * Get Unicode code point of a character
     * 
     * @param string $char Character
     * @return int Unicode code point
     */
    private static function uniord($char) {
        $k = mb_convert_encoding($char, 'UCS-2LE', 'UTF-8');
        $k1 = ord(substr($k, 0, 1));
        $k2 = ord(substr($k, 1, 1));
        return $k2 * 256 + $k1;
    }
    
    /**
     * Convert HTML content to PDF using TCPDF (if installed)
     * Note: Requires TCPDF library
     * 
     * @param string $html HTML content
     * @param string $filepath Output file path
     * @param string $title Document title
     * @return bool Success status
     */
    public static function htmlToPdf($html, $filepath, $title = 'Document') {
        if (!class_exists('TCPDF')) {
            return false;
        }
        
        try {
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            $pdf->SetCreator('E-Book Competition System');
            $pdf->SetAuthor('Competition System');
            $pdf->SetTitle($title);
            
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(TRUE, 15);
            
            $pdf->AddPage();
            
            $pdf->SetFont('helvetica', '', 12);
            $pdf->writeHTML($html, true, false, true, false, '');
            
            return $pdf->Output($filepath, 'F');
        } catch (Exception $e) {
            error_log("PDF conversion error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get word count from HTML content
     * 
     * @param string $html HTML content
     * @return int Word count
     */
    public static function getWordCount($html) {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim($text);
        
        if (empty($text)) {
            return 0;
        }
        
        $words = preg_split('/\s+/', $text);
        return count(array_filter($words));
    }
    
    /**
     * Get character count from HTML content
     * 
     * @param string $html HTML content
     * @param bool $includeSpaces Include spaces in count
     * @return int Character count
     */
    public static function getCharCount($html, $includeSpaces = true) {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        if (!$includeSpaces) {
            $text = str_replace(' ', '', $text);
        }
        
        return mb_strlen($text, 'UTF-8');
    }
    
    /**
     * Estimate reading time in minutes
     * 
     * @param string $html HTML content
     * @param int $wordsPerMinute Average reading speed (default: 200)
     * @return int Estimated reading time in minutes
     */
    public static function estimateReadingTime($html, $wordsPerMinute = 200) {
        $wordCount = self::getWordCount($html);
        return max(1, ceil($wordCount / $wordsPerMinute));
    }
}
?>