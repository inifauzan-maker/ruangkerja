<?php

namespace App\Support;

class SimplePdfReport
{
    /**
     * @param  array<int, string>  $lines
     */
    public function make(array $lines): string
    {
        $commands = ['BT', '/F1 11 Tf', '48 790 Td', '14 TL'];

        foreach (array_slice($this->wrapLines($lines), 0, 50) as $line) {
            $commands[] = '('.$this->escape($line).') Tj';
            $commands[] = 'T*';
        }
        $commands[] = 'ET';

        $stream = implode("\n", $commands);
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            '<< /Length '.strlen($stream)." >>\nstream\n".$stream."\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n".$object."\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF";
    }

    /**
     * @param  array<int, string>  $lines
     * @return array<int, string>
     */
    private function wrapLines(array $lines): array
    {
        $wrapped = [];
        foreach ($lines as $line) {
            $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $line) ?: $line;
            array_push($wrapped, ...explode("\n", wordwrap($ascii, 88, "\n", true)));
        }

        return $wrapped;
    }

    private function escape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }
}
