# Wunderground
Modul um Wetterdaten von Wunderground.com mittels Json abzurufen.
Informationen zur API findet ihr hier. [Klick](https://www.wunderground.com/weather/api/d/docs)


### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Abrufen der aktuellen Wetterdaten und speichern in Variablen
* Aktivieren bzw. Deaktivierung des Variablen Loggins direkt aus dem Modul heraus.
* Eigene Icon Sets oder die von [Wunderground](https://www.wunderground.com/weather/api/d/docs?d=resources/icon-sets).
* Unterscheidung der Icons (TAG/Nacht)
* Abrufen der Wetterdaten für die nächsten 24 Stunden (Stundenweise) Daten werden in einem Array gespeichert welches JSON decodiert ist.
  Zur eigenen Nutzung in Skripten.
* Abrufen der Wetterdaten für die nächsten 3 Tage (Tagesweise) Daten werden in einem Array gespeichert welches JSON decodiert ist.
  Zur eigenen Nutzung in Skripten.
* Abruf von Wetterwarnungen. Daten werden in einem Array gespeichert welches JSON decodiert ist.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x
- Wunderground API-Key ([Information um einen Account zu erstellen](https://www.wunderground.com/weather/api/))

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`https://github.com/Matzel687/Wunderground.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'WundergroundWetter'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.  

__Konfigurationsseite__:

Name                              | Beschreibung
--------------------------------- | ---------------------------------
Wetterstation                     | Wetterstation, von der die Daten entnommen werden sollen. Den Namen der Wetterstation könnt ihr auf der www.wunderground.com Seite herausfinden. Zulässig sind Stationsnamen pws:XXXXXX , Koordinaten zws:XXXXXX oder Städtenamen.
API Key                           | Wunderground API-Key. Kann auf der Wunderground Homepage nach Registrierung angefordert werden. "More"->"Weather API for Developers".
Wetter Icon Pfad                  | Hier könnt ihr den Pfad zu euren eigenen Wetter Icons angeben oder die Icons von [Wunderground](https://www.wunderground.com/weather/api/d/docs?d=resources/icon-sets) nutzen. Bei eigenen Icons müssen diese im  "\IP-Symcon\webfront\user" Ordner liegen.
Icon Datei Type                   | Dateityp von euren eigenen Icons z.B. Jpeg, Png, Gif. Wenn die Wunderground Icons verwendet werden, muss hier "Gif" eingetragen werden.
Update Wetterdaten alle X Minuten | Hier könnt ihr einstellen in welchen Zeitraum die Wetterdaten abgerufen werden sollen. (Ihr habt 500 Abrufe pro Tag umsonst)
Update Wetterwarnung alle X Minuten | Hier könnt ihr einstellen in welchen Zeitraum die Wetterwarnungen abgerufen werden sollen. (Dies ist ein separater Abruf der API und wird extra von eurem Tageskontingent abgezogen)
Sunrise                           | Variable für den Sonnenaufgang z.B.  aus der Location Control. Wird benötigt um die Icons umzuschalten Tag/Nacht Modus
Sunset                            | Variable für den Sonnenuntergang z.B.  aus der Location Control. Wird benötigt um die Icons umzuschalten Tag/Nacht Modus

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

Name                    | Typ       | Beschreibung
----------------------- | --------- | ----------------
UpdateWetterDaten       | Event     | Ruft die Wetterdaten alle x Minuten ab
UpdateWetterWarnung     | Event     | Ruft die Wetterwarnungen alle x Minuten ab
Temperatur              | Float     | Angabe in °C
Temperatur gefühlt      | Float     | Angabe in °C
Temperatur Taupunkt     | Float     | Angabe in °C
Luftfeuchtigkeit        | Float     | Angabe in %
Luftdruck               | Float     | Angabe in hPa
Windrichtung            | Float     | Angabe in Himmelsrichtungen
Windgeschwindigkeit     | Float     | Angabe in km/h
Windböe                 | Float     | Angabe in km/h
Niederschlag/h          | Float     | Angabe in Liter/m²
Niederschlag Tag        | Float     | Angabe in Liter/m²
Sonnenstrahlung         | Float     | Angabe in W/m²
Sichtweite              | Float     | Angabe in km
UV Strahlung            | Integer   | Informationen: [UVIndex Erklärung](https://www.wunderground.com/resources/health/uvindex.asp)
WetterIcon              | String    | Icon Link für das Aktuelle Wetter.
WeatherNextDaysData     | String    | Wetterdaten für die nächsten 3 Tage. Die Daten sind JSON decodiert. 
WeatherNextHoursData    | String    | Wetterdaten für die nächsten 24 Stunden. Die Daten sind JSON decodiert.
WeatherAlerts           | String    | Wetterwarnungen. Die Daten sind JSON decodiert.
Text                    | String    | Wettertext z.B. "Leichter Regen"

##### Profile:

Name             | Typ
---------------- | -------
WD_WindSpeed_kmh | Float
WD_Niederschlag  | Float
WD.Sonnenstrahlung| Float
WD.Sichtweite    | Float
WD_UV_Index      | Integer

### 6. WebFront

Über das WebFront werden die Variablen angezeigt. Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz

####*1. Funktion um die aktuellen Wetterdaten auszugeben
`Array WD_Weathernow(integer $ModulID, String $Key);`

$Key                    | Beschreibung
----------------------- | --------- 
'all'                   | Gibt alle unten stehenden Variablen als Array aus.     
'Temp_now'              | Aktuelle Temperatur
'Temp_feel'             | Gefühle Temperatur     
'Temp_dewpoint'         | Taupunkt      
'Hum_now'               | Luftfeuchtigkeit     
'Pres_now'              | Luftdruck    
'Wind_deg'              | Windrichtung     
'Wind_now'              | Windstärke     
'Wind_gust'             | Windböe    
'Rain_now'              | Regen Jetzt     
'Rain_today'            | Regen Tagesverlauf
'Solar_now'             | Sonnenenergie    
'Vis_now'               | Sichtweite 
'UV_now',               | UV Wert 
'Icon'                  | Icon
'Text'                  | Wetter Text

// Beispiel Ausgabe
`print_r(WD_Weathernow($ModulID, "all"));`

```
Array
(
    
    [Temp_now] => 15.8
    [Temp_feel] => 15.8
    [Temp_dewpoint] => 15
    [Hum_now] => 94
    [Pres_now] => 1015
    [Wind_deg] => 161
    [Wind_now] => 8
    [Wind_gust] => 17.7
    [Rain_now] => 3
    [Rain_today] => 5
    [Solar_now] => 33
    [Vis_now] => 9
    [UV_now] => 1
    [Icon] => user\Wetter_Icons\rain.png
    [Text] => Leichter Regen
)
```

####*2. Funktion um die Wetterdaten für die nächsten 3 Tage auszugeben
`Array WD_Weathernextdays(integer $ModulID,);`

// Beispiel Ausgabe
`print_r(WD_Weathernextdays($ModulID));`
```
Array
(
    [0] => Array
        (
            [Date] => 1466442000
            [Text] => Regen. Tiefsttemperatur 15C.
            [Icon] => user\Wetter_Icons\rain.png
            [TempHigh] => 20
            [TempLow] => 15
            [Humidity] => 91
            [Wind] => 6
            [MaxWind] => 31
            [Rain] => 6
            [Pop] => 100
        )
    [1] => Array
          .....
    [2] => Array
          .......
    [3] => Array
          ......
)
```
####*3. Funktion um die Wetterdaten für die nächsten 24 Stunden auszugeben
`Array WD_Weathernexthours(integer $ModulID,);`

// Beispiel Ausgabe
`print_r(WD_Weathernexthours($ModulID));`
```
Array
(
    [0] => Array
        (
            [Date] => 1466442000
            [Text] => Regen
            [Icon] => user\Wetter_Icons\rain.png
            [Temp] => 17
            [Tempfeel] => 17
            [Tempdewpoint] => 14
            [Humidity] => 88
            [Wind] => 16
            [Pres] => 1015
            [Rain] => 6
            [Pop] => 100
        )
        ....
      [23] => Array
        (
            [Date] => 1466524800
            [Text] => Wolkig
            [Icon] => user\Wetter_Icons\mostlycloudy.png
            [Temp] => 21
            [Tempfeel] => 21
            [Tempdewpoint] => 15
            [Humidity] => 69
            [Wind] => 11
            [Pres] => 1019
            [Rain] => 0
            [Pop] => 12
        )
)
```
####*4. Funktion um die Wetter Warnungen auszugeben
`Array WD_Weatheralerts(integer $ModulID,);`

// Beispiel Ausgabe
`print_r(WD_Weatheralerts($ModulID));`
```
Array
(
    [0] => Array
        (
            [Date] => 2017-04-22 07:00:15 GMT
            [Expires] => 2017-04-22 17:00:00 GMT
            [Type] => WND
            [Name] => Wind
            [Color] => Yellow
            [Text] => Potential disruption due to wind from 8AM CEST SAT until 7PM CEST SAT
        )

)
```