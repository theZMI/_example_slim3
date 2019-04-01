<?php

    /**
     * Model with simple cache for CallUrl function
     *
     * @author ZMI
     */
    abstract class ModelWithExternalCall
    {
        protected $isGetOnlyFromCache = false;
        protected $ttlConfs           = [];

        
        abstract protected function GetTtlByFuncName($funcName);

        public function __construct($fromCacheOnly)
        {
            $this->isGetOnlyFromCache = $fromCacheOnly;
        }
        
        protected function CallUrl($url, $funcName, $funcIsValidAnswer = NULL)
        {
            $ret   = '';
            $cache = new SimpleCache($funcName);
            if ($this->isGetOnlyFromCache)
            {
                $ret = $cache->GetForce();
            }
            else
            {
                $ret = $cache->Get();
                if (empty($ret))
                {
                    $ret = CallUrl($url);
                    if ($ret)
                    {
                        if (!is_null($funcIsValidAnswer))
                        {
                            $isValid = call_user_func_array($funcIsValidAnswer, [$ret]);
                            if (!$isValid)
                            {
                                throw new Exception("Not valid answer from url={$url}, answer={$ret}");
                            }
                        }
                        $cache->Set($ret, $this->GetTtlByFuncName($funcName));
                    }
                    else
                    {
                        throw new Exception("Can't call url={$url}");
                    }
                }
            }
            return $ret;
        }
        
        public static function GetLastUpdatedTime()
        {
            return SimpleCache::GetLastUpdatedTime();
        }
    };