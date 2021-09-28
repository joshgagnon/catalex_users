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
        exec(implode(' ', ["DISPLAY=:0 QT_QPA_PLATFORM='offscreen' phantomjs", base_path('scripts/pdferize.js'), $htmlPath, $pdfPath]));

        return $pdfPath;
    }
}
