<?
    // Klassendefinition
    class PI_Monitor extends IPSModule {
        // Der Konstruktor des Moduls
        // Überschreibt den Standard Kontruktor von IPS
        public function __construct($InstanceID) {
            // Diese Zeile nicht löschen
            parent::__construct($InstanceID);
 
            // Selbsterstellter Code
        }

        public function Create() {
            // Diese Zeile nicht löschen.
            parent::Create();
			
			$this->RegisterPropertyString("IPS_Pfad", "/usr/share/symcon");
			$this->RegisterPropertyString("Netzwerkkarte", "eth0");
			$this->RegisterPropertyInteger("UpdateInterval", 20);
        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
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
			$this->RegisterVariableFloat("CPU_idle","CPU-Auslastung","Temperature",1);
			$this->RegisterVariableFloat("CPU_volts","CPU-Spannung","Volt",2);
			$this->RegisterVariableFloat("CPU_temp","CPU-Temperatur","Temperature",3);
			$this->RegisterVariableFloat("HDD_total","Gesamt Speicherplatz","Megabyte",4);
			$this->RegisterVariableFloat("HDD_used","Belegter Speicherplatz","Megabyte",5);
			$this->RegisterVariableFloat("HDD_percent","HDD-Belegung","Humidity.F",6);
			$this->RegisterVariableFloat("HDD_syncom","IPS-Speicherbelegung","Megabyte",7);
			$this->RegisterVariableFloat("RAM_total","Gesamt RAM","Megabyte",8);
			$this->RegisterVariableFloat("RAM_used","Benutzer RAM","Megabyte",9);
			$this->RegisterVariableFloat("RAM_percent","RAM-Auslastung","Humidity.F",10);
			$this->RegisterVariableString("System_Info","System Informationen","HTMLBox",11);
            
		        //Timer erstellen
		//$this->SetTimerInterval("Update", $this->ReadPropertyInteger("UpdateInterval"));
		//IPS_SetHidden($this->GetIDForIdent("Update"), true);
		
        
			}
			else
			{
				//Instanz ist inaktiv
				$this->SetStatus(104);
			}
        }		
    }
?>