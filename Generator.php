<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 16.12.17
 * Time: 18:02
 */

namespace afinogen89\net;


class Generator
{
    public $width = 100;                //Ширина изображения
    public $height = 50;                //Высота изображения
    public $font_size = 14;            //Размер шрифта
    public $let_amount = 4;            //Количество символов, которые нужно набрать
    public $fon_let_amount = 23;        //Количество символов на фоне
    public $font = __DIR__.'/fonts/verbcondregular-webfont.ttf';    //Путь к шрифту
    public $letters = ["0", "1", "2", "3", "4", "5", "6", '7', '8', '9']; //набор символов
    public $colors = ["90", "110", "130", "150", "170", "190", "210"]; //цвета

    public function createImage($first, $second)
    {
        $src = imagecreatetruecolor($this->width, $this->height);    //создаем изображение
        $fon = imagecolorallocate($src, 4, 14, 36);    //создаем фон
        imagefill($src, 0, 0, $fon);                        //заливаем изображение фоном

        for ($i = 0; $i < $this->fon_let_amount; $i++)            //добавляем на фон буковки
        {
            $this->setFoneLettters($src);
        }

//        $sum = $first + $second;
        $this->setMainLetters($src, 0, $first);
        $this->setMainLetters($src, 1, '+');
        $this->setMainLetters($src, 2, $second);
        $this->setMainLetters($src, 3, '=');
//        $this->setMainLetters($src, 4, $sum);

        return $src;
    }

    public function setFoneLettters(&$src)
    {
        //случайный цвет
        $color = imagecolorallocatealpha($src, rand(0, 255), rand(0, 255), rand(0, 255), 100);
        //случайный символ
        $letter = $this->letters[rand(0, count($this->letters) - 1)];
        //случайный размер
        $size = rand($this->font_size - 5, $this->font_size + 2);
        imagettftext($src, $size, rand(0, 45),
            rand($this->width * 0.1, $this->width - $this->width * 0.1),
            rand($this->height * 0.2, $this->height), $color, $this->font, $letter);
    }

    public function setMainLetters(&$src, $pos, $letter)
    {
        $color = imagecolorallocatealpha($src, $this->colors[rand(0, count($this->colors) - 1)],
            $this->colors[rand(0, count($this->colors) - 1)],
            $this->colors[rand(0, count($this->colors) - 1)], rand(20, 40));
        $size = rand($this->font_size * 2 - 2, $this->font_size * 2 + 2);
        $x = ($pos + 1) * $this->font_size + rand(1, 5);        //даем каждому символу случайное смещение
        $y = (($this->height * 2) / 3) + rand(0, 5);
        imagettftext($src, $size, rand(0, 15), $x, $y, $color, $this->font, $letter);
    }
}

require_once __DIR__.'/ParsingCaptcha.php';

$gen = new Generator();
$matrix = new ParsingCaptcha();

file_put_contents(__DIR__.'/train.txt', '');
for ($i = 0; $i < 1000; $i++) {
    $first = rand(0, 9);
    $second = rand(0, 9);
    $img = $gen->createImage($first, $second);
    imagegif($img, __DIR__.'/gen_image/'.$i.'.gif');
    system('./img_convert '.$i, $out);
    if (!empty($out)) {
        var_dump($out);
        exit;
    }
    echo $first.' '.$second.PHP_EOL;
    $arr = $matrix->decryptCaptcha(__DIR__.'/gen_image/output_'.$i.'.jpg');
    $data = '';
    foreach ($arr['numbres'][0][0] as $number) {
//        echo implode($i).PHP_EOL;
        $data .= implode($number);
    }

    $num = $first/100 + 0.1;
    $data .= PHP_EOL.$num.PHP_EOL;
//    echo PHP_EOL;

    foreach ($arr['numbres'][1][0] as $number) {
//        echo implode($i).PHP_EOL;
        $data .= implode($number);
    }

    $num = '0.3';
    $data .= PHP_EOL.$num.PHP_EOL;

    if (isset($arr['numbres'][2])) {
        foreach ($arr['numbres'][2][0] as $number) {
//            echo implode($i).PHP_EOL;
            $data .= implode($number);
        }

        $num = $second/100 + 0.1;
        $data .= PHP_EOL.$num.PHP_EOL;
    }

//    echo '------------------------'.PHP_EOL;

    file_put_contents(__DIR__.'/train.txt', $data, FILE_APPEND);
    unlink(__DIR__.'/gen_image/'.$i.'.gif');
    unlink(__DIR__.'/gen_image/output_'.$i.'.jpg');
}
