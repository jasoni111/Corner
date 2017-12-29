#include <iostream>
#include <vector>
#include "helper.h"

int main(int argc, char* argv[]) {
	std::string tempf;
	if (argc == 1) std::cin >> tempf;
	const std::string filename = (argc == 1) ? tempf : argv[1];

	int w, h; // width and height of image
	std::vector<uint8_t> img = readBMP(filename, w, h); // use getPixel() defined in helper.h to get pixel information
	int cornerc = 0; // corner count
	std::vector< std::pair<int, int> > cornerv{}; // vector of corner coordinates, stored x-coord to pair.first and y-coord to pair.second

	// TODO: Your implementation of corner detection
	// You may define global functions in main.cpp if necessary


	// END OF IMPLEMENTATION

	printResult(filename, cornerc, cornerv);

	return 0;
}
