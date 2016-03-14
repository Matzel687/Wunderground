<?
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
			$this->RegisterPropertyInteger("UpdateInterval", 10);
     		$this->SetTimerMinutes($this->InstanceID,"Update",$this->ReadPropertyInteger("UpdateInterval"));
            
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

			if (($this->ReadPropertyString("API_Key") != "") AND ($this->ReadPropertyString("Wetterstation") != ""))
				{
                            //Variablen erstellen Wetter jetzt
            $this->RegisterVariableFloat("Temp_now","Temperatur","Temperature",1);
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
            //Variablen erstellen Wettervorhersage
            $this->RegisterVariableFloat("Temp_high_heute","Temperatur/Tag heute","Temperature",14);
			$this->RegisterVariableFloat("Temp_low_heute","Temperatur/Nacht heute","Temperature",15);
            $this->RegisterVariableFloat("Rain_heute","Niederschlag/h heute","WD_Niederschlag",16);
            $this->RegisterVariableFloat("Temp_high_morgen","Temperatur Tag morgen","Temperature",17);
			$this->RegisterVariableFloat("Temp_low_morgen","Temperatur Nacht morgen","Temperature",18);
            $this->RegisterVariableFloat("Rain_morgen","Niederschlag/h morgen","WD_Niederschlag",19);
            $this->RegisterVariableString("Wettervorhersage_html","Wettervorhersage","HTMLBox",20);
                
		        //Timer zeit setzen
					$this->SetTimerInterval("Update", $this->ReadPropertyInteger("UpdateInterval")*1000);

                //Instanz ist aktiv
				$this->SetStatus(102);
				}
			else
				{
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

   public function Update()
		{

$locationID =  $this->ReadPropertyString("Wetterstation");  // Location ID
$APIkey = $this->ReadPropertyString("API_Key");  // API Key Wunderground

//Wetterdaten vom aktuellen Wetter
$WetterJetzt = Sys_GetURLContent("http://api.wunderground.com/api/".$APIkey."/conditions/q/CA/".$locationID.".json");
$jsonNow = json_decode($WetterJetzt);

$aktuell = "current_observation"; // Aktuelle Wetter daten holen

$icon = $jsonNow->$aktuell->icon_url;
$Temp_now = $jsonNow->$aktuell->temp_c;
$Temp_feel = $jsonNow->$aktuell->feelslike_c;
$Temp_dewpoint = $jsonNow->$aktuell->dewpoint_c;
$Hum_now = $jsonNow->$aktuell->relative_humidity;
$Pres_now = $jsonNow->$aktuell->pressure_mb;
$Wind_deg = $jsonNow->$aktuell->wind_degrees;
$Wind_now = $jsonNow->$aktuell->wind_kph;
$Wind_gust = $jsonNow->$aktuell->wind_gust_kph;
$Rain_now = $jsonNow->$aktuell->precip_1hr_metric;
$Rain_today = $jsonNow->$aktuell->precip_today_metric;
$Solar_now = $jsonNow->$aktuell->solarradiation;
$Vis_now = $jsonNow->$aktuell->visibility_km;
$UV_now = $jsonNow->$aktuell->UV;

//Wetterdaten für die nächsten 2 Tage

$contentNextD = Sys_GetURLContent("http://api.wunderground.com/api/".$APIkey."/forecast/q/".$locationID.".json");
$jsonNextD = json_decode($contentNextD);
$Temp_now = $jsonNow->$aktuell->temp_c;

$Temp_high_heute = $jsonNextD->forecast->simpleforecast->forecastday[0]->high->celsius;
$Temp_low_heute = $jsonNextD->forecast->simpleforecast->forecastday[0]->low->celsius;
$Rain_heute = $jsonNextD->forecast->simpleforecast->forecastday[0]->qpf_allday->mm;
$Temp_high_morgen = $jsonNextD->forecast->simpleforecast->forecastday[1]->high->celsius;
$Temp_low_morgen = $jsonNextD->forecast->simpleforecast->forecastday[1]->low->celsius;
$Rain_morgen = $jsonNextD->forecast->simpleforecast->forecastday[1]->qpf_allday->mm;

// Wettervorhersage String

$html = '<table >
                <tr>
					<td align="center" valign="top"  style="width:130px;padding-left:20px;">
                    Aktuell<br>
                    <img src="'.$icon.'" style="float:left;">
                    <div style="float:right">
                    '.$Temp_now.' °C<br>
                    '.$Hum_now.'<br>

                    </div>
                   <div style="clear:both; font-size: 10px;">Ø Wind: '.$Wind_now.' km/h<br>
                   '.$Temp_feel.' °C gefühlt<br>
                   '.$Pres_now.' hPa<br>
                		Regen 1h: '.$Rain_now.' Liter/m²<br>
                		Sichtweite '.$Vis_now.' km
                    </div>
                </td>';

 foreach($jsonNextD->forecast->simpleforecast->forecastday as $name=> $day){
        if( $this->isToday($day->date->epoch)){
            $Wochentag = "Heute";
        } else {
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
                        <div style="clear:both; font-size: 10px;">Ø Wind: '.$day->avewind->kph.' km/h<br>
                        Niederschlag: '.($day->qpf_allday->mm).' Liter/m²
                        </div>
                    </td>';
    }
    $html .= "</tr>
                </table>";
                
							SetValue($this->GetIDForIdent("Temp_now"),$Temp_now);
							SetValue($this->GetIDForIdent("Temp_feel"), $Temp_feel);
							SetValue($this->GetIDForIdent("Temp_dewpoint"), $Temp_dewpoint);
							SetValue($this->GetIDForIdent("Hum_now"), substr($Hum_now, 0, -1));
							SetValue($this->GetIDForIdent("Pres_now"), $Pres_now);
							SetValue($this->GetIDForIdent("Wind_deg"), $Wind_deg);
                            SetValue($this->GetIDForIdent("Wind_now"), $Wind_now);
							SetValue($this->GetIDForIdent("Wind_gust"), $Wind_gust);
							SetValue($this->GetIDForIdent("Rain_now"), $Rain_now);
							SetValue($this->GetIDForIdent("Rain_today"), $Rain_today);
							SetValue($this->GetIDForIdent("Solar_now"), $Solar_now);
							SetValue($this->GetIDForIdent("Vis_now"), $Vis_now);
                            SetValue($this->GetIDForIdent("UV_now"), $UV_now);
                            SetValue($this->GetIDForIdent("Temp_high_heute"), $Temp_high_heute);
                            SetValue($this->GetIDForIdent("Temp_low_heute"), $Temp_low_heute);
                            SetValue($this->GetIDForIdent("Rain_heute"), $Rain_heute);
                            SetValue($this->GetIDForIdent("Temp_high_morgen"), $Temp_high_morgen);
                            SetValue($this->GetIDForIdent("Temp_low_morgen"), $Temp_low_morgen);
                            SetValue($this->GetIDForIdent("Rain_morgen"), $Rain_morgen);
                            SetValue($this->GetIDForIdent("Wettervorhersage_html"), $html);

}

protected function Var_Pro_Erstellen($name,$ProfileType,$Suffix,$MinValue,$MaxValue,$StepSize,$Digits,$Icon)
{

	if (IPS_VariableProfileExists($name) == false)
	{
    	IPS_CreateVariableProfile($name, $ProfileType);
    	IPS_SetVariableProfileText($name, "", $Suffix);
    	IPS_SetVariableProfileValues($name, $MinValue, $MaxValue,$StepSize);
    	IPS_SetVariableProfileDigits($name, $Digits);
    	IPS_SetVariableProfileIcon($name,$Icon);
	}
}
protected function Var_Pro_WD_WindSpeedKmh()
{
	if (IPS_VariableProfileExists("WD_WindSpeed_kmh") == false)

	{
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
	if (IPS_VariableProfileExists("WD_UV_Index") == false)
	{
        IPS_CreateVariableProfile("WD_UV_Index", 1);
        IPS_SetVariableProfileValues("WD_UV_Index", 0, 12, 0);
        IPS_SetVariableProfileAssociation("WD_UV_Index", 0, "%.1f","",0xC0FFA0);
        IPS_SetVariableProfileAssociation("WD_UV_Index", 3, "%.1f","",0xF8F040);
        IPS_SetVariableProfileAssociation("WD_UV_Index", 6, "%.1f","",0xF87820);
        IPS_SetVariableProfileAssociation("WD_UV_Index", 8, "%.1f","",0xD80020);
        IPS_SetVariableProfileAssociation("WD_UV_Index", 11, "%.1f","",0xA80080);
	}
}

private function VarLogging($VarName,$LogStatus,$Type)
{
    $archiveHandlerID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
    AC_SetAggregationType($archiveHandlerID, $this->GetIDForIdent($VarName), $Type);
    AC_SetLoggingStatus($archiveHandlerID, $this->GetIDForIdent($VarName), $this->ReadPropertyBoolean($LogStatus));
    IPS_ApplyChanges($archiveHandlerID);
}
private function SetTimerMinutes($parentID, $name,$minutes)
    {
    $eid = @IPS_GetEventIDByName($name, $parentID);
        if($eid === false)
	    {
            $eid = IPS_CreateEvent(1);
            IPS_SetParent($eid, $parentID);
            IPS_SetName($eid, $name);
        }
        else
        {
            IPS_SetEventCyclic($eid, 0 /* Keine Datumsüberprüfung */, 0, 0, 2, 2 /* Minütlich */ , $minutes/* Alle XX Minuten */);
            IPS_SetEventScript($eid, 'WD_Update($_IPS["TARGET"]);');
		    IPS_SetEventActive($eid, true);
            IPS_SetHidden($eid, true);
        }

    }
    
private function isToday($time)
{
  $begin = mktime(0, 0, 0);
  $end = mktime(23, 59, 59);
  // check if given time is between begin and end
  if($time >= $begin && $time <= $end)
  {
    return true;
  } else {
    return false;
  }
}

}
?>