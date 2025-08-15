![Solvians](/doc/images/Solvians-Logo.png)

#Solvians Backend Challenge


## Prerequisites
- MacOS ⌘ or Linux or WSL (Windows subsystem for Linux)
- Docker
- Terminal
- Git


## Starting local development environment
To start the local development environment, run the following command in the terminal at the root directory of the project:
```
./dev-tools.sh provision
```

## Stop the local development environment
Execute:
```
./dev-tools.sh down
```


## Run unit tests
When the containers are up and running, execute:
```
./dev-tools.sh test
```

<hr />

### Notes
- Please spend about 2-4 hours working on this project.
- If you are unable to complete all the tasks, please submit what you have completed.
- Please cover your implementation with unit tests!

# Tasks

**There are 2 tasks:** 

##### 1) Find and fix the bug in existing code (fix the value of `requestTime` in API response)
In the API response of [http://localhost:8013/list/bid/8.3?limit=2](http://localhost:8013/list/bid/8.3?limit=2)  
(you can change the `limit` parameter to get more results)  
The API response currently looks like this:   
```json
{
  "status": "OK",
  "state": "OK",
  "count": 2,
  "data": [
    {
      "expiry": "2020-01-23T11:25:21+00:00",
      "isin": "isin151406",
      "bid": 8.92,
      "ask": 2.71
    },
    {
      "expiry": "2020-08-01T11:25:21+00:00",
      "isin": "isin162787",
      "bid": 8.99,
      "ask": null
    }
  ],
  "requestTime": "no start time"
}
```  

The key `requestTime` is for some reason not reporting time in milliseconds.  
Make a fix so that the key `requestTime` has the value in milliseconds for the time it took for the request to be completed. Also fix/alter related tests if any.

<hr />

#### 2) Implement a new API endpoint

##### Business Logic:
A portfolio is a list of products (also called instruments) that belong to a user.<br />
The portfolio list contains products that a certain user purchased along with the quantity of the respective product.<br /><br />
The `purchasePrice` is the price at which the user bought the product.<br />
The `currentSellPrice` is the price at which the user can currently sell the product.<br />
The `currentBuyPrice` is the price at which the user can currently buy the product.<br />
The `quantity` is the amount of the purchased product.

##### Tasks:

Please create a new HTTP `GET` API endpoint `/portfolio`. <br />Once the API endpoint is called, it should perform the following steps:

<b>A)</b> Read two lists from two separate JSON files in a directory (`/data/source/instruments-data.json` and `/data/source/instruments-properties.json`)<br /><br />
<b>B)</b> Merge the two lists considering the `isin` (An ISIN is an international unique identifier for a financial instrument e.g `CH123456789` ) as the unique identifier in both list to get the expected JSON output:<br />
```js
{
    "CH123456789": {
      "isin": "CH123456789",
      "quantity": 2,
      "structure": 112,
      "buyPrice": "90",
      "currentSellPrice":  200,
      "currentBuyPrice":  1000,
      "currency": "EUR"
    },
    "CH123452189": {
      "isin": "CH123452189",
      "quantity": 2,
      "structure": 111,
      "buyPrice": "100",
      "currentSellPrice":  200,
      "currentBuyPrice":  1000,
      "currency": "EUR"
    },
    "CH1234567111": {
      "isin": "CH1234567111",
      "quantity": 2,
      "structure": 112,
      "buyPrice": "100",
      "currentSellPrice":  200,
      "currentBuyPrice":  1000,
      "currency": "EUR"
    }
    // ... more items
  }

```

<b>C)</b> If the API endpoint `/portfolio` is called with query parameters `showProfits=1`, Filter the list from previous step to provide the instruments that made profit. <br /><br />
If the API endpoint `/portfolio` is called with query parameters `structureId={structure}`, Filter the list from previous step to provide the instruments that belong to the structure specified in the query parameter. <br /><br />
Additionally, calculate the profit percentage and provide the value under `profitPercentage` key in the response.<br />  

```js
{
        "CH1334567111": {
                "quantity": 3,
                "structure": 112,
                "isin": "CH1334567111"
                "buyPrice": "100",
                "currentSellPrice": 200,
                "currentBuyPrice": 1000,
                "currency": "EUR",
                "profit": 100,
                "profitPercentage": "100%"
        },
        "CH5334567189": {
                "quantity": 5,
                "isin": "CH5334567189"
                "structure": 112,
                "buyPrice": "1000",
                "currentSellPrice": 1000.1,
                "currentBuyPrice": 900,
                "currency": "EUR",
                "profit": 0.1,
                "profitPercentage": "0.01%"
        },
        "CH1334567189": {
                "quantity": 5,
                "isin": "CH1334567189"
                "structure": 112,
                "buyPrice": "500",
                "currentSellPrice": 1500,
                "currentBuyPrice": 500,
                "currency": "EUR",
                "profit": 1000,
                "profitPercentage": "200%"
        }
}
```
<b>Formulas:</b> <br />
Profit = `currentSellPrice` - `buyPrice` <br />
Profit percentage = Profit * 100 / `buyPrice`

<hr />

# Submit your work
Please perform the following steps to deliver your work:
* Stop the docker containers by executing `./dev-tools.sh down`
* Commit your changes `git commit`
* Remove the vendor folder by executing: `rm -rf vendor`
* Compress the folder
* Send us back the compressed folder via email

<hr />

## More about the project
* Uses PHP 7.4
* Uses the Laminas framework (Zend 3): https://docs.laminas.dev/
* Xdebug is enabled by default
* MongoDb v4.4

<hr />

## Troubleshooting

### Folder permissions
* make sure directory `./data/` and `./tests/` are world writable (perms: 0777)
* By default, mongodb data file is not mounted, but it can be mounted by uncommenting the respective line in `./docker/docker-compose.yml` file.

### Sync system time with time servers
If you are setting up the project on linux, please execute the following command to synchronize the system time with time servers:

```
sudo systemctl start ntpd
```
# WSD
