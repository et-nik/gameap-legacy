<?php

/*
 * Непонятный, найденный класс на странице 
 * http://marc.info/?l=php-general&m=99394407709109
 * 
 * Какой-то кривой, но как ни странно работает.
 * 
*/

class Telnet extends CI_Model{
	
	/* (c) thies@thieso.net */

    var $sock = NULL;


    function connect($host,$port) 
    {
		$this->sock = fsockopen($host,$port);
		socket_set_timeout($this->sock,2,0);
    }


	function close() 
	{
		if ($this->sock){
            fclose($this->sock);
		}
        
        $this->sock = NULL;
	}


    function write($buffer) {
		$buffer = str_replace(chr(255),chr(255).chr(255),$buffer);
        fwrite($this->sock,$buffer);
    }


	function getc() 
	{
		return fgetc($this->sock);
	}


	function read_till($what) 
	{
		$buf = '';
		
		while (1) {
			$IAC = chr(255);

            $DONT = chr(254);
			$DO = chr(253);

			$WONT = chr(252);
			$WILL = chr(251);

			$theNULL = chr(0);

			$c = $this->getc();

			if ($c === false){
				return $buf;
			}

			if ($c == $theNULL) {
				continue;
			}

			if ($c == "\021") {
				continue;
			}


			if ($c != $IAC) {
				$buf .= $c;

				if ($what ==(substr($buf,strlen($buf)-strlen($what)))) {
					return $buf;
                } else {
					continue;
                }
            }


			$c = $this->getc();


			if ($c == $IAC) {
				$buf .= $c;
			} else if (($c == $DO) || ($c == $DONT)) {
				$opt = $this->getc();
				// echo "we wont ".ord($opt)."\n";
				fwrite($this->sock,$IAC.$WONT.$opt);
			} elseif (($c == $WILL) || ($c == $WONT)) {
				$opt = $this->getc();
				// echo "we dont ".ord($opt)."\n";
				fwrite($this->sock,$IAC.$DONT.$opt);
			} else {
				// echo "where are we? c=".ord($c)."\n";
			}
			
		// Конец while
		}

	}
}
