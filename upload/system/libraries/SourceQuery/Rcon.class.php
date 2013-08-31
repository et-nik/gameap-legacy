<?php
	/**
	 * Class written by xPaw
	 *
	 * Website: http://xpaw.ru
	 * GitHub: https://github.com/xPaw/PHP-Source-Query-Class
	 */
	
	class SourceQueryRcon
	{
		/**
		 * Points to buffer class
		 * 
		 * @var SourceQueryBuffer
		 */
		private $Buffer;
		
		/**
		 * Points to socket class
		 * 
		 * @var SourceQuerySocket
		 */
		private $Socket;
		
		private $RconSocket;
		private $RconPassword;
		private $RconRequestId;
		private $RconChallenge;
		
		public function __construct( $Buffer, $Socket )
		{
			$this->Buffer = $Buffer;
			$this->Socket = $Socket;
		}
		
		public function Close( )
		{
			if( $this->RconSocket )
			{
				FClose( $this->RconSocket );
				
				$this->RconSocket = null;
			}
			
			$this->RconChallenge = 0;
			$this->RconRequestId = 0;
			$this->RconPassword  = 0;
		}
		
		public function Open( )
		{
			if( !$this->RconSocket && $this->Socket->Engine == CI_SourceQuery :: SOURCE )
			{
				$this->RconSocket = @FSockOpen( $this->Socket->Ip, $this->Socket->Port, $ErrNo, $ErrStr, $this->Socket->Timeout );
				
				if( $ErrNo || !$this->RconSocket )
				{
					throw new SourceQueryException( 'Can\'t connect to RCON server: ' . $ErrStr );
				}
				
				Stream_Set_Timeout( $this->RconSocket, $this->Socket->Timeout );
				Stream_Set_Blocking( $this->RconSocket, true );
			}
		}
		
		public function Write( $Header, $String = '' )
		{
			switch( $this->Socket->Engine )
			{
				case CI_SourceQuery :: GOLDSOURCE:
				{
					$Command = Pack( 'cccca*', 0xFF, 0xFF, 0xFF, 0xFF, $String );
					$Length  = StrLen( $Command );
					
					return $Length === FWrite( $this->Socket->Socket, $Command, $Length );
				}
				case CI_SourceQuery :: SOURCE:
				{
					// Pack the packet together
					$Command = Pack( 'VV', ++$this->RequestId, $Header ) . $String . "\x00\x00\x00"; 
					
					// Prepend packet length
					$Command = Pack( 'V', StrLen( $Command ) ) . $Command;
					$Length  = StrLen( $Command );
					
					return $Length === FWrite( $this->RconSocket, $Command, $Length );
				}
			}
		}
		
		public function Read( $Length = 1400 )
		{
			switch( $this->Socket->Engine )
			{
				case CI_SourceQuery :: GOLDSOURCE:
				{
					// GoldSource RCON has same structure as Query
					$this->Socket->Read( );
					
					if( $this->Buffer->GetByte( ) != SourceQuery :: S2A_RCON )
					{
						return false;
					}
					
					$Buffer  = $this->Buffer->Get( );
					$Trimmed = Trim( $Buffer );
					
					if( $Trimmed == 'Bad rcon_password.'
					||  $Trimmed == 'You have been banned from this server.' )
					{
						throw new SourceQueryException( $Trimmed );
					}
					
					$ReadMore = false;
					
					// There is no indentifier of the end, so we just need to continue reading
					// TODO: Needs to be looked again, it causes timeouts
					do
					{
						$this->Socket->Read( );
						
						$ReadMore = $this->Buffer->Remaining( ) > 0 && $this->Buffer->GetByte( ) == SourceQuery :: S2A_RCON;
						
						if( $ReadMore )
						{
							$Packet  = $this->Buffer->Get( );
							$Buffer .= SubStr( $Packet, 0, -2 );
							
							// Let's assume if this packet is not long enough, there are no more after this one
							$ReadMore = StrLen( $Packet ) > 1000; // use 1300?
						}
					}
					while( $ReadMore );
					
					$this->Buffer->Set( Trim( $Buffer ) );
					
					break;
				}
				case CI_SourceQuery :: SOURCE:
				{
					$this->Buffer->Set( FRead( $this->RconSocket, $Length ) );
					
					$Buffer = "";
					
					$PacketSize = $this->Buffer->GetLong( );
					
					$Buffer .= $this->Buffer->Get( );
					
					// TODO: multi packet reading
					
					$this->Buffer->Set( $Buffer );
					
					break;
				}
			}
		}
		
		public function Command( $Command )
		{
			if( !$this->RconChallenge )
			{
				return false;
			}
			
			$Buffer = false;
			
			switch( $this->Socket->Engine )
			{
				case CI_SourceQuery :: GOLDSOURCE:
				{
					$this->Write( 0, 'rcon ' . $this->RconChallenge . ' "' . $this->RconPassword . '" ' . $Command . "\0" );
					$this->Read( );
					
					$Buffer = $this->Buffer->Get( );
					
					break;
				}
				case CI_SourceQuery :: SOURCE:
				{
					$this->Write( CI_SourceQuery :: SERVERDATA_EXECCOMMAND, $Command );
					$this->Read( );
					
					$RequestID = $this->Buffer->GetLong( );
					$Type      = $this->Buffer->GetLong( );
					
					if( $Type == CI_SourceQuery :: SERVERDATA_AUTH_RESPONSE )
					{
						throw new SourceQueryException( 'Bad rcon_password.' );
					}
					else if( $Type != CI_SourceQuery :: SERVERDATA_RESPONSE_VALUE )
					{
						return false;
					}
					
					// TODO: It should use GetString, but there are no null bytes at the end, why?
					// $Buffer = $this->Buffer->GetString( );
					$Buffer = $this->Buffer->Get( );
					
					break;
				}
			}
			
			return $Buffer;
		}
		
		public function Authorize( $Password )
		{
			$this->RconPassword = $Password;
			
			switch( $this->Socket->Engine )
			{
				case CI_SourceQuery :: GOLDSOURCE:
				{
					$this->Write( 0, 'challenge rcon' );
					$this->Socket->Read( );
					
					if( $this->Buffer->Get( 14 ) != 'challenge rcon' )
					{
						return false;
					}
					
					$this->RconChallenge = Trim( $this->Buffer->Get( ) );
					
					break;
				}	
				case CI_SourceQuery :: SOURCE:
				{
					$this->Write( CI_SourceQuery :: SERVERDATA_AUTH, $Password );
					$this->Read( );
					
					$RequestID = $this->Buffer->GetLong( );
					$Type      = $this->Buffer->GetLong( );
					
					// If we receive SERVERDATA_RESPONSE_VALUE, then we need to read again
					// More info: https://developer.valvesoftware.com/wiki/Source_RCON_Protocol#Additional_Comments
					
					if( $Type == CI_SourceQuery :: SERVERDATA_RESPONSE_VALUE )
					{
						$this->Read( );
						
						$RequestID = $this->Buffer->GetLong( );
						$Type      = $this->Buffer->GetLong( );
					}
					
					if( $RequestID == -1 || $Type != CI_SourceQuery :: SERVERDATA_AUTH_RESPONSE )
					{
						throw new CI_SourceQueryException( 'RCON authorization failed.' );
					}
					
					$this->RconChallenge = 1;
					
					break;
				}
			}
			
			return true;
		}
	}
