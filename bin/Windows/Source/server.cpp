#include <stdio.h>
#include <iostream>
#include <cstring>
#include <sstream>

#include <string>

#include <map>

#include <boost/regex.hpp>
#include <boost/format.hpp>
#include <boost/algorithm/string.hpp>

#ifdef _WIN32
#include <windows.h>
#else
#include <unistd.h>
#endif

#include "functions.h"

using namespace std;

string type 		= "help";
string dir  		= "";
string screen_name 	= "";
string ip			= "0.0.0.0";
string port			= "";
string command		= "";
string user			= "";
string memory		= "";
string percentage	= "";
string max_speed	= "";

string program 		= "";
string arguments 	= "";

string psexec	= "";

// ---------------------------------------------------------------------

void show_help()
{
	cout << "**************************************\n";
	cout << "* Welcome to GameAP Console Launcher *\n";
	cout << "**************************************\n\n";
		
	cout << "Program created by ET-NiK \n";
	cout << "Site: http://www.gameap.ru \n\n";
	
	cout << "Parameters\n";
	cout << "-t <type>	(start|stop|restart|status|get_console|send_command)\n";
	cout << "-d <dir>	base directory\n";
	cout << "-n <screen_name> name screen\n";
	cout << "-i <ip>\n";
	cout << "-p <port>\n";
	cout << "-c <command>  command (example 'hlds.exe -game valve +ip 127.0.0.1 +port 27015 +map crossfire')\n";
	cout << "-u <user>\n";
	cout << "-m <memory>	 RAM Limit (Kb)\n";
	cout << "-f <percentage>	 CPU Limit (%)\n";
	cout << "-s <max speed>	 NET Limit (Kb/s)\n\n";

	cout << "Examples:\n";
	cout << "./server.sh -t start -d /home/hl_server -n screen_hldm -i 127.0.0.1 -p 27015 -c \"hlds.exe -game valve +ip 127.0.0.1 +port 27015 +map crossfire\"\n";
	cout << "./server.sh -t get_console -n hldm -u usver\n";
}

// ---------------------------------------------------------------------

string cpu_affinity()
{
	int cores = get_cores_count();

	vector<string> vcores;
	std::stringstream ss;

	for (int i = 0; i < cores; i++) {
		if (i == 0) {
			ss << i;
		} else {
			ss << "," << i;
		}
	}

	// string affinity = implode(",", vcores);

	return ss.str();
}

// ---------------------------------------------------------------------

int server_status()
{
	int pid = 0;

	cpu_affinity();
	
	/* wmic process where description="rust_server.exe" get executablepath, processid
	 * 
	 * ExecutablePath                       ProcessId
	 * C:\servers\rust_01\rust_server.exe   2448
	 * C:\servers\rust_02\rust_server.exe  	2240
	*/
	
	string output = exec(str(boost::format("wmic process where description=\"%s\" get executablepath, processid") % program));
	vector<string> expl = explode("\n", output);
	
	for (int i = 1; i < expl.size(); i++) {
		boost::regex xregex("\\s+");
		string str = boost::regex_replace(expl[i], xregex, " ", boost::match_default | boost::format_perl);
		
		vector<string> expl_str = explode(" ", str);
		
		expl_str[0] = expl_str[0].substr(0, expl_str[0].length()-program.length()-1);
		
		if (expl_str.size() > 1 && expl_str[0] == dir) {
			pid = stoi(expl_str[1]);
			break;
		}
	}
	
	if (pid) {
		string output = exec(str(boost::format("echo %d > %s/pid.txt") % pid % dir));
	} else {
		string output = exec(str(boost::format("echo NOT FOUND > %s/pid.txt") % dir));
	}
	
	return pid;
}

// ---------------------------------------------------------------------

int server_start()
{
	fast_exec(str(boost::format("%s \"%s\\%s\" %s") % psexec % dir % program % arguments));

	// Sleep
#ifdef _WIN32
	Sleep(2000);
#else
	sleep(2);
#endif

	return server_status();
}

// ---------------------------------------------------------------------

int server_stop()
{
	int pid = server_status();

	if (pid != 0) {
		fast_exec(str(boost::format("taskkill /f /pid %d") % pid));
	} else {
		return 0;
	}

	// Sleep
#ifdef _WIN32
	Sleep(3000);
#else
	sleep(3);
#endif

	pid = server_status();

	if (pid != 0) {
		return 0;
	} else {
		return 1;
	}
}

// ---------------------------------------------------------------------

