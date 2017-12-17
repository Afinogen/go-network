package main

import (
	"github.com/disintegration/imaging"
	"log"
	"image/color"
	"image"
	"os"
	"fmt"
)

func prepareImage(fname string) {
	// Open the test image.
	src, err := imaging.Open("gen_image/" + fname + ".gif")
	if err != nil {
		fmt.Printf("Open failed: %v", err)
		panic(err)
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
	err = imaging.Save(dst, "gen_image/output_"+fname+".jpg")
	if err != nil {
		log.Fatalf("Save failed: %v", err)
	}
}

func main() {
	prepareImage(os.Args[1])
}
