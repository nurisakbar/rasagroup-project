<?php

namespace App\Support;

class InvoiceBarcode
{
    public static function html(string $text, int $height = 46): string
    {
        $bits = '11010010000';
        foreach (str_split($text) as $character) {
            $bits .= str_pad(decbin(ord($character)), 8, '0', STR_PAD_LEFT);
        }
        $bits .= '1100011101011';

        $html = '<table cellpadding="0" cellspacing="0" style="border-collapse:collapse;"><tr>';
        foreach (str_split($bits) as $bit) {
            $color = $bit === '1' ? '#111111' : '#ffffff';
            $html .= '<td style="width:1.4px;height:'.$height.'px;background:'.$color.';padding:0;margin:0;border:0;"></td>';
        }
        $html .= '</tr></table>';

        return $html;
    }
}
