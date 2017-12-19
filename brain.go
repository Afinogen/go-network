package main

import (
	"github.com/goml/gobrain"
	"math/rand"
	"strings"
	"strconv"
	"fmt"
	"os"
)

func main() {
	// set the random seed to 0
	rand.Seed(0)

	//// create the XOR representation patter to train the network
	//patterns := [][][]float64{
	//	{{0, 0}, {0}},
	//	{{0, 1}, {1}},
	//	{{1, 0}, {1}},
	//	{{1, 1}, {0}},
	//}
	//
	//// instantiate the Feed Forward
	//ff := &gobrain.FeedForward{}
	//
	//// initialize the Neural Network;
	//// the networks structure will contain:
	//// 2 inputs, 2 hidden nodes and 1 output.
	//ff.Init(2, 2, 1)
	//
	//// train the network using the XOR patterns
	//// the training will run for 1000 epochs
	//// the learning rate is set to 0.6 and the momentum factor to 0.4
	//// use true in the last parameter to receive reports about the learning error
	//ff.Train(patterns, 1000, 0.6, 0.4, true)
	//
	//ff.Test(patterns)
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

	patterns := [][][]float64{}
	for i := 0; i < len(str)-1; i++ {
		in := [] float64{}
		for _, r := range str[i] {
			ff, _ := strconv.ParseFloat(string(r), 64)
			in = append(in, ff)
		}
		if len(in) != 540 {
			panic(len(in))
		}
		i++

		f, _ := strconv.ParseFloat(str[i], 64)
		out := [] float64{f}
		out2 := [][] float64{in, out}

		patterns = append(patterns, out2)
	}

	ff := &gobrain.FeedForward{}
	ff.Init(540, 2, 1)
	// Начинаем обучать нашу НС.
	// Количество итераций - 100000
	ff.Train(patterns, 1000, 0.6, 0.4, true)

	ff.Test(patterns)
}
