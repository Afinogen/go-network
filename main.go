package main

import (
	"fmt"
	"github.com/fxsjy/gonn/gonn"
	"github.com/disintegration/imaging"
	"os"
	"strings"
	"strconv"
	"log"
	"image/color"
	"image"
)

func CreateNN() {
	// Создаём НС с 3 входными нейронами (столько же входных параметров),
	// 16 скрытыми нейронами и
	// 4 выходными нейронами (столько же вариантов ответа)
	nn := gonn.DefaultNetwork(160, 16, 1, false)

	file, err := os.Open("ocr.data")
	if err != nil {
		// handle the error here
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
		sumb := strings.Split(str[i], " ")
		in := [] float64{}
		for e := range sumb {
			f, err := strconv.ParseFloat(sumb[e], 64)
			if err == nil {
				in = append(in, f)
			}
		}

		input = append(input, in)

		i++

		f, _ := strconv.ParseFloat(str[i], 64)
		out := [] float64{f}
		target = append(target, out)
	}

	fmt.Println(target)

	// Начинаем обучать нашу НС.
	// Количество итераций - 100000
	nn.Train(input, target, 100000)

	// Сохраняем готовую НС в файл.
	gonn.DumpNN("gonn", nn)
}

func CreateNNMy() {
	// Создаём НС с 3 входными нейронами (столько же входных параметров),
	// 16 скрытыми нейронами и
	// 4 выходными нейронами (столько же вариантов ответа)
	nn := gonn.NewNetwork(391, 4, 1, false, 0.18, 0.15)

	file, err := os.Open("all.txt")
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
		//sumb := strings.Split(str[i], " ")
		in := [] float64{}
		//for e := range sumb {
		//	f, err := strconv.ParseFloat(sumb[e], 64)
		//	if err == nil {
		//		in = append(in, f)
		//	}
		//}
		for _, r := range str[i] {
			ff, _ := strconv.ParseFloat(string(r), 64)
			in = append(in, ff)
		}

		input = append(input, in)

		i++

		f, _ := strconv.ParseFloat(str[i], 64)
		f = f / 100
		out := [] float64{f}
		target = append(target, out)
	}

	//fmt.Println(target)

	// Начинаем обучать нашу НС.
	// Количество итераций - 100000
	nn.Train(input, target, 1000000)

	// Сохраняем готовую НС в файл.
	gonn.DumpNN("gonn", nn)
}

func prepareImage(dir, fname string) {
	// Open the test image.
	src, err := imaging.Open("testdata/" + dir + "/" + fname + ".gif")
	if err != nil {
		log.Fatalf("Open failed: %v", err)
	}

	// Crop the original image to 350x350px size using the center anchor.
	src = imaging.CropAnchor(src, 75, 50, imaging.Center)

	// Resize the cropped image to width = 256px preserving the aspect ratio.
	//src = imaging.Resize(src, 256, 128, imaging.Lanczos)

	// Create a blurred version of the image.
	//img1 := imaging.Blur(src, 2)

	// Create a grayscale version of the image with higher contrast and sharpness.
	img2 := imaging.Grayscale(src)
	img2 = imaging.AdjustContrast(img2, 20)
	img2 = imaging.Sharpen(img2, 2)

	// Create a new image and paste the four produced images into it.
	dst := imaging.New(75, 50, color.NRGBA{0, 0, 0, 0})
	dst = imaging.Paste(dst, img2, image.Pt(0, 0))

	// Save the resulting image using JPEG format.
	err = imaging.Save(dst, "testdata/output/"+dir+fname+".jpg")
	if err != nil {
		log.Fatalf("Save failed: %v", err)
	}
}

func main() {
	CreateNNMy()
	// Загружем НС из файла.
	nn := gonn.LoadNN("gonn")
	//

	file, err := os.Open("all_sum.txt")
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

		f, err := strconv.ParseFloat(str[i], 64)
		if err == nil {
			f = f / 100
		}
		out := nn.Forward(in);
		if err == nil {
			if out[0] < 0 {
				out[0] *= -1
			}

			f1 := strconv.FormatFloat(out[0], 'f', 2, 64)
			f2 := strconv.FormatFloat(f, 'f', 2, 64)
			if f1 == f2 {
				count++;
			}
			fmt.Println(out[0], f1, f)
			continue
		}
		fmt.Println(out, str[i])
	}
	//fmt.Println((len(str)-1)/2, count)
	fmt.Println((len(str)-1)/4, count)

	//dirs := [] string{"1", "2", "3", "1496733574", "1496736839", "1496738651", "1496740371", "1496742229"}
	//for e := range dirs {
	//	prepareImage(dirs[e], "0");
	//	prepareImage(dirs[e], "1");
	//	prepareImage(dirs[e], "2");
	//	prepareImage(dirs[e], "3");
	//}
}
