<?php

require_once __DIR__.'/ParsingCaptcha.php';

$files = glob(__DIR__.'/testdata_1/output_2.jpg');

$a = new \afinogen89\net\ParsingCaptcha();
//$a->decryptCaptcha('output/13.jpg');
file_put_contents(__DIR__.'/all.txt', '');
foreach ($files as $key => $file) {
    if ($file[strlen($file) - 5] != "0") {
        $arr = $a->decryptCaptcha($file);

        $a->fixNumber($arr['numbres'][0][0],$arr['numbres'][1][0]);
        exit;

        $data = '';
        foreach ($arr['numbres'][0][0] as $i) {
            echo implode($i).PHP_EOL;
            $data .= implode($i);
        }

        $num = readline();
        $data .= PHP_EOL.($num / 100 + 0.1).PHP_EOL;
        echo PHP_EOL;


        foreach ($arr['numbres'][1][0] as $i) {
            echo implode($i).PHP_EOL;
            $data .= implode($i);
        }

        $num = readline();
        $data .= PHP_EOL.($num / 100 + 0.1).PHP_EOL;


        echo '------------------------'.PHP_EOL;

        file_put_contents(__DIR__.'/all.txt', $data, FILE_APPEND);
    }
}