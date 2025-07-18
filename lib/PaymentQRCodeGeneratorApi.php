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

class PaymentQRCodeGeneratorApi
{
    public static function processRequest()
    {
        $success = false;
        if (($index = $_POST["index"]) >= 0) {
            $index = intval($index);
            $data = self::getAcc();
            if ($_POST["remove"]) {
                unset($data[$index]);
            } else {
                if (count($data) === 0 || $index > count($data)) {
                    $row = [];
                    $row[$_POST["name"]] = $_POST["value"];
                    $data[] = $row;
                } else {
                    $data[$index][$_POST["name"]] = $_POST["value"];
                }
            }
            $success = self::saveAcc($data);
        }

        return json_encode([
            "success" => $success,
            "data" => [
                "index" => $index
            ]
        ]);
    }

    private static function getDir()
    {
        $dir = __DIR__ . "/../data";
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return $dir;
    }

    private static function getFile()
    {
        return self::getDir() . "/data.json";
    }

    /**
     * @param $data array
     * @return bool
     */
    public static function saveAcc($data)
    {
        $file = self::getFile();
        if (file_put_contents($file, json_encode($data))) {
            return true;
        }
        return false;
    }

    /**
     * Get account list
     * @return array|mixed
     */
    public static function getAcc()
    {
        $file = self::getFile();
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true);
        }
        return [];
    }
}