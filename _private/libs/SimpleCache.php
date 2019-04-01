<?php

    /**
     * Cache by files
     *
     * @author ZMI
     */
    class SimpleCache
    {
        const IS_ACTIVE = true;

        private $cacheFile = '';

        
        public static function GetCacheDir()
        {
            return BASEPATH . '_private/cache/';
        }
        
        public static function GetLastUpdatedTime()
        {
            $path = self::GetCacheDir();
            if (!is_readable($path))
            {
                throw new Exception("Not readble cache directory");
            }
            $dir = new SplFileInfo($path);
            return $dir->getMTime();
        }

        public function __construct($key)
        {
            $this->cacheFile = self::GetCacheDir() . md5($key);
        }

        public function Set($data, $ttl = -1)
        {
            if (self::IS_ACTIVE == false)
            {
                return false;
            }

            $cache =
            [
                'data'        => $data,
                'ttl'         => $ttl,
                'create_time' => time()
            ];
            $ret = FileSys::WriteFile($this->cacheFile, serialize($cache));
            return $ret;
        }

        public function Has()
        {
            if (self::IS_ACTIVE == false)
            {
                return false;
            }

            $file = $this->cacheFile;
            $ret  = is_readable($file);
            if ($ret)
            {
                $t   = unserialize(FileSys::ReadFile($file));
                $ttl = $t['ttl'];
                if ($ttl != -1)
                {
                    $isExpired = (time() - $t['create_time']) > $t['ttl'];
                    $ret       = $isExpired ? false : true;
                }
            }
            return $ret;
        }

        public function Get()
        {
            $ret = NULL;
            if ($this->Has())
            {
                $t    = unserialize(FileSys::ReadFile($this->cacheFile));
                $ret  = $t['data'];
            }
            return $ret;
        }
        
        public function GetForce()
        {
            if (!is_readable($this->cacheFile))
            {
                throw new Exception(sprintf("Not readble cache cacheFile %s", $this->cacheFile));
            }
            $t   = unserialize(FileSys::ReadFile($this->cacheFile));
            $ret = $t['data'];
            return $ret;
        }
    };