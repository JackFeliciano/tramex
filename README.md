# WeTrade

## What the project is

WeTrade is a freight exchange. A shipper posts a load on the board. The load carries an id, an origin, a destination, a weight in kilograms, a pickup day and a ceiling in cents.

Carriers bid an amount in cents and a pickup day. Each bid also carries the load id, the carrier and the moment it arrived. The exchange refuses a bid above the ceiling. The exchange refuses a bid that arrives after the pickup day. The board ranks the bids by amount. The lowest amount wins the load. The board makes the award final.

The code models this flow in plain PHP classes. The Board class runs the exchange. The Sample class builds the demo board with load TR-403.

## How to run it

The demo runs on the built-in PHP server. The command below starts the server from the repository root on port 8111.

```
php -S 127.0.0.1:8111 -t showroom showroom/index.php
```

The router reads the base path from the TRAMEX_BASE environment variable and strips that base from the request path. The nginx location block serves the site under /tramex/. The variable takes this value:

```sh
TRAMEX_BASE=/tramex/
```

The command below runs the test suite.

```
php tests/run.php
```

## What the routes answer

The page route answers at /. The front controller returns HTML. The HTML shows the open loads, the awarded carrier, the saving against the ceiling and the bid count on each load.

The probe route answers at /health. The front controller returns JSON. The JSON carries the keys ok, loads and awards.

## How the layout is arranged

The file autoload.php sits at the root and loads the classes. src/Home/ holds the model. The model contains Load.php, Bid.php, Board.php and Sample.php. src/App/ holds the router, the front controller and the HTML. showroom/ holds the demo entry file and the stylesheet. tests/ holds the hand-written runner, the assertion helper and the test file. deploy/ holds the service unit and the web server location block.

## The state of the suite

The suite runs 22 checks. The hand-written runner executes the checks. The assertion helper verifies each result. The command below starts the runner.

```
php tests/run.php
```

## Licence

The repository uses the Mozilla Public License 2.0. The licence lets anyone use, modify and distribute the covered files. The licence keeps any modified covered file under its own terms. The licence also lets a larger work mix the covered files with code under other terms.
