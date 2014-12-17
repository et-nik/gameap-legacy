#include <stdio.h>
#include <iostream>

#include <string>
#include <vector>

#include <sstream>
#include <fstream>

#ifdef _WIN32
#include <windows.h>
#elif MACOS
#include <sys/param.h>
#include <sys/sysctl.h>
#else
#include <unistd.h>
#endif

#include <boost/thread.hpp>

#include "functions.h"

// ---------------------------------------------------------------------

int substr_count(std::string source, std::string substring)
{
	int count = 0;

	for (size_t pos = 0; pos < source.size(); pos += substring.size())
	{
		pos = source.find(substring, pos);
		if (pos != std::string::npos)
		{
			++count;
		}
		else
		{
			break;
		}
	}

	return count;
}

// ---------------------------------------------------------------------

std::string trim(std::string& str)
{
	str.erase(0, str.find_first_not_of(' '));	//prefixing spaces
	str.erase(str.find_last_not_of(' ') + 1);	//surfixing spaces
	return str;
}

// ---------------------------------------------------------------------

void fast_exec(std::string command)
{
#ifdef _WIN32
	STARTUPINFO si;
	PROCESS_INFORMATION pi;

	ZeroMemory(&si, sizeof(si));
	si.cb = sizeof(si);
	ZeroMemory(&pi, sizeof(pi));

	wchar_t* szCmdline = new wchar_t[strlen(command.c_str()) + 1];
	mbstowcs(szCmdline, command.c_str(), strlen(command.c_str()) + 1);

	// Start the child process. 
	if (!CreateProcess(NULL,   // No module name (use command line)
		szCmdline,      // Command line
		NULL,           // Process handle not inheritable
		NULL,           // Thread handle not inheritable
		FALSE,          // Set handle inheritance to FALSE
		0,              // No creation flags
		NULL,           // Use parent's environment block
		NULL,           // Use parent's starting directory 
		&si,            // Pointer to STARTUPINFO structure
		&pi)           // Pointer to PROCESS_INFORMATION structure
		)
	{
		return;
	}

	// Close process and thread handles. 
	CloseHandle(pi.hProcess);
	CloseHandle(pi.hThread);
#else
	boost::thread bthrd(boost::bind(exec, command));
#endif
}

// ---------------------------------------------------------------------

std::string exec(std::string command)
{
	std::string excmd;

#ifdef _WIN32
	FILE * f = _popen(&command[0], "r");
#else
	FILE * f = popen(&command[0], "r");
#endif

	if (f == 0) {
		return "";
	}

	const int BUFSIZE = 1000;
	char buf[BUFSIZE];

	while (fgets(buf, BUFSIZE, f)) {
		excmd = excmd + buf;
	}

#ifdef _WIN32
	_pclose(f);
#else
	pclose(f);
#endif

	return excmd;
}

// ---------------------------------------------------------------------

std::vector<std::string> explode(std::string delimiter, std::string inputstring){
	std::vector<std::string> explodes;

	inputstring.append(delimiter);

	while (inputstring.find(delimiter) != std::string::npos){
		explodes.push_back(inputstring.substr(0, inputstring.find(delimiter)));
		inputstring.erase(inputstring.begin(), inputstring.begin() + inputstring.find(delimiter) + delimiter.size());
	}

	return explodes;
}

// ---------------------------------------------------------------------

std::string implode(std::string delimiter, std::vector<std::string> & elements)
{
	std::string full;

	for (std::vector<std::string>::iterator it = elements.begin(); it != elements.end(); ++it)
	{
		full += (*it);
		if (it != elements.end() - 1)
			full += delimiter;

	}
	return full;
}

// ---------------------------------------------------------------------

bool file_exists(std::string file_name)
{
	std::ifstream f(file_name.c_str());

	if (f.good()) {
		f.close();
		return true;
	}
	else {
		f.close();
		return false;
	}
}


// ---------------------------------------------------------------------

/**
* Получает количество ядер
*/
int get_cores_count() {
#ifdef WIN32
	SYSTEM_INFO sysinfo;
	GetSystemInfo(&sysinfo);
	return sysinfo.dwNumberOfProcessors;
#elif MACOS
	int nm[2];
	size_t len = 4;
	uint32_t count;

	nm[0] = CTL_HW; nm[1] = HW_AVAILCPU;
	sysctl(nm, 2, &count, &len, NULL, 0);

	if (count < 1) {
		nm[1] = HW_NCPU;
		sysctl(nm, 2, &count, &len, NULL, 0);
		if (count < 1) { count = 1; }
	}
	return count;
#else
	return sysconf(_SC_NPROCESSORS_ONLN);
#endif
}

// ---------------------------------------------------------------------

bool in_array(const std::string &needle, const std::vector< std::string > &haystack)
{
	int max = haystack.size();

	if (max == 0) return false;

	for (int i = 0; i<max; i++)
	if (haystack[i] == needle)
		return true;
	return false;
}
