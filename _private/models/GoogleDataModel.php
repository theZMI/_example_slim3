<?php

    /**
     * Model for get data from google sheets
     *
     * @author ZMI
     */
    class GoogleDataModel extends ModelWithExternalCall
    {
        const GOOGLE_SHEETS_PIVOT     = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQFx1gmYiMeBO6Wb92u6Gp5OZI9OIcRxQNmihh-PJk74diDOh1F8t75deqyq4Ieo_SxsqS-gL6YFrk0/pub?gid=2050799466&single=true&output=csv";
        const GOOGLE_SHEETS_DASHBOARD = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQFx1gmYiMeBO6Wb92u6Gp5OZI9OIcRxQNmihh-PJk74diDOh1F8t75deqyq4Ieo_SxsqS-gL6YFrk0/pub?gid=2138030202&single=true&output=csv";
        

        public function __construct($fromCacheOnly)
        {
            parent::__construct($fromCacheOnly);
        }
        
        protected function GetTtlByFuncName($funcName)
        {
            $ttlConfs =
            [
                'GetPivot'      => 60,
                'GetDashboard'  => 60
            ];
            return $ttlConfs[$funcName];
        }
        
        private function ParseAnswer($answer)
        {
            $ret = array_map("str_getcsv", explode("\n", $answer));
            return $ret;
        }
        
        public function GetPivot()
        {
            return $this->ParseAnswer($this->CallUrl(self::GOOGLE_SHEETS_PIVOT, __FUNCTION__));
        }

        public function GetDashboard()
        {
            return $this->ParseAnswer($this->CallUrl(self::GOOGLE_SHEETS_DASHBOARD, __FUNCTION__));
        }
    };