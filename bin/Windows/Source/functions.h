#include <string>
#include <vector>

using namespace std;

#ifndef FUNCTIONS_H
#define FUNCTIONS_H

// ---------------------------------------------------------------------

int substr_count(string source, string substring);

// ---------------------------------------------------------------------

string trim(string& str);

// ---------------------------------------------------------------------

void fast_exec(string command);

// ---------------------------------------------------------------------

string exec(string command);

// ---------------------------------------------------------------------

vector<string> explode(string delimiter, string inputstring);

// ---------------------------------------------------------------------

string implode(string delimiter, vector<string> & elements);

// ---------------------------------------------------------------------

bool file_exists(string file_name);

// ---------------------------------------------------------------------

int get_cores_count();


#endif