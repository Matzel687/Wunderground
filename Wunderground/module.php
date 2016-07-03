<?

//require_once(__DIR__ . "/../OnkyoAVRClass.php");  // diverse Klassen
    // Klassendefinition
    class WundergroundWetter extends IPSModule
     {
        // Der Konstruktor des Moduls
        // Überschreibt den Standard Kontruktor von IPS
        public function __construct($InstanceID)
            {
                // Diese Zeile nicht löschen
                parent::__construct($InstanceID);
                // Selbsterstellter Code
            }

        public function Create()
            {
                // Diese Zeile nicht löschen.
                parent::Create();

                $this->RegisterPropertyString("Wetterstation", "");
                $this->RegisterPropertyString("API_Key", "");
                $this->RegisterPropertyString("Icon_Dir", "http://icons.wxug.com/i/c/k/");
                $this->RegisterPropertyString("Icon_Data_Type", "gif");
                $this->RegisterPropertyInteger("UpdateWetterInterval", 10);
                $this->RegisterPropertyInteger("UpdateWarnungInterval", 60);
                $this->RegisterPropertyInteger("SunriseVariableID", 0);
                $this->RegisterPropertyInteger("SunsetVariableID", 0);

                //Variable Änderungen aufzeichnen
                $this->RegisterPropertyBoolean("logTemp_now", false);
                $this->RegisterPropertyBoolean("logTemp_feel", false);
                $this->RegisterPropertyBoolean("logTemp_dewpoint", false);
                $this->RegisterPropertyBoolean("logHum_now", false);
                $this->RegisterPropertyBoolean("logPres_now", false);
                $this->RegisterPropertyBoolean("logWind_deg", false);
                $this->RegisterPropertyBoolean("logWind_now", false);
                $this->RegisterPropertyBoolean("logWind_gust", false);
                $this->RegisterPropertyBoolean("logRain_now", false);
                $this->RegisterPropertyBoolean("logRain_today", false);
                $this->RegisterPropertyBoolean("logSolar_now", false);
                $this->RegisterPropertyBoolean("logVis_now", false);
                $this->RegisterPropertyBoolean("logUV_now", false);
                
                //Variablenprofil anlegen
                $this->Var_Pro_Erstellen("WD_Niederschlag",2,"Liter/m²",0,10,0,2,"Rainfall");
                $this->Var_Pro_Erstellen("WD_Sonnenstrahlung",2,"W/m²",0,2000,0,2,"Sun");
                $this->Var_Pro_Erstellen("WD_Sichtweite",2,"km",0,0,0,2,"");
                $this->Var_Pro_WD_WindSpeedkmh();
                $this->Var_Pro_WD_UVIndex();
                //Timer erstellen
                $this->SetTimerMinutes($this->InstanceID,"UpdateWetterDaten",$this->ReadPropertyInteger("UpdateWetterInterval"),'WD_UpdateWetterDaten($_IPS["TARGET"]);');
                $this->SetTimerMinutes($this->InstanceID,"UpdateWetterWarnung",$this->ReadPropertyInteger("UpdateWarnungInterval"),'WD_UpdateWetterWarnung($_IPS["TARGET"]);');
                
            }

        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges()
            {
                // Diese Zeile nicht löschen
               parent::ApplyChanges();

                if (($this->ReadPropertyString("API_Key") != "") AND ($this->ReadPropertyString("Wetterstation") != "")
                    AND ($this->ReadPropertyInteger("SunriseVariableID") != "") AND ($this->ReadPropertyInteger("SunsetVariableID") != "")){
                    //Variablen erstellen Wetter jetzt
                    $this->RegisterVariableFloat("Temp_now","Temperatur","Temperature",1);
                    $this->RegisterVariableFloat("Temp_feel","Temperatur gefühlt","Temperature",2);
                    $this->RegisterVariableFloat("Temp_dewpoint","Temperatur Taupunkt","Temperature",3);
                    $this->RegisterVariableFloat("Hum_now","Luftfeuchtigkeit","Humidity.F",4);
                    $this->RegisterVariableFloat("Pres_now","Luftdruck","AirPressure.F",5);
                    $this->RegisterVariableFloat("Wind_deg","Windrichtung","WindDirection.Text",6);
                    $this->RegisterVariableFloat("Wind_now","Windgeschwindigkeit","WD_WindSpeed_kmh",7);
                    $this->RegisterVariableFloat("Wind_gust","Windböe","WD_WindSpeed_kmh",8);
                    $this->RegisterVariableFloat("Rain_now","Niederschlag/h","WD_Niederschlag",9);
                    $this->RegisterVariableFloat("Rain_today","Niederschlag Tag","WD_Niederschlag",10);
                    $this->RegisterVariableFloat("Solar_now","Sonnenstrahlung","WD_Sonnenstrahlung",11);
                    $this->RegisterVariableFloat("Vis_now","Sichtweite","WD_Sichtweite",12);
                    $this->RegisterVariableInteger("UV_now","UV Strahlung","WD_UV_Index",13);
                    $this->RegisterVariableString("Text","WetterText","String",18);
                    $this->RegisterVariableString("Icon","WetterIcon","HTMLBox",14);
                    IPS_SetHidden($this->GetIDForIdent("Icon"), true); //Objekt verstecken
                    $this->RegisterVariableString("Weathernextdays","WeatherNextDaysData","String",15);
                    IPS_SetHidden($this->GetIDForIdent("Weathernextdays"), true); //Objekt verstecken
                    $this->RegisterVariableString("Weathernexthours","WeatherNextHoursData","String",16);
                    IPS_SetHidden($this->GetIDForIdent("Weathernexthours"), true); //Objekt verstecken
                    $this->RegisterVariableString("Weatheralerts","WeatherAlerts","String",17);
                    IPS_SetHidden($this->GetIDForIdent("Weatheralerts"), true); //Objekt verstecken
                    //Timer zeit setzen
                    $this->SetTimerMinutes($this->InstanceID,"UpdateWetterDaten",$this->ReadPropertyInteger("UpdateWetterInterval"),'WD_UpdateWetterDaten($_IPS["TARGET"]);');
                    $this->SetTimerMinutes($this->InstanceID,"UpdateWetterWarnung",$this->ReadPropertyInteger("UpdateWarnungInterval"),'WD_UpdateWetterWarnung($_IPS["TARGET"]);');
                    //Instanz ist aktiv
                    $this->SetStatus(102);
                }
                else {
                    //Instanz ist inaktiv
                   $this->SetStatus(104); 
                }
                
                // Variable Logging Aktivieren/Deaktivieren
                if ($this->ReadPropertyBoolean("logTemp_now") === true)
                    $this-> VarLogging("Temp_now","logTemp_now",0);
                if ($this->ReadPropertyBoolean("logTemp_feel") === true)
                    $this-> VarLogging("Temp_feel","logTemp_feel",0);
                if ($this->ReadPropertyBoolean("logTemp_dewpoint") === true)
                    $this-> VarLogging("Temp_dewpoint","logTemp_dewpoint",0);
                if ($this->ReadPropertyBoolean("logHum_now") === true)
                    $this-> VarLogging("Hum_now","logHum_now",0);
                if ($this->ReadPropertyBoolean("logPres_now") === true)
                    $this-> VarLogging("Pres_now","logPres_now",0);
                if ($this->ReadPropertyBoolean("logWind_deg") === true)
                    $this-> VarLogging("Wind_deg","logWind_deg",0);
                if ($this->ReadPropertyBoolean("logWind_now") === true)
                    $this-> VarLogging("Wind_now","logWind_now",0);
                if ($this->ReadPropertyBoolean("logWind_gust") === true)
                    $this-> VarLogging("Wind_gust","logWind_gust",0);
                if ($this->ReadPropertyBoolean("logRain_now") === true)
                    $this-> VarLogging("Rain_now","logRain_now",1);
                if ($this->ReadPropertyBoolean("logRain_today") === true)
                    $this-> VarLogging("Rain_today","logRain_today",1);
                if ($this->ReadPropertyBoolean("logSolar_now") === true)
                    $this-> VarLogging("Solar_now","logSolar_now",1);
                if ($this->ReadPropertyBoolean("logVis_now") === true)
                    $this-> VarLogging("Vis_now","logVis_now",0);
                if ($this->ReadPropertyBoolean("logUV_now") === true)
                    $this-> VarLogging("UV_now","logUV_now",0);
            }

        public function UpdateWetterDaten()
        {
                $locationID =  $this->ReadPropertyString("Wetterstation");  // Location ID
                $APIkey = $this->ReadPropertyString("API_Key");  // API Key Wunderground
                $IconDir = $this->ReadPropertyString("Icon_Dir");  // Icon Pfad für die WetterIcons
                $IconDataType = $this->ReadPropertyString("Icon_Data_Type");// Icon Type jpeg,png,gif
                $SunriseVarID = $this->ReadPropertyInteger("SunriseVariableID");
                $SunsetVarID = $this->ReadPropertyInteger("SunsetVariableID");
                $isDay = $this->isDayTime($SunriseVarID,$SunsetVarID,time());
 
                //Wetterdaten abrufen 
                $Weather = $this->Json_String("http://api.wunderground.com/api/".$APIkey."/conditions/forecast/hourly/lang:DL/q/CA/".$locationID.".json");
                //Wetterdaten in Variable speichern
                $this->SetValueByID($this->GetIDForIdent("Temp_now"),$Weather->current_observation->temp_c);
                $this->SetValueByID($this->GetIDForIdent("Temp_feel"), $Weather->current_observation->feelslike_c);
                $this->SetValueByID($this->GetIDForIdent("Temp_dewpoint"), $Weather->current_observation->dewpoint_c);
                $this->SetValueByID($this->GetIDForIdent("Hum_now"), substr($Weather->current_observation->relative_humidity, 0, -1));
                $this->SetValueByID($this->GetIDForIdent("Pres_now"), $Weather->current_observation->pressure_mb);
                $this->SetValueByID($this->GetIDForIdent("Wind_deg"), $Weather->current_observation->wind_degrees);
                $this->SetValueByID($this->GetIDForIdent("Wind_now"), $Weather->current_observation->wind_kph);
                $this->SetValueByID($this->GetIDForIdent("Wind_gust"), $Weather->current_observation->wind_gust_kph);
                $this->SetValueByID($this->GetIDForIdent("Rain_now"), $Weather->current_observation->precip_1hr_metric);
                $this->SetValueByID($this->GetIDForIdent("Rain_today"), $Weather->current_observation->precip_today_metric);
                $this->SetValueByID($this->GetIDForIdent("Solar_now"), $Weather->current_observation->solarradiation);
                $this->SetValueByID($this->GetIDForIdent("Vis_now"), $Weather->current_observation->visibility_km);
                $this->SetValueByID($this->GetIDForIdent("UV_now"), $Weather->current_observation->UV);
                SetValue($this->GetIDForIdent("Text"),  $Weather->current_observation->weather);
                SetValue($this->GetIDForIdent("Icon"),''.$IconDir.''.$this->getDayTimeRelatedIcon($Weather->current_observation->icon, $isDay).'.'.$IconDataType);

              
              
                //Wetterdaten für die nächsten  Tage
                //$Weather = $this->Json_String("http://api.wunderground.com/api/".$APIkey."/forecast/lang:DL/q/".$locationID.".json");  
                for ($i=0; $i <4 ; $i++) { 

                    $data[$i] =   array(
                        'Date' =>  $Weather->forecast->simpleforecast->forecastday[$i]->date->epoch,
                        'Text' =>  $Weather->forecast->txt_forecast->forecastday[$i]->fcttext_metric,
                        'Icon'  => ''.$IconDir.''.$this->getDayTimeRelatedIcon($Weather->forecast->simpleforecast->forecastday[$i]->icon, $isDay).'.'.$IconDataType ,
                        'TempHigh' =>  $Weather->forecast->simpleforecast->forecastday[$i]->high->celsius,
                        'TempLow' =>  $Weather->forecast->simpleforecast->forecastday[$i]->low->celsius,
                        'Humidity' =>  $Weather->forecast->simpleforecast->forecastday[$i]->avehumidity,       
                        'Wind' =>  $Weather->forecast->simpleforecast->forecastday[$i]->avewind->kph,
                        'MaxWind' =>  $Weather->forecast->simpleforecast->forecastday[$i]->maxwind->kph,
                        'Rain' =>  $Weather->forecast->simpleforecast->forecastday[$i]->qpf_allday->mm, 
                        'Pop'  =>  $Weather->forecast->simpleforecast->forecastday[$i]->pop);            
                }
                // Wetterdaten in String speichern
                SetValue($this->GetIDForIdent("Weathernextdays"),json_encode($data)); 
                $data = NULL;
                  
                //Wetterdaten für die nächsten  Stunden         
               // $Weather = $this->Json_String("http://api.wunderground.com/api/".$APIkey."/hourly/lang:DL/q/".$locationID.".json"); 
                for ($i=0; $i <24 ; $i++) { 

                    //Prüfe ob Tag oder Nacht ist 
                    $time = $Weather->hourly_forecast[$i]->FCTTIME->epoch;
                    $isDaynexthours = $this->isDayTime($SunriseVarID,$SunsetVarID,$time);

                    $data[$i] =   array(
                        'Date' => $time,
                        'Text' => $Weather->hourly_forecast[$i]->condition,
                        'Icon'  => ''.$IconDir.''.$this->getDayTimeRelatedIcon($Weather->hourly_forecast[$i]->icon,$isDaynexthours).'.'.$IconDataType ,
                        'Temp' => $Weather->hourly_forecast[$i]->temp->metric,
                        'Tempfeel' => $Weather->hourly_forecast[$i]->feelslike->metric,
                        'Tempdewpoint' => $Weather->hourly_forecast[$i]->dewpoint->metric,
                        'Humidity' => $Weather->hourly_forecast[$i]->humidity,       
                        'Wind' => $Weather->hourly_forecast[$i]->wspd->metric,
                        'Pres' => $Weather->hourly_forecast[$i]->mslp->metric,
                        'Rain' => $Weather->hourly_forecast[$i]->qpf->metric,
                        'Pop'  => $Weather->hourly_forecast[$i]->pop);            
                }
                // Wetterdaten in String speichern
                SetValue($this->GetIDForIdent("Weathernexthours"),json_encode($data)); 
                $data = NULL;             
                     
        }
        
        public function UpdateWetterWarnung()
        {
                $locationID =  $this->ReadPropertyString("Wetterstation");  // Location ID
                $APIkey = $this->ReadPropertyString("API_Key");  // API Key Wunderground
                $data = NULL;
               //Wetter Warnung
                $alerts = $this->Json_String("http://api.wunderground.com/api/".$APIkey."/alerts/lang:DL/q/".$locationID.".json");
                foreach ($alerts->alerts  as $key => $value) {
                    $data[$key] =   array(
                        'Date' => $value->date_epoch,
                        'Type' => $value->type,
                        'Name' => $value->wtype_meteoalarm_name,
                        'Color'  => $value->level_meteoalarm_name,
                        'Text' => str_replace("deutsch:", "", $value->description));
                }
                //Wetter Warnung speichern
                if ($data == NULL) {
                        SetValue($this->GetIDForIdent("Weatheralerts"),"[]"); 
                }
                else {
                        $sorted_data = $this->unique_multidim_array($data,"Text");
                        SetValue($this->GetIDForIdent("Weatheralerts"),json_encode($sorted_data)); 
                        $data = NULL;    
                }

        }
         
        public function Weathernow($value)
        {
            $Weathernow = array('Temp_now','Temp_feel', 'Temp_dewpoint','Hum_now','Pres_now','Wind_deg','Wind_now','Wind_gust','Rain_now','Rain_today','Solar_now','Vis_now','UV_now','Icon','Text');
            if (empty ($value) || $value == "all") {
                foreach ($Weathernow as $value) {
                    $data[$value] = GetValue($this->GetIDForIdent($value));           
                }      
                 return $data; 
            }
            elseif (in_array($value, $Weathernow)) {
                return GetValue($this->GetIDForIdent($value)); 
            }
            else {
                echo "Variable ".$value." nicht gefunden !";
                IPS_LogMessage("Wunderground", "FEHLER - Variable ".$value." nicht gefunden !");
       		    exit;
            }           
        }
        
        public function Weathernextdays()
        {         
           $GetData = GetValue($this->GetIDForIdent("Weathernextdays"));             
           return json_decode($GetData,TRUE);         
        }
        
        public function Weathernexthours()
        {         
           $GetData = GetValue($this->GetIDForIdent("Weathernexthours"));        
           return json_decode($GetData,TRUE);            
        }
        
            public function Weatheralerts()
        {         
           $GetData = GetValue($this->GetIDForIdent("Weatheralerts"));        
           return json_decode($GetData,TRUE);            
        }
            
        protected function Json_String($URLString)
              {
                  $GetURL = Sys_GetURLContent($URLString);  //Json Daten öfffen
                  if ($GetURL == false) {
                      IPS_LogMessage("Wunderground", "FEHLER - Die Wunderground-API konnte nicht abgefragt werden!");
                      exit;
                  }
                  return json_decode($GetURL);  //Json Daten in String speichern
              }  

        // Variablen profile erstellen        
        protected function Var_Pro_Erstellen($name,$ProfileType,$Suffix,$MinValue,$MaxValue,$StepSize,$Digits,$Icon)
            {
                if (IPS_VariableProfileExists($name) == false){
                    IPS_CreateVariableProfile($name, $ProfileType);
                    IPS_SetVariableProfileText($name, "", $Suffix);
                    IPS_SetVariableProfileValues($name, $MinValue, $MaxValue,$StepSize);
                    IPS_SetVariableProfileDigits($name, $Digits);
                    IPS_SetVariableProfileIcon($name,$Icon);
                 }
            }
        protected function Var_Pro_WD_WindSpeedKmh()
            {
                if (IPS_VariableProfileExists("WD_WindSpeed_kmh") == false){
                    IPS_CreateVariableProfile("WD_WindSpeed_kmh", 2);
                    IPS_SetVariableProfileText("WD_WindSpeed_kmh", "", "km/h");
                    IPS_SetVariableProfileValues("WD_WindSpeed_kmh", 0, 200, 0);
                    IPS_SetVariableProfileDigits("WD_WindSpeed_kmh", 1);
                    IPS_SetVariableProfileIcon("WD_WindSpeed_kmh", "WindSpeed");
                    IPS_SetVariableProfileAssociation("WD_WindSpeed_kmh", 0, "%.1f", "WindSpeed", 16776960);
                    IPS_SetVariableProfileAssociation("WD_WindSpeed_kmh", 2, "%.1f", "WindSpeed", 6736947);
                    IPS_SetVariableProfileAssociation("WD_WindSpeed_kmh", 4, "%.1f", "WindSpeed", 16737894);
                    IPS_SetVariableProfileAssociation("WD_WindSpeed_kmh", 6, "%.1f", "WindSpeed", 3381504);
                    IPS_SetVariableProfileAssociation("WD_WindSpeed_kmh", 10, "%.1f", "WindSpeed", 52428);
                    IPS_SetVariableProfileAssociation("WD_WindSpeed_kmh", 20, "%.1f", "WindSpeed", 16724940);
                    IPS_SetVariableProfileAssociation("WD_WindSpeed_kmh", 36, "%.1f", "WindSpeed", 16764159);
                 }
            }
        protected function Var_Pro_WD_UVIndex()
            {
                if (IPS_VariableProfileExists("WD_UV_Index") == false){
                    IPS_CreateVariableProfile("WD_UV_Index", 1);
                    IPS_SetVariableProfileValues("WD_UV_Index", 0, 12, 0);
                    IPS_SetVariableProfileAssociation("WD_UV_Index", 0, "%.1f","",0xC0FFA0);
                    IPS_SetVariableProfileAssociation("WD_UV_Index", 3, "%.1f","",0xF8F040);
                    IPS_SetVariableProfileAssociation("WD_UV_Index", 6, "%.1f","",0xF87820);
                    IPS_SetVariableProfileAssociation("WD_UV_Index", 8, "%.1f","",0xD80020);
                    IPS_SetVariableProfileAssociation("WD_UV_Index", 11, "%.1f","",0xA80080);
                 }          
            }
        // Aktvieren und Deaktivieren vom Varriable Logging 
        protected function VarLogging($VarName,$LogStatus,$Type)
            {
                $archiveHandlerID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
                AC_SetAggregationType($archiveHandlerID, $this->GetIDForIdent($VarName), $Type);
                AC_SetLoggingStatus($archiveHandlerID, $this->GetIDForIdent($VarName), $this->ReadPropertyBoolean($LogStatus));
                if(IPS_HasChanges($archiveHandlerID))
                {
                    IPS_ApplyChanges($archiveHandlerID);
                }
            }

        //Timer erstllen alle X minuten 
        protected function SetTimerMinutes($parentID, $name,$minutes,$Event)
            {
                $eid = @IPS_GetEventIDByName($name, $parentID);
                if($eid === false){
                    $eid = IPS_CreateEvent(1);
                    IPS_SetParent($eid, $parentID);
                    IPS_SetName($eid, $name);
                 }
                else{
                    IPS_SetEventCyclic($eid, 0 /* Keine Datumsüberprüfung */, 0, 0, 2, 2 /* Minütlich */ , $minutes/* Alle XX Minuten */);
                    IPS_SetEventScript($eid, $Event);
                    IPS_SetEventActive($eid, true);
                    IPS_SetHidden($eid, true);
                 }
            }
    
        protected function SetValueByID($VariablenID,$Wert)
            {
                // Überprüfen ob $Wert eine Zahl ist
                if (is_numeric($Wert)){
                    SetValue($VariablenID,$Wert);
                }
                //Wenn $Wert keine Zahl ist setze den Wert auf 0
                else{
                    SetValue($VariablenID,0);
                }
            }
        
        //doppelte Array Einträge löschen 
        protected function unique_multidim_array($array, $key)
            {
                $temp_array = array();
                $i = 0;
                $key_array = array();
                foreach($array as $val) {
                    if (!in_array($val[$key], $key_array)) {
                        $key_array[$i] = $val[$key];
                        $temp_array[$i] = $val;
                    }
                    $i++;
                }
                return $temp_array;
             }
        
        protected function getDayTimeRelatedIcon($icon, $DayTime)
            {
                if ($DayTime == true){
                    $new_icon = $icon;
                } else {
                    $basename = basename($icon);
                    $new_icon = str_replace($basename, 'nt_'.$basename, $icon);
                }
                    return $new_icon;
            }  

        //Prüfe ob Tag oder Nacht 
        protected function isDayTime($SunriseVarID,$SunsetVarID,$time)
            {
                $Sunrisedate = getdate(GetValueInteger($SunriseVarID));
                $Sunsetdate = getdate(GetValueInteger($SunsetVarID)); 
                $Sunrise = mktime($Sunrisedate['hours'],$Sunrisedate['minutes']);
                $Sunset = mktime($Sunsetdate['hours'], $Sunsetdate['minutes']);
                // check if given time is between sunset and sunrise
                if (($time >= $Sunrise) AND ($time <= $Sunset)) {
                    return true;
                } else {
                    return false;
                }
            }

        protected function CeckAndSetValueByID($VariablenID,$Wert)  // Prüfe Werte auf Extreme Werte über 700% 
            {
                // Überprüfen ob $Wert eine Zahl ist
                $archiveHandlerID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];  //ID vom Archive Control ermitteln
                    
                if(AC_GetLoggingStatus($archiveHandlerID , $VariablenID) ==true){
                    $LastValues = AC_GetLoggedValues($archiveHandlerID, $VariablenID, strtotime("yesterday 00:00"), time(), 1);     // Letzten Wert auslesen
                    $LastValue = $LastValues [0]['Value'];
                    if (($LastValue-$Wert)/($LastValue+1) <= 7  && ($LastValue-$Wert+1)/($LastValue+1) >= -7){            //Wenn der neue Wert nicht um +-700% größer/kleiner ist, schreibe den neuen Wert in die Variable
                        SetValue($VariablenID,$Wert);
                    }
                    else{                                               // sonst nehme den alten Wert 
                        SetValue($VariablenID,$LastValue);
                    }
                }
                elseif (is_numeric($Wert)){
                    SetValue($VariablenID,$Wert);
                }
                //Wenn $Wert keine Zahl ist setze den Wert auf 0
                else 
                SetValue($VariablenID,0);
            }

     }
?>