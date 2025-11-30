# BSS Meteo Widget for TYPO3

MeteoSwiss NOWCAST weather widget for TYPO3 13.4.
Supports compact forecast and icon rendering.

## Usage
Create a new content element “Weather Widget MeteoSwiss” and set point_id + parameters in FlexForm.

## Data Storage
JSON forecast files will be stored in:
public/fileadmin/meteoswiss/{frequency}/{param}/{point_id}.json

## License
This extension is licensed under GPL-2.0-or-later.  
© 2025 Bermuda Software Solutions — anton.marchenko@bermuda-software.ch
Weather data © MeteoSwiss — CC BY 4.0
Icons © Weather Icons — MIT License

### Requirements

- TYPO3 CMS 13.4
- PHP 8.2 or higher
- File system write access to public/fileadmin/
- Scheduler enabled (typo3/cms-scheduler)

### Installation 
`composer require bermuda-software/bss-meteo-widget`

Activate the extension in the TYPO3 Extension Manager.

### One-time Setup (required before scheduler)

Before running the scheduler, the directory structure for storing forecast data must be created:

`vendor/bin/typo3 bss-meteo:setup`

This command creates:

public/fileadmin/meteoswiss/
│
├── hourly/                        ← parameters updated hourly
│   ├── tre200h0/                  ← Air temperature 2m – hourly mean
│   ├── fu3010h0/                  ← Wind speed – hourly mean
│   ├── fu3010h1/                  ← Wind gusts – hourly maximum
│   ├── dkl010h0/                  ← Wind direction – hourly mean
│   ├── rre150h0/                  ← Precipitation – hourly sum
│   ├── nprolohs/                  ← Low cloud cover – hourly mean
│   ├── npromths/                  ← Medium cloud cover – hourly mean
│   ├── nprohihs/                  ← High cloud cover – hourly mean
│   ├── sre000h0/                  ← Sunshine duration – hourly value
│   ├── gre000h0/                  ← Global radiation – hourly mean
│   ├── zprfr0hs/                  ← Freezing level – hourly value
│   └── (empty until downloader writes files)
│
├── 3hourly/                       ← parameters updated every 3 hours
│   ├── jww003i0/                  ← Weather symbol – 3h forecast
│   ├── rp0003i0/                  ← Precipitation probability – 3h
│   └── (empty)
│
├── daily/                         ← daily forecast parameters
│   ├── jp2000d0/                  ← Weather symbol – daily forecast
│   ├── rka150p0/                  ← Precipitation – daily sum
│   ├── tre200dn/                  ← Daily minimum temperature
│   ├── tre200dx/                  ← Daily maximum temperature
│   └── (empty)
│
├── unknown/                       ← fallback for uncategorized parameters
│
├── weather_icons/                 ← weather icons (SVG/PNG)
│   └── symbol_map.json            ← icon mapping file (copy)
│
├── stations.json                  ← minimal station catalogue
├── symbol_map.json                ← icon mapping (copy)
└── .installed                     ← setup marker file

After .installed is created, the scheduler can safely update forecast files.

### Usage in TYPO3

Create a new content element:
Weather Widget MeteoSwiss

Configure:

Weather station (point_id)

Weather parameters (comma-separated list)

Save the content element

Add Scheduler task:
MeteoSwiss Forecast Updater

Recommended execution interval:
every 30 minutes

No additional maintenance is required.

### Data Storage

Each parameter is stored in a separate JSON file — this ensures stability even when MeteoSwiss updates different parameters at different intervals.

`public/fileadmin/meteoswiss/{frequency}/{param}/{point_id}.json`


Examples:

public/fileadmin/meteoswiss/hourly/tre200h0/405.json
public/fileadmin/meteoswiss/daily/rka150p0/63.json


Update behavior:

Condition 	        Result
Forecast changed	JSON updated
Forecast unchanged	JSON not modified
CSV temporarily missing	JSON preserved — never deleted
🔗 MeteoSwiss Data Source (NOWCAST API)

The widget downloads data from the official MeteoSwiss Open Government Data endpoint:

https://data.geo.admin.ch/ch.meteoschweiz.ogd-local-forecasting/{param}/{timestamp}/{param}_{timestamp}.csv

### ⏱ Scheduler Logic 

Operates only on current date

Generates timestamps in sequence:
`0000 → 2300 → 2200 → 0200 → … → 0100`

For each parameter:
- Build download URL using {param} + {timestamp}
- Download CSV
- Parse rows matching the configured point_id
- Write JSON only when data differs from existing JSON

If MeteoSwiss does not yet provide a timestamp → next timestamp

If parameter has no more new timestamps → parameter skipped silently

No fatal errors stop the scheduler — the widget always keeps the latest forecast available.

### Why every parameter has its own JSON file

MeteoSwiss updates parameters with different frequencies:

Frequency	Update interval
Hourly parameters	every 1 hour
3-hourly parameters	every 3 hours
Daily parameters	every 24 hours

In earlier versions, storing all parameters in one JSON file caused data loss for parameters that were not yet updated.
The current model — one JSON per parameter — is stable and production-proved.

### Logging

The TYPO3 Scheduler log reports:
- number of CSV downloads
- which parameters updated
- unchanged parameters
- skipped parameters
- missing/future timestamps (not errors)

No exceptions are thrown when external data is temporarily unavailable.

