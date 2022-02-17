<?php

namespace App\Library;

class PhantomJS
{
    public static function htmlToPdf($html)
    {
        $baseName = tempnam(base_path('storage/tmp'), 'invoice');
        $htmlPath = $baseName . '.html';
        $handle = fopen($htmlPath, 'w');

        fwrite($handle, $html);
        fclose($handle);

        $pdfPath = $baseName . '.pdf';
        exec(implode(' ', ["DISPLAY=:0 phantomjs", base_path('scripts/pdferize.js'), $htmlPath, $pdfPath]));

        return $pdfPath;
    }
}
