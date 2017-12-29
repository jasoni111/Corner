/**
 * helper.h
 *
 * Author: Peter Tse (mcreng)
 * BMP code adapted from
 * https://www.quora.com/What-will-be-the-equivalent-code-of-this-program-read-a-monochrome-BMP-file-in-C++
 *
 * DO NOT MODIFY THIS FILE
 */

#ifndef __HELPER_H_
#define __HELPER_H_

#include <iostream>
#include <vector>
#include <fstream>

#pragma pack(push, 1)
struct bmp_header {
	char magic[2];
	uint32_t fileSize;
	uint32_t res;
	uint32_t offset;
	uint32_t header_size;
	uint32_t width;
	uint32_t height;
	uint16_t bits_per_pixel;
};
#pragma pack(pop)

/**
 * Parse the bmp files into a vector of character
 * @param fname bmp filename
 * @param w     reference variable of storing width of image
 * @param h     reference variable of storing height of image
 */
std::vector<uint8_t> readBMP(std::string const &fname, int& w, int& h) {
	bmp_header head;
	std::ifstream f(fname, std::ios::binary);

	f.read((char *)&head, sizeof(head));

	if (head.bits_per_pixel != 1)
		return{};

	w = head.width;
	h = head.height;

	// lines are aligned on 4-byte boundary
	int lineSize = (w / 8 + (w / 8) % 4);
	int fileSize = lineSize * h;

	std::vector<uint8_t> img(w*h);
	std::vector<uint8_t> data(fileSize);

	// find bits
	f.seekg(head.offset);

	// read data
	f.read((char *)&data[0], fileSize);

	// decode bits
	int i, j, k, rev_j;
	for (j = 0, rev_j = h - 1; j < h; j++, rev_j--) {
		for (i = 0; i < w / 8; i++) {
			int fpos = j * lineSize + i, pos = rev_j * w + i * 8;
			for (k = 0; k < 8; k++)
				img[pos + (7 - k)] = (data[fpos] >> k) & 1;
		}
	}
	return img;
}

/**
 * Get pixel information of image
 *
 * Details of img[] array
 * x: horizontal direction
 * y: vertical direction
 * (0,0) --------------- (w-1, 0)
 *   |                       |
 *   |                       |
 *   |                       |
 *   |                       |
 * (0, h-1) ------------ (w-1, h-1)
 * The pixel information is stored by row in the array, i.e.
 * img[0] stores (0, 0); img[1] stores (1, 0); ...; img[w-1] stores (w-1, 0); img[w] stores (0, 1).
 *
 * @param  img Image vector
 * @param  x   x-coordinate of desired location
 * @param  y   y-coordinate of desired location
 * @param  w   width of image
 * @param  h   height of image
 * @return     1 for black pixel; 0 for white pixel
 */
int8_t getPixel(std::vector<uint8_t> img, int x, int y, int w, int h) {
  static const int8_t masks[] = "10";
  return masks[img[y*w+x]];
}

/**
 * Print result to result.txt
 * @param filename Image filename
 * @param cornerc  Corner count
 * @param cornerv  Corner coordinates vector
 */
void printResult(std::string filename, int cornerc, std::vector< std::pair<int, int> > cornerv) {
  std::fstream f;
  f.open("result.txt", std::fstream::out | std::fstream::app); // append
  f << filename << " "; // prints filename
  f << cornerc << " "; // prints corner count
  for (const auto& corner : cornerv)
    f << corner.first << " " << corner.second << " "; // print corner coordinates
  f << std::endl;
}

#endif // __HELPER_H_
