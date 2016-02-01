<?
    // Klassendefinition
class PI_Monitor extends IPSModule
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

			$this->RegisterPropertyString("IPS_Pfad", "/usr/share/symcon");
			$this->RegisterPropertyString("Netzwerkkarte", "eth0");
			$this->RegisterPropertyInteger("UpdateInterval", 20);
     		$this->RegisterTimer("Update", 0, 'PIMonitor_Update($_IPS["TARGET"]);');
            //Variable Änderungen aufzeichnen
            $this->RegisterPropertyBoolean("logCPU_idle", false);
            $this->RegisterPropertyBoolean("logCPU_volts", false);
            $this->RegisterPropertyBoolean("logCPU_temp", false);
            $this->RegisterPropertyBoolean("logHDD_total", false);
            $this->RegisterPropertyBoolean("logHDD_used", false);
            $this->RegisterPropertyBoolean("logHDD_percent", false);
            $this->RegisterPropertyBoolean("logHDD_symcon", false);
            $this->RegisterPropertyBoolean("logRAM_total", false);
            $this->RegisterPropertyBoolean("logRAM_used", false);
            $this->RegisterPropertyBoolean("logRAM_percent", false);
  		 }

        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
   public function ApplyChanges()
		{
         // Diese Zeile nicht löschen
         parent::ApplyChanges();

			//Variablenprofil anlegen
			if (!IPS_VariableProfileExists("Megabyte"))
				{
					IPS_CreateVariableProfile("Megabyte", 2);
					IPS_SetVariableProfileValues("Megabyte", 0, 0, 2);
					IPS_SetVariableProfileText("Megabyte",""," MB");
				}

			if (($this->ReadPropertyString("IPS_Pfad") != "") AND ($this->ReadPropertyString("Netzwerkkarte") != ""))
				{
					//Variablen erstellen
					$this->RegisterVariableFloat("CPU_idle","CPU-Auslastung","Humidity.F",1);
					$this->RegisterVariableFloat("CPU_volts","CPU-Spannung","Volt",2);
					$this->RegisterVariableFloat("CPU_temp","CPU-Temperatur","Temperature",3);
					$this->RegisterVariableFloat("HDD_total","Gesamt Speicherplatz","Megabyte",4);
					$this->RegisterVariableFloat("HDD_used","Belegter Speicherplatz","Megabyte",5);
					$this->RegisterVariableFloat("HDD_percent","HDD-Belegung","Humidity.F",6);
					$this->RegisterVariableFloat("HDD_symcon","IPS-Speicherbelegung","Megabyte",7);
					$this->RegisterVariableFloat("RAM_total","Gesamt RAM","Megabyte",8);
					$this->RegisterVariableFloat("RAM_used","Benutzer RAM","Megabyte",9);
					$this->RegisterVariableFloat("RAM_percent","RAM-Auslastung","Humidity.F",10);
					$this->RegisterVariableString("System_Info","System Informationen","HTMLBox",11);
		        //Timer zeit setzen
					$this->SetTimerInterval("Update", $this->ReadPropertyInteger("UpdateInterval")*1000);
				}
			else
				{
				//Instanz ist inaktiv
				$this->SetStatus(104);
				}
            //Variablen Logging Aktivieren / Deaktivieren
            $archiveHandlerID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
            AC_SetLoggingStatus($archiveHandlerID, $this->GetIDForIdent("CPU_idle"), $this->ReadPropertyBoolean("logCPU_idle"));
            AC_SetLoggingStatus($archiveHandlerID, $this->GetIDForIdent("CPU_volts"), $this->ReadPropertyBoolean("logCPU_volts"));
            AC_SetLoggingStatus($archiveHandlerID, $this->GetIDForIdent("CPU_temp"), $this->ReadPropertyBoolean("logCPU_temp"));
            AC_SetLoggingStatus($archiveHandlerID, $this->GetIDForIdent("HDD_total"), $this->ReadPropertyBoolean("logHDD_total"));
            AC_SetLoggingStatus($archiveHandlerID, $this->GetIDForIdent("HDD_used"), $this->ReadPropertyBoolean("logHDD_used"));
            AC_SetLoggingStatus($archiveHandlerID, $this->GetIDForIdent("HDD_percent"), $this->ReadPropertyBoolean("logHDD_percent"));
            AC_SetLoggingStatus($archiveHandlerID, $this->GetIDForIdent("HDD_symcon"), $this->ReadPropertyBoolean("logHDD_symcon"));
            AC_SetLoggingStatus($archiveHandlerID, $this->GetIDForIdent("RAM_total"), $this->ReadPropertyBoolean("logRAM_total"));
            AC_SetLoggingStatus($archiveHandlerID, $this->GetIDForIdent("RAM_used"), $this->ReadPropertyBoolean("logRAM_used"));
            AC_SetLoggingStatus($archiveHandlerID, $this->GetIDForIdent("RAM_percent"), $this->ReadPropertyBoolean("logRAM_percent"));
            IPS_ApplyChanges($archiveHandlerID);
            
            copy  (IPS_GetKernelDir()."modules/IPS_Module/PI_Monitor/Raspi-PGB001.png", IPS_GetKernelDir()."webfront/user/Raspi-PGB001.png");
            

   	}

   public function Update()
		{
        		$IPS_directory = $this->ReadPropertyString("IPS_Pfad");
        		$networkcard = $this->ReadPropertyString("Netzwerkkarte");
        		$CPU_idle =exec("mpstat| grep all| awk '{print $12}'"); // CPU Auslastung %idle
        		$CPU_temp = substr(exec('vcgencmd measure_temp'), 5, 4); //Temperatur CPU
        		$CPU_volts = substr(exec("vcgencmd measure_volts"),5,4); //CPU Spannung
        		$RAM_total = exec("free -m| grep Mem | awk '{print $2}'"); //Freier RAM
        		$RAM_used = exec("free -m| grep Mem | awk '{print $3}'"); //Benutzer RAM
        		$HDD_total = exec("df -m | grep /dev/root | awk '{print $2}'"); // Gesamt Speicherplatz SD-Karte
        		$HDD_used = exec("df -m | grep /dev/root | awk '{print $3}'"); // Belegter Speicherplatz SD-Karte
        		$HDD_percent = substr(exec("df -m | grep /dev/root | awk '{print $5}'"),-3,2); // Belegter Speicherplatz in % SD-Karte
        		$HDD_symcon = exec("du -sh -m $IPS_directory| awk '{print $1}'"); // Verzeichnissgröße IPS
        		$LAN_IP = substr(exec("/sbin/ifconfig $networkcard | grep 'inet Adresse'| awk '{print $2}'"),8); // IP Adresse
        		$Linux_Vers = exec('uname -snr'); // Linux Version
        		$SSH_Log = substr(exec("who -q | grep '#' | awk '{print $2}'"),6); // Anzahl SSH Verbindungen
        		$SSH_Connection = substr(exec("who -s"),14); // // SSH Verbindungen von Client xy

				$html = ' <table width="100%" border="0" cellpadding="0" cellspacing="2" align="center" valign="top" >
 							<tr >
							<td align="center" valign="top"  width="100px"; rowspan="6">
							<img src="user/Raspi-PGB001.png" style="float:left"; width="110px">
							</tr>
 							<tr>
 							<td align="left" valign="top">IP Adresse:</td>
  							<td align="right" valign="top">'.$LAN_IP.'</td>
 							</tr>
 							<tr>
  							<td align="left" valign="top">System Online seit:</td>
  							<td align="right" valign="top">'.$this->uptime().'</td>
							</tr>
 							<tr>
 							<td align="left" valign="top">Linux Version:</td>
  							<td align="right" valign="top">'.$Linux_Vers.'</td>
 							</tr>
  							<tr>
  							<td align="left" valign="top">SSH Verbindung:</td>
  							<td align="right" valign="top">'.$SSH_Connection.'</td>
 							</tr>
  	 						<tr>
  							<td align="left" valign="top">IPS Version:</td>
  							<td align="right" valign="top">'.IPS_GetKernelVersion().'</td>
 							</tr>
							</table>';


							SetValue($this->GetIDForIdent("CPU_idle"), 100 - $CPU_idle);
							SetValue($this->GetIDForIdent("CPU_volts"), $CPU_volts);
							SetValue($this->GetIDForIdent("CPU_temp"), $CPU_temp);
							SetValue($this->GetIDForIdent("HDD_total"), $HDD_total);
							SetValue($this->GetIDForIdent("HDD_used"), $HDD_used);
							SetValue($this->GetIDForIdent("HDD_percent"), $HDD_percent);
							SetValue($this->GetIDForIdent("HDD_symcon"), $HDD_symcon);
							SetValue($this->GetIDForIdent("RAM_total"), $RAM_total);
							SetValue($this->GetIDForIdent("RAM_used"), $RAM_used);
							SetValue($this->GetIDForIdent("RAM_percent"),($RAM_used/$RAM_total)*100);
							SetValue($this->GetIDForIdent("System_Info"), $html);
   	}

	public function uptime()
		{
			$upSeconds = exec("/usr/bin/cut -d. -f1 /proc/uptime");
			$uptimeDays = floor($upSeconds /86400);
			$uptimeHours = $upSeconds /3600 % 24;
			$uptimeMin = $upSeconds /60 % 60;
			$uptime = " $uptimeDays Tag(e) $uptimeHours Stunde(n) $uptimeMin Minute(n)";

			return $uptime;
		}

}
?>