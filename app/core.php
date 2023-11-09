<?php

		$load = sys_getloadavg();
		$cpuload = $load[0];
		$cpu_count = shell_exec('nproc');
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
		$connections = `netstat -ntu | grep :80 | grep ESTABLISHED | grep -v LISTEN | awk '{print $5}' | cut -d: -f1 | sort | uniq -c | sort -rn | grep -v 127.0.0.1 | wc -l`; 
		$totalconnections = `netstat -ntu | grep :80 | grep -v LISTEN | awk '{print $5}' | cut -d: -f1 | sort | uniq -c | sort -rn | grep -v 127.0.0.1 | wc -l`; 
        $memusage = round(($memused/$memtotal)*100);		
        $phpload = round(memory_get_usage() / 1000000,2);
        $diskfree = round(disk_free_space(".") / 1000000000);
        $disktotal = round(disk_total_space(".") / 1000000000);
        $diskused = round($disktotal - $diskfree);
        $diskusage = round($diskused/$disktotal*100);
?>
