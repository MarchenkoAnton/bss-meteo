# BSS Meteo Widget for TYPO3

MeteoSwiss NOWCAST weather widget for TYPO3 13.4.
Supports compact forecast and icon rendering.

## Installation
composer require bermuda/bss-meteo-widget

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