void main(int argc, char *argv[]) 
{	
	//setlocale(LC_CTYPE, "rus");

	for (int i = 0; i < argc-1; i++) {

		if (string(argv[i]) == "-t") {
			// Тип
			type = argv[i+1];
			i++;
		} else if (string(argv[i]) == "-d") {
			// Директория
			dir = argv[i+1];
			i++;
		} else if (string(argv[i]) == "-n") {
			// Screen name
			screen_name = argv[i+1];
			i++;
		} else if (string(argv[i]) == "-i") {
			// IP
			ip = argv[i+1];
			i++;
		} else if (string(argv[i]) == "-p") {
			// Port
			port = argv[i+1];
			i++;
		} else if (string(argv[i]) == "-c") {
			// Start Command
			command = argv[i+1];
			i++;
		} else if (string(argv[i]) == "-u") {
			// User
			user = argv[i+1];
			i++;
		} else if (string(argv[i]) == "-m") {
			// Memory
			memory = argv[i+1];
			i++;
		} else if (string(argv[i]) == "-f") {
			// CPU
			percentage = argv[i+1];
			i++;
		} else if (string(argv[i]) == "-s") {
			// Speed Limit
			max_speed = argv[i+1];
			i++;
		} else {
			// ...
		}
    }

	// Slash convert
#ifdef _WIN32
	// Windows
	dir = dir + "\\";
    boost::replace_all(dir, "/", "\\");

	boost::regex xregex("\\\\\\\\+");
    dir = boost::regex_replace(dir, xregex, "\\", boost::match_default | boost::format_perl);
    dir = dir.substr(0, dir.length()-1);
#else
	// Linux
	dir = dir + "/";
	boost::replace_all(dir, "\\", "/");
	boost::regex xregex("/+");
	dir = boost::regex_replace(dir, xregex, "/", boost::match_default | boost::format_perl);
	dir = dir.substr(0, dir.length()-1);
#endif

	if (file_exists("psexec.exe")) {
		psexec = str( boost::format("psexec.exe -s -i -d -w \"%s\" -a %s ") % dir % cpu_affinity() );
	} else if(file_exists("paexec.exe")) {
		psexec = str( boost::format("paexec.exe \\\\localhost -s -d -w  \"%s\" -a %s ") % dir % cpu_affinity() );
	} else {
		cout << "psexec.exe and paexec.exe not found" << endl;
		psexec = str( boost::format("start /D \"%s\" /I /affinity -a %s ") % dir % cpu_affinity() );
	}
    
    /* Разъединение программы и аргумента в команде запуска сервера */
    vector<string> explodes = explode(" ", command);

    program 	= explodes[0];
    
    int i = 1;
    int count = explodes.size();
    while(i < count)
	{
		arguments = arguments + explodes[i] + ' ';
		i++;
	}

	/*
	cout << "type:" << type << endl;
	cout << "dir:" << dir << endl;
	cout << "screen_name:" << screen_name << endl;
	cout << "ip:" << ip << endl;
	cout << "port:" << port << endl;
	cout << "command:" << command << endl;
	cout << "user:" << user << endl;
	cout << "memory:" << memory << endl;
	cout << "percentage:" << percentage << endl;
	cout << "max_speed:" << max_speed << endl;

	cout << "program:" << program << endl;
	cout << "arguments:" << max_speed << endl;

	cout << "psexec:" << psexec << endl;
	*/

	if (string(type) == "start") {
		
		if(server_status() == 0) {
			
			if(server_start() != 0) {
				cout << "Server started" << endl;
			} else {
				cout << "Server not started" << endl;
			}
				
		} else {
			cout << "Server is already running" << endl;
		}
		
	} else if (string(type) == "stop") {
		if (server_stop() == 1) {
			cout << "Server stopped" << endl;
		} else {
			cout << "Server not stopped" << endl;
		}
	} else if (string(type) == "restart") {
		
		if (server_status() != 0) {
			server_stop();

			// Sleep
#ifdef _WIN32
			Sleep(2000);
#else
			sleep(2);
#endif
		}

		if (server_start() != 0) {
			cout << "Server restarted" << endl;
		} else {
			cout << "Server not restarted" << endl;
		}

	} else if (string(type) == "status") {
		int pid = server_status();
		
		if(pid != 0) {
			cout << "Server is UP" << endl;
		} else {
			cout << "Server is Down" << endl;
		}
		
	} else if (string(type) == "get_console") {
		// Coming soon
	} else {
		show_help();
	}
}
