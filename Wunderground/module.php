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
                $this->RegisterPropertyInteger("UpdateWetterInterval", 10);
                $this->RegisterPropertyInteger("UpdateWarnungInterval", 60);
  
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
            }

        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges()
            {
                // Diese Zeile nicht löschen
               parent::ApplyChanges();

                if (($this->ReadPropertyString("API_Key") != "") AND ($this->ReadPropertyString("Wetterstation") != "")){
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
                    $this->RegisterVariableString("Icon","WetterIcon","HTMLBox",14);
                    //Variablen erstellen Wettervorhersage
                    $this->RegisterVariableString("Wettervorhersage_Woche","Wettervorhersage Woche","HTMLBox",20);
                    $this->RegisterVariableString("Wettervorhersage_Stunden","Wettervorhersage Stunden","HTMLBox",20);
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

                //Wetterdaten vom aktuellen Wetter
                $Weathernow = $this->Json_String("http://api.wunderground.com/api/".$APIkey."/conditions/lang:DL/q/CA/".$locationID.".json");
                //Wetterdaten für die nächsten  Tage downloaden
                $this->Json_Download("http://api.wunderground.com/api/".$APIkey."/forecast/lang:DL/q/".$locationID.".json",IPS_GetKernelDir()."\webfront\user\WU_WetterdatenNaechsteTage.json");
                //Wetterdaten für die nächsten  Stunden dowloaden 
                $this->Json_Download("http://api.wunderground.com/api/".$APIkey."/hourly/lang:DL/q/".$locationID.".json", IPS_GetKernelDir()."\webfront\user\WU_WetterdatenNaechsteStunden.json");
             
                //Wetterdaten in Variable speichern
                $this->SetValueByID($this->GetIDForIdent("Temp_now"),$Weathernow->current_observation->temp_c);
                $this->SetValueByID($this->GetIDForIdent("Temp_feel"), $Weathernow->current_observation->feelslike_c);
                $this->SetValueByID($this->GetIDForIdent("Temp_dewpoint"), $Weathernow->current_observation->dewpoint_c);
                $this->SetValueByID($this->GetIDForIdent("Hum_now"), substr($Weathernow->current_observation->relative_humidity, 0, -1));
                $this->SetValueByID($this->GetIDForIdent("Pres_now"), $Weathernow->current_observation->pressure_mb);
                $this->SetValueByID($this->GetIDForIdent("Wind_deg"), $Weathernow->current_observation->wind_degrees);
                $this->SetValueByID($this->GetIDForIdent("Wind_now"), $Weathernow->current_observation->wind_kph);
                $this->SetValueByID($this->GetIDForIdent("Wind_gust"), $Weathernow->current_observation->wind_gust_kph);
                $this->SetValueByID($this->GetIDForIdent("Rain_now"), $Weathernow->current_observation->precip_1hr_metric);
                $this->SetValueByID($this->GetIDForIdent("Rain_today"), $Weathernow->current_observation->precip_today_metric);
                $this->SetValueByID($this->GetIDForIdent("Solar_now"), $Weathernow->current_observation->solarradiation);
                $this->SetValueByID($this->GetIDForIdent("Vis_now"), $Weathernow->current_observation->visibility_km);
                $this->SetValueByID($this->GetIDForIdent("UV_now"), $Weathernow->current_observation->UV);
                SetValue($this->GetIDForIdent("Icon"),'http://icons.wxug.com/i/c/k/'.$Weathernow->current_observation->icon.'.gif');
               // SetValue($this->GetIDForIdent("Wettervorhersage_Woche"), $this->String_Wetter_Now_And_Next_Days($Weathernow ,$jsonNextD,$jsonWarnung) );
              //  SetValue($this->GetIDForIdent("Wettervorhersage_Stunden"), $this->String_Wetter_Heute_Stunden($jsonNextH) );

        }
        public function UpdateWetterWarnung()
        {
                $locationID =  $this->ReadPropertyString("Wetterstation");  // Location ID
                $APIkey = $this->ReadPropertyString("API_Key");  // API Key Wunderground
                
               //Wetter Warnung
                $this->Json_Download("http://api.wunderground.com/api/".$APIkey."/alerts/lang:DL/q/".$locationID.".json", IPS_GetKernelDir()."\webfront\user\WU_WetterWarnungen.json");
        }
        

        
        public function Weathernow($value)
        {
            $Weathernow = array('Temp_now','Temp_feel', 'Temp_dewpoint','Hum_now','Pres_now','Wind_deg','Wind_now','Wind_gust','Rain_now','Rain_today','Solar_now','Vis_now','UV_now','Icon');
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
        
        public function Weathernextdays($day,$value)
        {
            if ($day < 0 || $day > 3) {   
                echo "Tag ".$day." nicht gefunden ! Gültige Werte 0 - 3";
                IPS_LogMessage("Wunderground","FEHLER Tag ".$day." nicht gefunden ! Gültige Werte 0 - 3");
       		    exit;
            }               
            $GetData = file_get_contents(IPS_GetKernelDir()."\webfront\user\WU_WetterdatenNaechsteTage.json");
                if ($GetData === false) {
       			        IPS_LogMessage("Wunderground", "FEHLER - Die WetterdatenNaechsteTage.json konnte nicht geladen werden!");
       				    exit;
    						}
            $jsonData = json_decode($GetData);
            for ($i=0; $i <4 ; $i++) { 
             $data[$i] =   array(
                'Date' => $jsonData->forecast->simpleforecast->forecastday[$i]->date->epoch,
                'text' => $jsonData->forecast->txt_forecast->forecastday[$i]->fcttext_metric,
                'Icon'  => 'http://icons.wxug.com/i/c/k/'. $jsonData->forecast->simpleforecast->forecastday[$i]->icon.'.gif',
                'TempHigh' => $jsonData->forecast->simpleforecast->forecastday[$i]->high->celsius,
                'TempLow' => $jsonData->forecast->simpleforecast->forecastday[$i]->low->celsius,
                'Humidity' => $jsonData->forecast->simpleforecast->forecastday[$i]->avehumidity,       
                'Wind' => $jsonData->forecast->simpleforecast->forecastday[$i]->avewind->kph,
                'MaxWind' => $jsonData->forecast->simpleforecast->forecastday[$i]->maxwind->kph,
                'Rain' => $jsonData->forecast->simpleforecast->forecastday[$i]->qpf_allday->mm);              
            }
                        
            if (empty ($value) || $value == "all") {   
                foreach ($data[$day] as $value) {
                    $a = $data[$day][$value];
                }
                 return $a; 
            }
            if (array_key_exists($value, $data[$day])) {
                return $data[$day][$value]; 
            }
            else {
                echo "Variable ".$value." nicht gefunden !";
                IPS_LogMessage("Wunderground", "FEHLER - Variable ".$value." nicht gefunden !");
       		    exit;
            }



        }
        
        
        protected function String_Wetter_Now_And_Next_Days($Weathernow, $WetterNextDays, $WetterWarnung)
            {           
                $html = '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
                            <table >';
                           
                            foreach($WetterWarnung->alerts as $Warnung=> $ID){
                                $html.= '<tr>
                                        <td style="color:'.$ID->level_meteoalarm_name.'" colspan="5"> <i class="fa fa-info-circle"></i>
                                         '.$ID->description.'
                                         </td>                              
                                        </tr>';
                              }
                              
                        $html.= '<tr>
                                <td align="center" valign="top"  style="width:130px;padding-left:20px;">
                                    Aktuell<br>
                                    <img src="'.$Weathernow->current_observation->icon_url.'" style="float:left;">
                                    <div style="float:right">
                                         '.$Weathernow->current_observation->temp_c.' °C<br>
                                        '.$Weathernow->current_observation->relative_humidity.'<br>
                                     </div>
                                    <div style="clear:both; font-size: 10px;">
                                        Ø Wind: '.$Weathernow->current_observation->wind_kph.' km/h<br>
                                        '.$Weathernow->current_observation->feelslike_c.' °C gefühlt<br>
                                        '.$Weathernow->current_observation->pressure_mb.' hPa<br>
                                        Regen 1h: '.$Weathernow->current_observation->precip_1hr_metric.' Liter/m²<br>
                                        Sichtweite '.$Weathernow->current_observation->visibility_km.' km
                                     </div>
                                 </td>';
                foreach($WetterNextDays->forecast->simpleforecast->forecastday as $name=> $day){
                    if( $this->isToday($day->date->epoch))
                        $Wochentag = "Heute";
                    else {
                        $tag = array("Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag");
                        $Wochentag =$tag[date("w",intval($day->date->epoch))];
                         }
                     $html.= '<td align="center" valign="top"  style="width:130px;padding-left:20px;">
                                '.$Wochentag.'<br>
                                <img src="'.$day->icon_url.'" style="float:left;">
                                <div style="float:right">
                                    '.$day->low->celsius.' °C<br>
                                    '.$day->high->celsius.' °C
                                 </div>
                                 <div style="clear:both; font-size: 10px;"> 
                                    Ø Wind: '.$day->avewind->kph.' km/h<br>
                                    Niederschlag: '.($day->qpf_allday->mm).' Liter/m²
                                  </div>
                               </td>';
                       }
                $html .= "</tr>
                           </table>";
                return $html;
            }

        protected function String_Wetter_Heute_Stunden($WetterStunden)
            {
                  $html = '<table >
                            <tr>';
                for ($i=0; $i < 24; $i=$i+4) {
                       $html.= '<td align="center" valign="top"  style="width:130px;padding-left:20px;">
                                '.$WetterStunden->hourly_forecast[$i]->FCTTIME->weekday_name.' '.$WetterStunden->hourly_forecast[$i]->FCTTIME->hour.' Uhr <br>
                                
                                <img src="'.$WetterStunden->hourly_forecast[$i]->icon_url.'" style="float:left;">
                                <div style="float:right">
                                    '.$WetterStunden->hourly_forecast[$i]->temp->metric.' °C<br>
                                    '.$WetterStunden->hourly_forecast[$i]->humidity.' %
                                 </div>

                                 <div style="clear:both; font-size: 10px;">
                                    '.$WetterStunden->hourly_forecast[$i]->wx.'<br>
                                    Ø Wind: '.$WetterStunden->hourly_forecast[$i]->wspd->metric.' km/h<br>
                                    Niederschlag: '.$WetterStunden->hourly_forecast[$i]->qpf->metric.' Liter/m²
                                  </div>
                               </td>';
                       }
                $html .= "</tr>
                           </table>";
                return $html;  
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

              
        protected function Json_Download($URLString,$file)
              {
                  $GetURL = Sys_GetURLContent($URLString);  //Json Daten öfffen
                  if ($GetURL == false) {
                      IPS_LogMessage("Wunderground", "FEHLER - Die Wunderground-API konnte nicht abgefragt werden!");
                      exit;
                  }
                        $data = json_decode($GetURL);  //Json Daten in String speichern
 						file_put_contents($file,json_encode($data)); //Json String in Datei speichern
 						return true;
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
                IPS_ApplyChanges($archiveHandlerID);
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
    
        protected function isToday($time)
            {
                $begin = mktime(0, 0, 0);
                $end = mktime(23, 59, 59);
                // check if given time is between begin and end
                if($time >= $begin && $time <= $end)
                    return true;
                else 
                    return false;
            }

        protected function SetValueByID($VariablenID,$Wert)
            {
                // Überprüfen ob $Wert eine Zahl ist
                if (is_numeric($Wert))
                    SetValue($VariablenID,$Wert);
                //Wenn $Wert keine Zahl ist setze den Wert auf 0
                else 
                SetValue($VariablenID,0);
            }


     }
?>