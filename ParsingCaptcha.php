<?php

namespace afinogen89\net;

class ParsingCaptcha
{
    /**
     * Дешифровка каптчи
     *
     * @param string $_file
     *
     * @return bool|array
     */
    public function decryptCaptcha($_file = null)
    {
        $img2 = imagecreatefromjpeg($_file);

        $dataAll = $this->imageToMatrix($img2);
        $returnData['all'] = $dataAll;
        $dataOne = $this->imageToMatrix($img2, false);

        $returnData['one'] = $dataOne;
        $dataTwo = $this->firstFilter($this->searchTwo($dataAll, $dataOne));
        $returnData['two'] = $dataTwo;

        $tmp = $this->find15($dataAll);
        if (!$this->isGoodMatrix($tmp[0])) {
            $tmp = $this->find15($dataOne);
        }
        if (!$this->isGoodMatrix($tmp[0])) {
            $tmp = $this->find15($dataTwo);
        }
        $tmp[0] = $this->_correctArray($tmp[0]);
        $returnData['numbres'][] = $tmp;

        $tmp = $this->find15($dataAll, $tmp[1], $tmp[2]);
        if (!$this->isGoodMatrix($tmp[0])) {
            $tmp = $this->find15($dataOne, $tmp[1], $tmp[2]);
        }
        if (!$this->isGoodMatrix($tmp[0])) {
            $tmp = $this->find15($dataTwo, $tmp[1], $tmp[2]);
        }
        $tmp[0] = $this->_correctArray($tmp[0]);
        $returnData['numbres'][] = $tmp;

        $tmp = $this->find15($dataAll, $tmp[1] - 2, $tmp[2]);
        if (!$this->isGoodMatrix($tmp[0])) {
            $tmp = $this->find15($dataOne, $tmp[1] - 2, $tmp[2]);
        }
        if (!$this->isGoodMatrix($tmp[0])) {
            $tmp = $this->find15($dataTwo, $tmp[1] - 2, $tmp[2]);
        }
        if ($this->isGoodMatrix($tmp[0])) {
            $tmp[0] = $this->_correctArray($tmp[0]);
            $returnData['numbres'][] = $tmp;
        }
//        foreach ($returnData['numbres'] as $item) {
//            $this->printMatrix($item[0]);
//        }

        return $returnData;
    }

    public function fixNumber(array $_numFirst, array $_numSecond)
    {
        $this->printMatrix($_numFirst);
        echo PHP_EOL.PHP_EOL;
        $this->printMatrix($_numSecond);
        echo PHP_EOL.PHP_EOL;
        $height = count($_numFirst);
        $width = count($_numFirst[0]);
        $slice = [];
        for ($i = 0; $i < $height; $i++) {
            for ($j = $width - 5; $j < $width; $j++) {
                echo $slice[$i][$j - $width + 5] = $_numFirst[$i][$j];
            }
            echo PHP_EOL;
        }

        $isNoClear = false;
        for ($i = 0; $i < $height; $i++) {
            for ($j = 0; $j < 5; $j++) {
                if ($slice[$i][$j] == 1 && $_numSecond[$i][$j] != $slice[$i][$j]) {
                    $isNoClear = true;
                    break 2;
                }
            }
        }

        if (!$isNoClear) {
            for ($i = 0; $i < $height; $i++) {
                for ($j = 0; $j < 5; $j++) {
                    if ($slice[$i][$j] == 1) {
                        $_numSecond[$i][$j] = 0;
                    }
                }
            }
        }
        echo PHP_EOL.PHP_EOL;
        $this->printMatrix($_numSecond);
        echo PHP_EOL.PHP_EOL;
    }

    protected function _correctArray(array $data)
    {
        if ($this->getCountNum($data, true) < 540) {
            $height = count($data);
            $width = count($data[0]);

            $newWidth = 20 - $width;

            for ($i = 0; $i < $height; $i++) {
                for ($j = $width; $j < $width + $newWidth; $j++) {
                    $data[$i][$j] = 0;
                }
            }

            if ($this->getCountNum($data, true) < 540) {
                $this->printMatrix($data);
                var_dump($this->getCountNum($data, true));
                exit;
            }
        }

        return $data;
    }

