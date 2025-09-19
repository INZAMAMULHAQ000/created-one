<?php
/**
 * Number to Words Converter
 * Converts numeric amounts to words in Indian format
 */

function numberToWords($number) {
    $ones = array(
        0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'
    );

    $tens = array(
        0 => '', 2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
        6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
    );

    if ($number == 0) {
        return 'Zero';
    }

    $words = '';

    // Convert crores
    if ($number >= 10000000) {
        $crores = intval($number / 10000000);
        $words .= convertGroup($crores, $ones, $tens) . ' Crore ';
        $number = $number % 10000000;
    }

    // Convert lakhs
    if ($number >= 100000) {
        $lakhs = intval($number / 100000);
        $words .= convertGroup($lakhs, $ones, $tens) . ' Lakh ';
        $number = $number % 100000;
    }

    // Convert thousands
    if ($number >= 1000) {
        $thousands = intval($number / 1000);
        $words .= convertGroup($thousands, $ones, $tens) . ' Thousand ';
        $number = $number % 1000;
    }

    // Convert hundreds
    if ($number >= 100) {
        $hundreds = intval($number / 100);
        $words .= $ones[$hundreds] . ' Hundred ';
        $number = $number % 100;
    }

    // Convert remaining number
    if ($number > 0) {
        $words .= convertGroup($number, $ones, $tens);
    }

    return trim($words);
}

function convertGroup($number, $ones, $tens) {
    $result = '';

    if ($number >= 20) {
        $tensDigit = intval($number / 10);
        $onesDigit = $number % 10;
        $result = $tens[$tensDigit];
        if ($onesDigit > 0) {
            $result .= ' ' . $ones[$onesDigit];
        }
    } else {
        $result = $ones[$number];
    }

    return $result;
}

function amountInWords($amount) {
    // Split amount into rupees and paise
    $amountParts = explode('.', number_format($amount, 2, '.', ''));
    $rupees = intval($amountParts[0]);
    $paise = intval($amountParts[1]);

    $words = '';
    
    if ($rupees > 0) {
        $words .= numberToWords($rupees) . ' Rupee';
        if ($rupees > 1) {
            $words .= 's';
        }
    }

    if ($paise > 0) {
        if ($rupees > 0) {
            $words .= ' and ';
        }
        $words .= numberToWords($paise) . ' Paise';
    }

    return $words . ' Only';
}
?>