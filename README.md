# Trail listing tool
Tool for listing items from [Trail Asset Management](https://trail.fi/). To use this tool you have to get an API Token from the Trail service. Output of this list can be embedded for ie. organizations intranet for realtime asset listing.

## Key features
- two departments supported, at least one have to set as default and is not necessary to define in query
- two model ids supported
- free text search
- one location
- outputting table which contains `manufacturer`, `model`, `description`, `location` and `serial`

## How to use
1. Clone repository to your web server
2. Copy config-sample.php as config.php
3. Edit config.php and paste your personal `API key` and `department id` which you would like to include in search
4. Open https://your_web_server/trail.php in your web browser

## Supported url parameters
- `free` (free text search, use as free=xxx)
- `model1` (first model id, use as model1=12345)
- `model2` (second model id, use as model2=12345)
- `location1` (location id, use as location1=12345)
- `debug` (display query URI and PHP array for debugging purposes, no value needed)
- `department` (overrides default department, use as department=12345)
- `department2` (set second department, use as department2=34567)
- `hide-model` (hides model column, no value needed)
- `hide-serial` (hides serial column, no value needed)
- `hide-manufacturer` (hides manufacturer column, no value needed)
- `hide-itemdesc` (hides item description column, no value needed)
- `clean` (removes TUAS colors from table which are currently there for development purposes, no value needed)
- `summary` (gives summary table instead of device listing, no value needed)

## Example
 https://your_web_server/trail.php?model1=1234567890&location1=12345&free=freetext&debug&hide-serial