    /**
     * Конвертация картинки в матрицу
     *
     * @param $im
     * @param bool $isLow
     *
     * @return array
     */
    protected function imageToMatrix($im, $isLow = true)
    {
        $height = imagesy($im);
        $width = imagesx($im);

        $binary = [];
        for ($i = 0; $i < $height; $i++) {
            for ($j = 0; $j < $width; $j++) {
                $rgb = imagecolorat($im, $j, $i);

                //получаем индексы цвета RGB
                list($r, $g, $b) = array_values(imageColorsForIndex($im, $rgb));

                // если цвет пикселя не равен фоновому заполняем матрицу единицей
                $binary[$i][$j] = (1 - (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255 < ($isLow ? 0.74 : 0.6)) ? 1 : 0;
            }
        }

        return $binary;
    }

    /**
     * Проверка корректности матрицы
     *
     * @param array $data
     *
     * @return bool
     */
    protected function isGoodMatrix(array $data)
    {
        return $this->getCountNum($data) > 30;
    }

    /**
     * Подсчет кол-ва едениц
     *
     * @param array $data
     *
     * @return int
     */
    protected function getCountNum(array $data, $all = false)
    {
        $h = count($data);
        $w = count($data[0]);

        $counter = 0;

        for ($i = 0; $i < $h; $i++) {
            for ($j = 0; $j < $w; $j++) {
                if ($data[$i][$j] === 1 || $all) {
                    $counter++;
                }
            }
        }

        return $counter;
    }

    /**
     * Поиск второго отображения на основе двух
     *
     * @param array $dataAll
     * @param array $dateOne
     *
     * @return array
     */
    protected function searchTwo(array $dataAll, array $dataOne)
    {
        $h = count($dataAll);
        $w = count($dataAll[0]);

        $two = [];
        for ($i = 0; $i < $h; $i++) {
            for ($j = 0; $j < $w; $j++) {
                if ($dataAll[$i][$j] != $dataOne[$i][$j]) {
                    $two[$i][$j] = $dataAll[$i][$j];
                } else {
                    $two[$i][$j] = 0;
                }
            }
        }

        return $two;
    }

    /**
     * Поиск цифр
     *
     * @param array $data
     * @param int $offsetWidth
     * @param int $offsetJ
     *
     * @return array
     */
    protected function find15(array $data, $offsetWidth = 0, $offsetJ = 0)
    {
        $h = count($data);
        $w = count($data[0]);

        $startWidth = 0;
        $startHeight = 0;
        $addOffsetI = 0;
        if ($offsetWidth) {
            $addOffsetI = 15;
        }
        for ($width = $offsetWidth + $addOffsetI; $width < $w; $width++) { //по ширине
            for ($height = $offsetJ; $height < $h; $height++) { //по высоте
                if ($data[$height][$width] === 1 && $this->getCountInRound($data, $height, $width, 2) > 5) {
                    $startWidth = $width;
                    $startHeight = $height;
                    break 2;
                }
            }
        }

//        echo $startWidth.' '.$startHeight.PHP_EOL;
        for ($height = 0; $height < $h; $height++) { //по высоте
            for ($width = $startWidth; $width < $startWidth + 15; $width++) { //по ширине
                if ($data[$height][$width] === 1 && $this->getCountInRound($data, $height, $width, 2) > 5) {
                    $startHeight = $height;
                    break 2;
                }
            }
        }
//        echo $startWidth.' '.$startHeight.PHP_EOL;

        $number = [];
        $curI = 0;
        $curJ = 0;
        for ($height = $startHeight; $height < $startHeight + 27; $height++) {
            for ($width = $startWidth - 1; $width < $startWidth + 19; $width++) {
                if (isset($data[$height][$width])) {
                    $number[$curI][$curJ++] = $data[$height][$width];
                }
            }
            $curI++;
            $curJ = 0;
        }
        //echo '---'.PHP_EOL;
        do {
            $numberOld = $number;
            $number = $this->firstFilter($number, 3, 5);
            $count1 = $this->getCountNum($numberOld);
            $count2 = $this->getCountNum($number);
            //echo $count1.' '.$count2.PHP_EOL;
        } while ($count1 !== $count2);

        //echo '--'.PHP_EOL;

        return [
            $number,
            $startWidth,
            $startHeight,
        ];
    }

    /**
     * Вывод матрицы
     *
     * @param array $data
     */
    public function printMatrix(array $data)
    {
        $h = count($data);
        $w = count($data[0]);

        for ($i = 0; $i < $h; $i++) {
            for ($j = 0; $j < $w; $j++) {
                echo $data[$i][$j];
            }
            echo PHP_EOL;
        }
        echo PHP_EOL;
    }

    /**
     * Вычисление кол-во едениц в заданном радиусе
     *
     * @param array $data
     * @param int $ri
     * @param int $rj
     * @param int $round
     *
     * @return int
     */
    protected function getCountInRound(array $data, $ri, $rj, $round = 1)
    {
        $count = 0;
        for ($i = $ri - $round; $i < $ri + $round; $i++) {
            for ($j = $rj - $round; $j < $rj + $round; $j++) {
                if (isset($data[$i][$j]) && $data[$i][$j] === 1) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Простая фильтрация
     *
     * @param array $data
     * @param int $round
     * @param int $num
     *
     * @return array
     */
    protected function firstFilter(array $data, $round = 3, $num = 4)
    {
        $h = count($data);
        $w = count($data[0]);

        for ($i = 0; $i < $h; $i++) {
            for ($j = 0; $j < $w; $j++) {
                if ($this->getCountInRound($data, $i, $j, $round) < $num) {
                    $data[$i][$j] = 0;
                }
            }
        }

        return $data;
    }
}
