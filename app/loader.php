<?php

class SystemInfo {
    public function getLoad() {
        $load = sys_getloadavg();
        $cpuLoad = $load[0] / $this->getCPUCount() * 100;
        return $cpuLoad;
    }

    public function getLoadfromTop() {
        exec('top -bn2 | grep "Cpu(s)" | tail -n 1 | awk \'{print $2+$4}\'', $output);
        if (isset($output[0])) {
            return round($output[0], 2);
        }
        return null;
    }

    public function getLoadfromMpstat() {
        $output = shell_exec('mpstat -o JSON 2 1');
        if ($output) {
            $json = json_decode($output, true);
            if (isset($json['sysstat']['hosts'][0]['statistics'][0]['cpu-load'][0]['idle'])) {
                $cpuLoad = 100 - $json['sysstat']['hosts'][0]['statistics'][0]['cpu-load'][0]['idle'];
                return round($cpuLoad, 2);
            }
        }
        return null;
    }

    public function getCPUCount() {
        return (int)shell_exec('nproc');
    }

    public function getMemoryUsage() {
        $free = shell_exec('free');
        $free = (string)trim($free);
        $free_arr = explode("\n", $free);
        $mem = explode(" ", $free_arr[1]);
        $mem = array_filter($mem, function($value) { return ($value !== null && $value !== false && $value !== ''); }); 
        $mem = array_merge($mem); 
        $memtotal = round($mem[1] / 1000000,2);
        $memused = round($mem[2] / 1000000,2);
        $memfree = round($mem[3] / 1000000,2);
        $memshared = round($mem[4] / 1000000,2);
        $memcached = round($mem[5] / 1000000,2);
        $memavailable = round($mem[6] / 1000000,2);
        $memusage = round(($memused/$memtotal)*100);
        return array(
            'total' => $memtotal,
            'used' => $memused,
            'free' => $memfree,
            'shared' => $memshared,
            'cached' => $memcached,
            'available' => $memavailable,
            'usage' => $memusage
        );
    }

    public function getConnections() {
        $connections = `netstat -ntu | grep :80 | grep ESTABLISHED | grep -v LISTEN | awk '{print $5}' | cut -d: -f1 | sort | uniq -c | sort -rn | grep -v 127.0.0.1 | wc -l`; 
        $totalconnections = `netstat -ntu | grep :80 | grep -v LISTEN | awk '{print $5}' | cut -d: -f1 | sort | uniq -c | sort -rn | grep -v 127.0.0.1 | wc -l`; 
        return array(
            'connections' => $connections,
            'totalconnections' => $totalconnections
        );
    }

    public function getAllConnections() {
        $allConnections = trim(shell_exec('netstat -an | grep -c ESTABLISHED'));
        return $allConnections;
    }

    public function getTopProcess() {
        $output = shell_exec('ps aux');
        $lines = explode(PHP_EOL, $output);
        $processes = array();
        foreach ($lines as $line) {
            $tokens = preg_split('/\s+/', trim($line));
            $processes[] = array(
                'name' => $tokens[10],
                'cpu' => $tokens[2]
            );
        }
        usort($processes, function($a, $b) {
            return $b['cpu'] - $a['cpu'];
        });
        $top_processes = array();
        for ($i = 0; $i < 5; $i++) {
            $top_processes[] = array(
                'name' => $processes[$i]['name'],
                'cpu' => $processes[$i]['cpu']
            );
        }
        return $top_processes;
    }

    public function getDiskUsage() {
        $diskfree = round(disk_free_space(".") / 1000000000);
        $disktotal = round(disk_total_space(".") / 1000000000);
        $diskused = round($disktotal - $diskfree);
        $diskusage = round($diskused/$disktotal*100);
        return array(
            'total' => $disktotal,
            'used' => $diskused,
            'free' => $diskfree,
            'usage' => $diskusage
        );
    }
    public function getIOUsage() {
        $IOUsage = shell_exec('iostat -d 1 2 | awk \'FNR == 4 {print $2}\'');
        return $IOUsage;
    } 

    public function getPHPver() {
        return 'PHP version: ' . phpversion();
    } 

    public function getOSdata() {
        return php_uname('s') . ' ' . php_uname('r');
    }    
}

if (isset($_GET['action']) && $_GET['action'] === 'system_info') {
    $system_info = new SystemInfo();
    $load = $system_info->getLoadfromMpstat();
    if ($load === null) {
        $load = $system_info->getLoadfromTop();
    }
    if ($load === null) {
        $load = $system_info->getLoad();
    }
    
    $data = array(
        'load' => $load,
        'memory_usage' => $system_info->getMemoryUsage(),
        'IO_usage' => $system_info->getIOUsage()
    );
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'general') {
    $system_info = new SystemInfo();
    $data = array(
        'cpu_count' => 'Cores: '. $system_info->getCPUCount(),
        'php_ver' => $system_info->getPHPver(),
        'OS_data' => $system_info->getOSdata()
    );
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}
