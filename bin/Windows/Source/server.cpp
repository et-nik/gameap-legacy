#include <stdio.h>
#include <iostream>
#include <cstring>
#include <sstream>

#include <string>

#include <map>

#include <jsoncpp/json/json.h>

#include <boost/regex.hpp>
#include <boost/format.hpp>
#include <boost/algorithm/string.hpp>

#ifdef _WIN32
#include <windows.h>
#else
#include <unistd.h>
#endif

#include "functions.h"

// using namespace std;

std::string type 		= "help";
std::string dir = "";
std::string screen_name = "";
std::string ip = "0.0.0.0";
std::string port = "";
std::string command = "";
std::string user = "";
std::string memory = "";
std::string percentage = "";
std::string max_speed = "";

std::string program = "";
std::string arguments = "";

std::string psexec = "";

// ---------------------------------------------------------------------

void show_help()
{
	std::cout << "**************************************\n";
	std::cout << "* Welcome to GameAP Console Launcher *\n";
	std::cout << "**************************************\n\n";
		
	std::cout << "Program created by ET-NiK \n";
	std::cout << "Site: http://www.gameap.ru \n\n";
	
	std::cout << "Parameters\n";
	std::cout << "-t <type>	(start|stop|restart|status|get_console|send_command)\n";
	std::cout << "-d <dir>	base directory\n";
	std::cout << "-n <screen_name> name screen\n";
	std::cout << "-i <ip>\n";
	std::cout << "-p <port>\n";
	std::cout << "-c <command>  command (example 'hlds.exe -game valve +ip 127.0.0.1 +port 27015 +map crossfire')\n";
	std::cout << "-u <user>\n";
	std::cout << "-m <memory>	 RAM Limit (Kb)\n";
	std::cout << "-f <percentage>	 CPU Limit (%)\n";
	std::cout << "-s <max speed>	 NET Limit (Kb/s)\n\n";

	std::cout << "Examples:\n";
	std::cout << "./server.sh -t start -d /home/hl_server -n screen_hldm -i 127.0.0.1 -p 27015 -c \"hlds.exe -game valve +ip 127.0.0.1 +port 27015 +map crossfire\"\n";
	std::cout << "./server.sh -t get_console -n hldm -u usver\n";
}

// ---------------------------------------------------------------------

std::string cpu_affinity()
{
	int cores = get_cores_count();

	std::vector<std::string> vcores;
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
	
#ifdef _WIN32
	// Windows
	std::string output = exec(str(boost::format("wmic process where description=\"%s\" get executablepath, processid") % program));
	std::vector<std::string> expl = explode("\n", output);
	
	for (int i = 1; i < expl.size(); i++) {
		boost::regex xregex("\\s+");
		std::string str = boost::regex_replace(expl[i], xregex, " ", boost::match_default | boost::format_perl);
		
		std::vector<std::string> expl_str = explode(" ", str);
		
		expl_str[0] = expl_str[0].substr(0, expl_str[0].length()-program.length()-1);
		
		if (expl_str.size() > 1 && expl_str[0] == dir) {
			pid = stoi(expl_str[1]);
			break;
		}
	}
#else
	// Linux
	//string output = exec();
#endif
	
	if (pid) {
		std::string output = exec(str(boost::format("echo %d > %s/pid.txt") % pid % dir));
	} else {
		std::string output = exec(str(boost::format("echo NOT FOUND > %s/pid.txt") % dir));
	}
	
	return pid;
}

// ---------------------------------------------------------------------

int server_start()
{
	std::cout << "PROGRAM:" << program << std::endl;
	fast_exec(str(boost::format("%s \"%s\\%s\" %s") % psexec % dir % program % arguments));

	std::cout << "CmdLine: " << str(boost::format("%s \"%s\\%s\" %s") % psexec % dir % program % arguments) << std::endl;

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

int main(int argc, char *argv[]) 
{	
	//setlocale(LC_CTYPE, "rus");

	for (int i = 0; i < argc-1; i++) {

		if (std::string(argv[i]) == "-t") {
			// Тип
			type = argv[i+1];
			i++;
		} 
		else if (std::string(argv[i]) == "-d") {
			// Директория
			dir = argv[i+1];
			i++;
		} 
		else if (std::string(argv[i]) == "-n") {
			// Screen name
			screen_name = argv[i+1];
			i++;
		}
		else if (std::string(argv[i]) == "-i") {
			// IP
			ip = argv[i+1];
			i++;
		}
		else if (std::string(argv[i]) == "-p") {
			// Port
			port = argv[i+1];
			i++;
		}
		else if (std::string(argv[i]) == "-c") {
			// Start Command
			command = argv[i+1];
			i++;
		}
		else if (std::string(argv[i]) == "-u") {
			// User
			user = argv[i+1];
			i++;
		}
		else if (std::string(argv[i]) == "-m") {
			// Memory
			memory = argv[i+1];
			i++;
		}
		else if (std::string(argv[i]) == "-f") {
			// CPU
			percentage = argv[i+1];
			i++;
		}
		else if (std::string(argv[i]) == "-s") {
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
		psexec = str( boost::format("psexec.exe -accepteula -s -i -d -w \"%s\" -a %s ") % dir % cpu_affinity() );
	} else if(file_exists("paexec.exe")) {
		psexec = str( boost::format("paexec.exe \\\\localhost -s -d -w \"%s\" -a %s") % dir % cpu_affinity() );
	} else if(file_exists("screen.exe")) {
		psexec = str( boost::format("screen -t start -S %s -d %s -c") % screen_name % dir );
	} else {
		std::cout << "psexec.exe, paexec.exe and screen.exe not found" << std::endl;
		psexec = str( boost::format("start /D \"%s\" /I ") % dir );
	}
    
    /* Разъединение программы и аргумента в команде запуска сервера */
	std::vector<std::string> explodes = explode(" ", command);

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

	if (std::string(type) == "start") {
		
		if(server_status() == 0) {
			
			if(server_start() != 0) {
				std::cout << "Server started" << std::endl;
			} else {
				std::cout << "Server not started" << std::endl;
			}
				
		} else {
			std::cout << "Server is already running" << std::endl;
		}
		
	} 
	else if (std::string(type) == "stop" || std::string(type) == "kill") {
		Sleep(3000);
		if (server_stop() == 1) {
			std::cout << "Server stopped" << std::endl;
		} else {
			std::cout << "Server not stopped" << std::endl;
		}
	} 
	else if (std::string(type) == "restart") {
		
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
			std::cout << "Server restarted" << std::endl;
		} else {
			std::cout << "Server not restarted" << std::endl;
		}

	} 
	else if (std::string(type) == "status") {
		int pid = server_status();
		
		if(pid != 0) {
			std::cout << "Server is UP" << std::endl;
		} else {
			std::cout << "Server is Down" << std::endl;
		}
		
	}
	else if (std::string(type) == "get_console") {
		std::string output = "";
#ifdef _WIN32
		output = exec(str(boost::format("screen -t get_console -S %s") % screen_name));
#else

#endif
		// Вывод команды
		std::cout << output << std::endl;
	}
	else if (std::string(type) == "send_command") {
		std::string output = "";
#ifdef _WIN32
		output = exec(str(boost::format("screen -t get_console -S %s -c %s") % screen_name % command));
#else

#endif
		// Вывод команды
		std::cout << output << std::endl;
	}
	else {
		show_help();
	}
}
