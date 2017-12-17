package main

import (
	"fmt"
	"github.com/fxsjy/gonn/gonn"
	"os"
	"strings"
	"strconv"
)

func CreateNN() {
	// Создаём НС с 3 входными нейронами (столько же входных параметров),
	// 16 скрытыми нейронами и
	// 4 выходными нейронами (столько же вариантов ответа)
	nn := gonn.NewNetwork(540, 2, 1, false, 0.25, 0.1)

	file, err := os.Open("train.txt")
	if err != nil {
		// handle the error here
		fmt.Println(err)
		return
	}
	defer file.Close()

	// get the file size
	stat, err := file.Stat()
	if err != nil {
		return
	}
	// read the file
	bs := make([]byte, stat.Size())
	_, err = file.Read(bs)
	if err != nil {
		return
	}

	str := strings.Split(string(bs), "\n")

	// Создаём массив входящих параметров:
	input := [][]float64{}
	// Теперь создаём "цели" - те результаты, которые нужно получить
	target := [][]float64{}
	for i := 0; i < len(str)-1; i++ {
		in := [] float64{}
		for _, r := range str[i] {
			ff, _ := strconv.ParseFloat(string(r), 64)
			in = append(in, ff)
		}
		if len(in) != 540 {
			panic(len(in))
		}
		input = append(input, in)

		i++

		f, _ := strconv.ParseFloat(str[i], 64)
		out := [] float64{f}
		target = append(target, out)
	}

	// Начинаем обучать нашу НС.
	// Количество итераций - 100000
	nn.Train(input, target, 100)

	// Сохраняем готовую НС в файл.
	gonn.DumpNN("gonn", nn)
}

func main() {
	CreateNN()
	// Загружем НС из файла.
	nn := gonn.LoadNN("gonn")
	//

	file, err := os.Open("all.txt")
	//file, err := os.Open("all.txt")
	if err != nil {
		// handle the error here
		fmt.Println(err)
		return
	}
	defer file.Close()

	// get the file size
	stat, err := file.Stat()
	if err != nil {
		return
	}
	// read the file
	bs := make([]byte, stat.Size())
	_, err = file.Read(bs)
	if err != nil {
		return
	}

	str := strings.Split(string(bs), "\n")
	count := 0
	for i := 0; i < len(str)-1; i++ {
		in := [] float64{}
		for _, r := range str[i] {
			ff, _ := strconv.ParseFloat(string(r), 64)
			in = append(in, ff)
		}

		i++

		f, _ := strconv.ParseFloat(str[i], 64)

		out := nn.Forward(in);
		prec := 2
		if f == 0.3 {
			prec = 1
		}
 		f1 := strconv.FormatFloat(out[0], 'f', prec, 64)
		f2 := strconv.FormatFloat(f, 'f', prec, 64)
		if f1 == f2 {
			count++;
		} else {
			fmt.Println(out[0], f1, f)
		}
	}
	fmt.Println((len(str)-1)/2, count)
}
