<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mds_Stats extends CI_Model {

    private $_filter_time_between = array();

    // -----------------------------------------------------------------

    function time_between($time_start, $time_end)
    {
        $this->_filter_time_between = array($time_start, $time_end);
    }

    // -----------------------------------------------------------------

    /**
     *
     */
    function get_stats($ds_id = 0) 
    {
        $this->load->helper('date');
        if ($ds_id == 0) {
            return false;
        }

        // $ret_stats = array();
        
        $this->db->select("ds_stats.time, ds_stats.loa, ds_stats.ram, ds_stats.cpu, ds_stats.ifstat, ds_stats.ping, ds_stats.drvspace, dedicated_servers.name");
        $this->db->from('ds_stats');
        $this->db->join('dedicated_servers', 'dedicated_servers.id = ds_stats.ds_id', 'left');

        $this->db->where('ds_stats.ds_id', $ds_id);

        if (!empty($this->_filter_time_between[0]) && !empty($this->_filter_time_between[1])) {
            $this->db->where("`time` BETWEEN {$this->_filter_time_between[0]} AND {$this->_filter_time_between[1]}");
            $this->_filter_time_between = array();
        }
        
        $query = $this->db->get();

        $i = 0;
        if ($query->num_rows() == 0) {
            return array();
        }
        
        $results = $query->result_array();
        
        $cpu_count = count(array_filter(explode(' ', $results[0]['cpu'])));
        $ram_total = explode(' ', $results[0]['ram'])[2]/1024;
        
        foreach ($results as &$arr) {

            // print_r($arr);
            
            // $ret_stats[$i] = array(
                // 'human_time' => $arr['time'],
                // 'loa' => explode(' ' , $arr['loa']),
                // 'ping' => $arr['ping'],
            // );

            $ret_stats['time'][$i] = date('H:i', $arr['time']);
            $ret_stats['date'][$i] = unix_to_human($arr['time']);
            $ret_stats['timestamp'][$i] = $arr['time'];
            $ret_stats['loa'][$i] = $arr['loa'];
            $ret_stats['ping'][$i] = $arr['ping'];

            // Cpu
            $j = 0;
            foreach (explode(' ', $arr['cpu']) as $cpu) {
                if ($cpu == "") continue;
                $ret_stats['cpu'][$j][$i] = $cpu;
                $j++;
            }
            
            // Ram Mib
            $ramarr = explode(' ', $arr['ram']);
            $ret_stats['ram'][$i]   = round($ramarr[0]/1024, 0, PHP_ROUND_HALF_UP);
            $ret_stats['ramav'][$i] = round(($ramarr[0]-$ramarr[1])/1024, 0, PHP_ROUND_HALF_UP);
            
            // Ifstat
            foreach (explode("\n", $arr['ifstat']) as $ifst) {
                if ($ifst == '') continue;
                
                $ifst_interf = explode(' ', $ifst);

                $ret_stats['if_stat'][$ifst_interf[0]]['rxb'][$i] = $ifst_interf[1]/1024;
                $ret_stats['if_stat'][$ifst_interf[0]]['txb'][$i] = $ifst_interf[2]/1024;
                $ret_stats['if_stat'][$ifst_interf[0]]['rxp'][$i] = $ifst_interf[3]/1024;
                $ret_stats['if_stat'][$ifst_interf[0]]['txp'][$i] = $ifst_interf[4]/1024;
            }

            // DrvSpace
            foreach (explode("\n", $arr['drvspace']) as $drv) {
                if ($drv == '') continue;
                
                $drv_sp = explode(' ', $drv);
                $ret_stats['drvspace'][$drv_sp[0]][$i] = round($drv_sp[1]/(1024*1024), 0, PHP_ROUND_HALF_UP);

                isset($ret_stats['drvspace_total'][$drv_sp[0]])
                    OR $ret_stats['drvspace_total'][$drv_sp[0]] = round($drv_sp[2]/(1024*1024), 0, PHP_ROUND_HALF_UP);;
            }
            
            $i++;
        }
        
        $ret_stats['ds_name']       = $arr['name'];
        $ret_stats['ram_total']     = round($ram_total, 0, PHP_ROUND_HALF_UP);
        $ret_stats['cpu_count']     = $cpu_count;

        return $ret_stats;
	}

    // -----------------------------------------------------------------

    function get_all_stats() 
    {
        // $this->db->select('ds_stats.ds_id, ds_stats.time, ds_stats.loa, ds_stats.ram, ds_stats.cpu, ds_stats.ifstat, ds_stats.ping, ds_stats.drvspace, dedicated_servers.name');
        // $this->db->from('ds_stats');
        // $this->db->group_by('ds_stats.ds_id');
        // $this->db->join('dedicated_servers', 'dedicated_servers.id = ds_stats.ds_id', 'left');
        // $this->db->order_by('ds_stats.time', 'DESC');

        $ds_stats_tb = $this->db->dbprefix('ds_stats');
        $ds_tb = $this->db->dbprefix('dedicated_servers');

        $query = $this->db->query("
            SELECT * FROM (
                SELECT
                    `{$ds_stats_tb}`.`id`,
                    `{$ds_stats_tb}`.`ds_id`,
                    `{$ds_stats_tb}`.`time`,
                    `{$ds_stats_tb}`.`loa`,
                    `{$ds_stats_tb}`.`ram`,
                    `{$ds_stats_tb}`.`cpu`,
                    `{$ds_stats_tb}`.`ifstat`,
                    `{$ds_stats_tb}`.`ping`,
                    `{$ds_stats_tb}`.`drvspace`,
                    `{$ds_tb}`.`name`
                FROM `{$ds_stats_tb}`
                LEFT JOIN `{$ds_tb}`
                    ON `{$ds_tb}`.`id` = `{$ds_stats_tb}`.`ds_id`
                ORDER BY `{$ds_stats_tb}`.`time` DESC
                LIMIT 99999
            ) AS t GROUP BY `ds_id`"
        );

        $results = $query->result_array();
        
        $ret_stats = array();
        $i = 0;
        foreach ($results as &$arr) {
            $ret_stats[$i]['ds_id']     = $arr['ds_id'];
            $ret_stats[$i]['ds_name']   = $arr['name'];
            $ret_stats[$i]['time']      = date('H:i', $arr['time']);
            $ret_stats[$i]['date']      = unix_to_human($arr['time']);
            $ret_stats[$i]['timestamp'] = $arr['time'];

            $ret_stats[$i]['loa'] = $arr['loa'];

            // Cpu
            $j = 0;
            foreach (explode(' ', $arr['cpu']) as $cpu) {
                if ($cpu == "") continue;
                $ret_stats[$i]['cpu'][$j] = $cpu;
                $j++;
            }
            
            // Ram Mib
            $ramarr = explode(' ', $arr['ram']);
            $ret_stats[$i]['ram']   = round($ramarr[0]/1024, 0, PHP_ROUND_HALF_UP);
            $ret_stats[$i]['ramav'] = round(($ramarr[0]-$ramarr[1])/1024, 0, PHP_ROUND_HALF_UP);
            $ret_stats[$i]['ramtot'] = round(($ramarr[2])/1024, 0, PHP_ROUND_HALF_UP);
            
            // Ifstat
            foreach (explode("\n", $arr['ifstat']) as $ifst) {
                if ($ifst == '') continue;
                
                $ifst_interf = explode(' ', $ifst);

                $ret_stats[$i]['if_stat'][$ifst_interf[0]]['rxb'] = $ifst_interf[1]/1024;
                $ret_stats[$i]['if_stat'][$ifst_interf[0]]['txb'] = $ifst_interf[2]/1024;
                $ret_stats[$i]['if_stat'][$ifst_interf[0]]['rxp'] = $ifst_interf[3]/1024;
                $ret_stats[$i]['if_stat'][$ifst_interf[0]]['txp'] = $ifst_interf[4]/1024;
            }

            // DrvSpace
            foreach (explode("\n", $arr['drvspace']) as $drv) {
                if ($drv == '') continue;
                
                $drv_sp = explode(' ', $drv);
                $ret_stats[$i]['drvspace'][$drv_sp[0]][0] = round($drv_sp[1]/(1024*1024), 0, PHP_ROUND_HALF_UP);
                $ret_stats[$i]['drvspace'][$drv_sp[0]][1] = round($drv_sp[2]/(1024*1024), 0, PHP_ROUND_HALF_UP);
            }
            $i++;
        }

        return $ret_stats;
    }
}
