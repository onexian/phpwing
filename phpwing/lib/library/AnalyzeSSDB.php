<?php
/**
 * User: wx
 * Date: 2019/5/13
 * Time: 11:08
 *
 * Catch the tail of time
 */

namespace lib\library;

class AnalyzeSSDB
{
    private $host;

    private $port;
    /**
     * @var \ssdb
     */
    private $ssdb;

    /**
     * @var array
     */
    private $report = [];

    private $type = 's';
    
    public function __construct($conf = 'master')
    {

        $config = Config::get('database.ssdb');
        $config = $config[$conf] ?? [];

        $host = $config['host'];
        $port = $config['port'];
        $password = $config['auth'];

        $this->host = $host;
        $this->port = $port;
        $this->ssdb = new Ssdb($conf);

        $usePassword = strlen($password) > 0;
        if ($usePassword) {
            $this->ssdb->auth($password);
        }
    }

    public function getDatabases()
    {
        $keyspace = $this->ssdb->info('leveldb');
        $databases = [];
        foreach ($keyspace as $db => $space) {
            $databases[(int)substr($db, 2)] = $space;
        }
        return $databases;
    }

    /**
     * @param string $type check type
     * @param string $start prefix
     * @param bool $end is only prefix ? false for all
     * @param int $limit every time limit
     */
    public function start($type = 's', $start = '', $end = false, $limit = 1000)
    {
        $this->type = $type; // this type
        $databases = $this->getDatabases(); // link effectiveness ?

        p($databases);
        $oldStartStr = $start; // record old stsrt key
        foreach ($databases as $db => $keyspace) {

            $this->report[$db] = [];

            while(1){

                $endStr = $end?$oldStartStr.'}':'';

                switch($type){
                    case 's':
                        $keys = $this->ssdb->scan($start, $endStr, $limit);
                        break;
                    case 'h':

                        $keys = $this->ssdb->hlist($start, $endStr, $limit);
                        if($keys){
                            $keys = array_flip($keys);
                        }
                        break;
                    default:

                        $keys = $this->ssdb->scan($start, $endStr, $limit);
                        break;
                }

                if(!$keys){
                    break;
                }

                if ($keys) {
                    foreach ($keys as $key => $val) {

                        $countKey = null;
                        if($oldStartStr){
                            $pieces = explode($oldStartStr, $key);
                        }else{
                            $pieces[0] = $key;
                        }

                        if($pieces[0]){

                            if($prefix = strstr($key, '#', true)){
                                $countKey = $prefix . '#*';
                            }elseif($prefix = strstr($key, '-', true)){
                                $countKey = $prefix . '-*';
                            }else{
                                $countKey = $pieces[0];
                            }

                        }else{
                            $countKey = $pieces[0] . $oldStartStr . '*';
                        }
                        if (!$countKey) {
                            continue;
                        }

                        if (!isset($this->report[$db][$countKey])) {
                            $this->report[$db][$countKey] = [
                                'count'       => 0,//total count
                                'key_size'    => 0,//total key size
                                'size'        => 0,//total size
                                'neverExpire' => 0,//the count of never expired keys
                                'avgTtl'      => 0,//the average ttl of the going to be expired keys
                            ];
                        }

                        switch($type){
                            case 's':

                                $size = $this->ssdb->countbit($key,0,1);
                                $ttl = $this->ssdb->ttl($key);

                                break;
                            case 'h':

                                // get all hashname data ,strlen() by keys+values
                                $allData = $this->ssdb->hgetall($key);
                                $size = 0;
                                foreach ($allData as $_k=>$_v) {
                                    $size += strlen("{$_k}{$_v}");
                                }
                                $ttl = $this->ssdb->ttl($key);
                                break;
                            default:
                                $size = $this->ssdb->countbit($key,0,1);
                                $ttl = $this->ssdb->ttl($key);
                                break;
                        }

                        $this->report[$db][$countKey]['size'] += $size; // value size
                        $this->report[$db][$countKey]['key_size'] += strlen($key); // key size


                        if ($ttl) {
                            if ($ttl != -2) {//-2: expired or not exist
                                if ($ttl == -1) {//-1: never expire
                                    ++$this->report[$db][$countKey]['neverExpire'];
                                } else {
                                    if ($this->report[$db][$countKey]['count'] > 0) {
                                        $avgCount = $this->report[$db][$countKey]['count'] - $this->report[$db][$countKey]['neverExpire'];
                                        $totalTtl = $this->report[$db][$countKey]['avgTtl'] * $avgCount + $ttl;
                                        $this->report[$db][$countKey]['avgTtl'] = $totalTtl / ($avgCount + 1);
                                    } else {
                                        $this->report[$db][$countKey]['avgTtl'] = $ttl;
                                    }
                                }

                                ++$this->report[$db][$countKey]['count'];
                            }
                        }

                    }
                    usleep(50);
                }

                // do something on key-value pairs...
                $allKeys = array_keys(array_slice($keys, -1, 1, true));
                $max_key = $allKeys[0];
                $start = $max_key;
            }

            uasort($this->report[$db], function ($a, $b) {
                if ($a['size'] > $b['size']) {
                    return -1;
                } elseif ($a['size'] < $b['size']) {
                    return 1;
                } else {
                    if ($a['count'] > $b['count']) {
                        return -1;
                    } elseif ($a['count'] < $b['count']) {
                        return 1;
                    } else {
                        return 0;
                    }
                }
            });
        }
    }

    public function getReport()
    {
        return $this->report;
    }

    public function saveReport($folder = null)
    {
        $folder = $folder ? (rtrim($folder, '/') . '/') : './reports';

        if (!is_dir($folder)) {
            if (!mkdir($folder, 0777, true)) {
                throw new \Exception('mkdir failed: ' . $folder);
            }
        }

        foreach ($this->report as $db => $report) {
            $filename = sprintf('ssdb-analysis-%s-%d-%s-%s.csv', $this->host, $this->port, $this->type, date('YmdHis'));
            $filename = $folder . '/' . $filename;

            $fp = fopen($filename, 'w');
            fwrite($fp, 'Key,Count,KeySize,Size,NeverExpire,AvgTtl(excluded never expire)' . PHP_EOL);
            foreach ($report as $key => $keyStat) {
                $humanKeySize = $this->toHumanSize($keyStat['key_size']);
                $humanSize = $this->toHumanSize($keyStat['size']);
                fwrite($fp, sprintf('%s,%d,%s,%s,%d,%s',
                    $key,
                    $keyStat['count'],
                    implode(' ', $humanKeySize),
                    implode(' ', $humanSize),
                    round($keyStat['neverExpire']),
                    round($keyStat['avgTtl']) . PHP_EOL
                ));
            }
            fclose($fp);
        }

        return true;
    }

    protected function toHumanSize($bytes)
    {
        $units = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB'];
        for ($i = 0; $bytes >= 1024; $i++) {
            $bytes /= 1024;
        }
        return [round($bytes, 3), $units[$i]];
    }
    
}