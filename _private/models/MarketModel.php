<?php

    /**
     * Model for get market data from others api
     *
     * @author ZMI
     */
    class MarketModel extends ModelWithExternalCall
    {
        const MAX_MARKET_DATA_TTL = 600;
        
        
        public function __construct($fromCacheOnly)
        {
            parent::__construct($fromCacheOnly);
        }

        protected function GetTtlByFuncName($funcName)
        {
            $ttlConfs =
            [
                'GetFromCexIO'          => 4,
                'GetFromExmoCom'        => 2,
                'GetFromLiveCoinNet'    => 2,
                'GetFromYoBitNet'       => 2,
                'GetFromCrex24Com'      => 2,
                'GetFromDskUk'          => 4
            ];
            return $ttlConfs[$funcName];
        }

        public function GetFromCexIO()
        {
            try
            {
                $data = $this->CallUrl
                (
                    "https://cex.io/api/ticker/BTC/RUB",
                    __FUNCTION__,
                    function ($answer)
                    {
                        $json = json_decode($answer);
                        return isset($json->timestamp);
                    }
                );
                $json = json_decode($data);
                $ret  =
                [
                    'last_price'    => $json->last,
                    'ask'           => $json->ask,
                    'bid'           => $json->bid,
                    'volume'        => $json->volume,
                    'upd_time'      => $json->timestamp
                ];
            }
            catch (Exception $e)
            {
                $ret =
                [
                    'last_price'    => 0,
                    'ask'           => 0,
                    'bid'           => 0,
                    'volume'        => 0,
                    'upd_time'      => 0,
                    'error'         => $e->getMessage()
                ];
            }
            return $ret;
        }
        
        public function GetFromExmoCom()
        {
            try
            {
                $data = $this->CallUrl
                (
                    "https://api.exmo.com/v1/ticker/",
                    __FUNCTION__,
                    function ($answer)
                    {
                        $json = json_decode($answer);
                        return isset($json->BTC_RUB->last_trade);
                    }
                );
                $json = json_decode($data);
                $json = $json->BTC_RUB;
                $ret  =
                [
                    'last_price'    => $json->last_trade,
                    'ask'           => $json->buy_price,
                    'bid'           => $json->sell_price,
                    'volume'        => $json->vol,
                    'upd_time'      => $json->updated
                ];
            }
            catch (Exception $e)
            {
                $ret =
                [
                    'last_price'    => 0,
                    'ask'           => 0,
                    'bid'           => 0,
                    'volume'        => 0,
                    'upd_time'      => 0,
                    'error'         => $e->getMessage()
                ];
            }
            return $ret;
        }

        public function GetFromLiveCoinNet()
        {
            try
            {
                $data = $this->CallUrl
                (
                    "https://api.livecoin.net/exchange/ticker?currencyPair=BTC/RUR",
                    __FUNCTION__,
                    function ($answer)
                    {
                        $json = json_decode($answer);
                        return isset($json->last);
                    }
                );
                $json = json_decode($data);
                $ret  =
                [
                    'last_price'    => $json->last,
                    'ask'           => $json->best_ask,
                    'bid'           => $json->best_bid,
                    'volume'        => $json->volume,
                    'upd_time'      => time()
                ];
            }
            catch (Exception $e)
            {
                $ret =
                [
                    'last_price'    => 0,
                    'ask'           => 0,
                    'bid'           => 0,
                    'volume'        => 0,
                    'upd_time'      => 0,
                    'error'         => $e->getMessage()
                ];
            }
            return $ret;
        }
        
        public function GetFromYoBitNet()
        {
            try
            {
                $data = $this->CallUrl
                (
                    "https://yobit.net/api/3/ticker/btc_rur",
                    __FUNCTION__,
                    function ($answer)
                    {
                        $json = json_decode($answer);
                        return isset($json->btc_rur->last);
                    }
                );
                //$data = '{"btc_rur":{"high":232948.99999999,"low":230001,"avg":231474.99999999,"vol":4852843.91056689,"vol_cur":21.06839311,"last":230001,"buy":230001.00000000,"sell":230500.00000000,"updated":1549284107}}';
                $json = json_decode($data);
                $json = $json->btc_rur;
                $ret  =
                [
                    'last_price'    => $json->last,
                    'ask'           => $json->buy,
                    'bid'           => $json->sell,
                    'volume'        => $json->vol_cur,
                    'upd_time'      => $json->updated
                ];
            }
            catch (Exception $e)
            {
                $ret =
                [
                    'last_price'    => 0,
                    'ask'           => 0,
                    'bid'           => 0,
                    'volume'        => 0,
                    'upd_time'      => 0,
                    'error'         => $e->getMessage()
                ];
            }
            return $ret;
        }
        
        public function GetFromCrex24Com()
        {
            try
            {
                $data = $this->CallUrl
                (
                    "https://api.crex24.com/v2/public/tickers?instrument=BTC-RUB",
                    __FUNCTION__,
                    function ($answer)
                    {
                        $json = json_decode($answer);
                        return isset($json[0]->last);
                    }
                );
                $json = json_decode($data);
                $json = $json[0];
                $time = $json->timestamp;
                $time = strtr($time, ['T' => ' ', 'Z' => '+00:00']);
                $time = strtotime($time);
                $ret  =
                [
                    'last_price'    => $json->last,
                    'ask'           => $json->ask,
                    'bid'           => $json->bid,
                    'volume'        => $json->volumeInBtc,
                    'upd_time'      => $time
                ];
            }
            catch (Exception $e)
            {
                $ret =
                [
                    'last_price'    => 0,
                    'ask'           => 0,
                    'bid'           => 0,
                    'volume'        => 0,
                    'upd_time'      => 0,
                    'error'         => $e->getMessage()
                ];
            }
            return $ret;
        }

        public function GetFromDskUk()
        {
            try
            {
                $data = $this->CallUrl
                (
                    "https://dsx.uk/mapi/ticker/btcrub",
                    __FUNCTION__,
                    function ($answer)
                    {
                        $json = json_decode($answer);
                        return isset($json->btcrub->last);
                    }
                );
                $json = json_decode($data);
                $json = $json->btcrub;
                $ret  =
                [
                    'last_price'    => $json->last,
                    'ask'           => $json->buy,
                    'bid'           => $json->sell,
                    'volume'        => $json->vol,
                    'upd_time'      => $json->updated
                ];
            }
            catch (Exception $e)
            {
                $ret =
                [
                    'last_price'    => 0,
                    'ask'           => 0,
                    'bid'           => 0,
                    'volume'        => 0,
                    'upd_time'      => 0,
                    'error'         => $e->getMessage()
                ];
            }
            return $ret;
        }
    };