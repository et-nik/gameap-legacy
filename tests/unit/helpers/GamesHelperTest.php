<?php

class GamesHelperTest extends CodeIgniterTestCase
{
    public function _before()
    {
        $this->load->helper('games');
    }

    public function test_steamid_to_steamid64()
    {
        $this->assertEquals('76561198011856444', steamid_to_steamid64('STEAM_0:0:25795358'));
        $this->assertEquals('76561198092438355', steamid_to_steamid64('STEAM_0:1:66086313'));
        $this->assertEquals('76561197990974651', steamid_to_steamid64('STEAM_0:1:15354461'));
        $this->assertEquals('76561198070763969', steamid_to_steamid64('STEAM_0:1:55249120'));
        $this->assertEquals('76561198115182244', steamid_to_steamid64('STEAM_0:0:77458258'));
    }

    public function test_steamid64_to_steamid()
    {
        $this->assertEquals('STEAM_0:0:25795358', steamid64_to_steamid('76561198011856444'));
        $this->assertEquals('STEAM_0:1:66086313', steamid64_to_steamid('76561198092438355'));
        $this->assertEquals('STEAM_0:1:15354461', steamid64_to_steamid('76561197990974651'));
        $this->assertEquals('STEAM_0:1:55249120', steamid64_to_steamid('76561198070763969'));
        $this->assertEquals('STEAM_0:0:77458258', steamid64_to_steamid('76561198115182244'));
    }
}