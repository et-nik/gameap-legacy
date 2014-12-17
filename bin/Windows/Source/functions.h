#include <string>
#include <vector>

#ifndef FUNCTIONS_H
#define FUNCTIONS_H

// ---------------------------------------------------------------------

int substr_count(std::string source, std::string substring);

// ---------------------------------------------------------------------

std::string trim(std::string& str);

// ---------------------------------------------------------------------

void fast_exec(std::string command);

// ---------------------------------------------------------------------

std::string exec(std::string command);

// ---------------------------------------------------------------------

std::vector<std::string> explode(std::string delimiter, std::string inputstring);

// ---------------------------------------------------------------------

std::string implode(std::string delimiter, std::vector<std::string> & elements);

// ---------------------------------------------------------------------

bool file_exists(std::string file_name);

// ---------------------------------------------------------------------

int get_cores_count();

// ---------------------------------------------------------------------

bool in_array(const std::string &needle, const std::vector< std::string > &haystack);

#endif
