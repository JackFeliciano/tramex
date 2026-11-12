# Roadmap

The repository holds a freight exchange today. The shipper posts a load with an origin, a destination, a weight in kilograms, a pickup day and a ceiling in cents. The carriers bid an amount in cents and a pickup day. The board takes the bids, ranks them by amount, awards the load to the lowest bid and makes the award final. The page route serves the board as HTML. The probe route serves the health JSON. The suite runs 22 checks. A contributor could extend the code in the ways below. The items carry no ranking.

1. The board keeps every load and bid in memory. A contributor could add a durable store for loads and bids. The work would touch src/Home/Board.php and add a store class under src/Home/.

2. The exchange accepts any carrier name on a bid. A contributor could add carrier accounts with a sign-in step. The work would touch the router and the front controller in src/App/ and add an account class under src/Home/.

3. The board ranks bids by amount alone. A contributor could break ties between equal amounts with the arrival moment on each bid. The work would touch src/Home/Board.php and the test file in tests/.

4. The board leaves an open load standing after its pickup day passes. A contributor could add a timed task that closes each open load once its pickup day passes. The work would touch src/Home/Board.php and the service unit in deploy/.

5. The page route serves the board as HTML alone. A contributor could add a JSON endpoint that lists the loads and the bids for other programs. The work would touch the router in src/App/ and the board methods in src/Home/Board.php.
