<?php
/*
 * The MIT License
 *
 * Copyright 2022 Ivan Smitka <ivan at stimulus dot cz>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 *
 */

include 'lib/phpqrcode/qrlib.php';
/**
 * Obchodník předá do QR kódu veškeré informace pomocí textového řetězce. Informace obsahují oddělená pole ACC – číslo účtu v IBAN formátu, AM – částka k platbě, CC – měna, DT – datum splatnosti, MSG – zpráva pro příjemce (nemůžete měnit v našem případě je uvedeno Platba qr kódem za objednávku – 0000), X-VS – variabilní symbol.
 * https://cbaonline.cz/upload/1408-standard-qr-kody-leden-2021.pdf
 */
/*
var msg = "PLATBA FEE BARBORA";
var dt = date("Ymd");
var q = "SPD*1.0*ACC:CZ9362106701002207352758*AM:2420*CC:CZK*DT:{$dt}}*X-VS:202205*MSG:{$msg}";
 */

$data = "";
foreach (array_filter([
    "ACC" => $_POST["ACC"],
    "AM" => $_POST["AM"],
    "CC" => "CZK",
    "MSG" => $_POST["MSG"],
    "X-VS" => $_POST["X-VS"],
    "DT" => date("Ymd"),
]) as $key => $value) {
    $data .= "*{$key}:{$value}";
}

$dir = __DIR__ . "/tmp";
if (!is_dir($dir)) {
    mkdir($dir);
}
$file = "{$dir}/" . md5($data) . ".png";
QRcode::png("SPD*1.0" . $data, $file, QR_ECLEVEL_M);
print 'data:image/png;base64,' . base64_encode(file_get_contents($file));
unlink($file);
