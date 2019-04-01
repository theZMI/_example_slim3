<?php

    /**
     * Model for calculation information from markets and google-sheets
     *
     * @author ZMI
     */
    class CalcDataModel
    {
        private $marketsData = [];
        private $googleData  = [];

        
        public function __construct($fromCacheOnly)
        {
            $marketModel = new MarketModel($fromCacheOnly);
            $this->marketsData =
            [
                'CEX.IO'    => $marketModel->GetFromCexIO(),
                'EXMO'      => $marketModel->GetFromExmoCom(),
                'Livecoin'  => $marketModel->GetFromLiveCoinNet(),
                'YObit'     => $marketModel->GetFromYoBitNet(),
                'CREX24'    => $marketModel->GetFromCrex24Com(),
                'DSX'       => $marketModel->GetFromDskUk()
            ];

            $googleDataModel = new GoogleDataModel($fromCacheOnly);
            $this->googleData[GoogleDataModel::GOOGLE_SHEETS_DASHBOARD] = $googleDataModel->GetDashboard();
            $this->googleData[GoogleDataModel::GOOGLE_SHEETS_PIVOT]     = $googleDataModel->GetPivot();
        }

        public function GetMarketDataByMarketName($name)
        {
            return isset($this->marketsData[$name])
                    ? $this->marketsData[$name]
                    : NULL;
        }

        private function FieldFormat($v)
        {
            $ret = NULL;
            // Если это процент, то возвращаем float (50% -> 0.5)
            if (strpos($v, '%') !== false)
            {
                $ret = round( floatval(str_replace("%", '', $v)) / 100 , 5 );
            }
            elseif (in_array($v, ['Yes', 'No']))
            {
                $ret = $v === 'Yes' ? 1 : 0;
            }
            else
            {
                // Убираем разделитель у тысяч
                $ret = str_replace(',', '', $v);
            }
            return $ret;
        }

        public function GetTrueBTC()
        {
            $googleDashboard    = array_slice($this->googleData[GoogleDataModel::GOOGLE_SHEETS_DASHBOARD], 2);
            $priceBuyExchanges  = [];
            $priceSellExchanges = [];
            $exchangeVolumes    = [];
            
            foreach ($googleDashboard as $k => $v)
            {
                $marketName                  = $v[0];
                
                // Check ttl for data of current market:
                $isDataExpired = (time() - $this->marketsData[$marketName]['upd_time']) > MarketModel::MAX_MARKET_DATA_TTL;
                if ($isDataExpired)
                {
                    continue;
                }
                
                // From googleSheets:
                $totalRelativeCommissionBuy  = $this->FieldFormat($v[14]);
                $totalRelativeCommissionSell = $this->FieldFormat($v[15]);
                $totalAbsoluteCommissionBuy  = floatval($this->FieldFormat($v[18]) / 1000);
                $totalAbsoluteCommissionSell = floatval($this->FieldFormat($v[19]) / 1000);

                // Calc withRelativeCommissions by googleSheets and API data:
                $withRelativeCommissionBuy   = $this->marketsData[$marketName]['bid'] / (1 - $totalRelativeCommissionBuy);
                $withRelativeCommissionSell  = $this->marketsData[$marketName]['ask'] * (1 - $totalRelativeCommissionSell);

                $priceBuyExchanges[]         = $withRelativeCommissionBuy + $totalAbsoluteCommissionBuy;
                $priceSellExchanges[]        = $withRelativeCommissionSell + $totalAbsoluteCommissionSell;
                $exchangeVolumes[]           = $this->marketsData[$marketName]['volume'];
            }
            $sumProductBuy = 0;
            for ($i = 0; $i < count($priceBuyExchanges); $i++)
            {
                $t = $priceBuyExchanges[$i] * $exchangeVolumes[$i];
                $sumProductBuy += $t;
            }
            $sumProductSell = 0;
            for ($i = 0; $i < count($priceSellExchanges); $i++)
            {
                $t = $priceSellExchanges[$i] * $exchangeVolumes[$i];
                $sumProductSell += $t;
            }
            $ret['buy'] = array_sum($exchangeVolumes) ? $sumProductBuy / array_sum($exchangeVolumes) : 0;
            $ret['sell'] = array_sum($exchangeVolumes) ? $sumProductSell / array_sum($exchangeVolumes) : 0;
            return $ret;
        }
        
        public function GetData()
        {
            $googleDashboard = array_slice($this->googleData[GoogleDataModel::GOOGLE_SHEETS_DASHBOARD], 2);
            $data = [];
            foreach ($googleDashboard as $v)
            {
                $marketName = $v[0];

                $isDataExpired = (time() - $this->marketsData[$marketName]['upd_time']) > MarketModel::MAX_MARKET_DATA_TTL;
                if ($isDataExpired)
                {
                    continue;
                }
                
                $totalRelativeCommissionBuy  = $this->FieldFormat($v[14]);
                $totalRelativeCommissionSell = $this->FieldFormat($v[15]);
                $totalAbsoluteCommissionBuy  = floatval($this->FieldFormat($v[18]) / 1000);
                $totalAbsoluteCommissionSell = floatval($this->FieldFormat($v[19]) / 1000);

                $data[$marketName] =
                [
                    'last'      => $this->marketsData[$marketName]['last_price'],
                    'volume'    => $this->marketsData[$marketName]['volume'],
                    'limits'    =>
                    [
                        'min' => $this->FieldFormat($v[12]),
                        'max' => $this->FieldFormat($v[13])
                    ],
                    'KYC'       => $this->FieldFormat($v[9]),
                    'stable'    => $this->FieldFormat($v[10]),
                    'easy'      => $this->FieldFormat($v[11]),
                    'bid'       => $this->marketsData[$marketName]['bid'],
                    'ask'       => $this->marketsData[$marketName]['ask'],
                    'total_relative_commission' =>
                    [
                        'buy'  => $totalRelativeCommissionBuy,
                        'sell' => $totalRelativeCommissionSell
                    ],
                ];
                $data[$marketName]['true_rate_buy']  = ($data[$marketName]['bid'] / (1 - $data[$marketName]['total_relative_commission']['buy'])) + $totalAbsoluteCommissionBuy;
                $data[$marketName]['true_rate_sell'] = ($data[$marketName]['ask'] * (1 - $data[$marketName]['total_relative_commission']['sell'])) - $totalAbsoluteCommissionSell;
                $data[$marketName]['diff_rate_buy']  = abs($data[$marketName]['last'] - $data[$marketName]['true_rate_buy']);
                $data[$marketName]['diff_rate_sell'] = abs($data[$marketName]['last'] - $data[$marketName]['true_rate_sell']);
            }
            return $data;
        }
    };