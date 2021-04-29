# SymPriceAlert
Save money :) Products, services... No limit.

Be notified when the desired price is reached.

Made in France.

## Requirements and installation
You need PHP 8.0 (or higher) with some [extensions enabled](https://symfony.com/doc/5.2/setup.html#technical-requirements) and composer.
```sh
git clone git@github.com:duboiss/SymPriceAlert.git
cd SymPriceAlert

composer install
```

Fill the following environement variables in a new `.env.local` file.
You need a Twilio account unless you want to use another provider or not receive SMS.

```env
MAILER_DSN=
MAILER_FROM=
MAILER_TO=
TWILIO_DSN=
PHONE_NUMBER=+33600000000
```

## Configuration
Each website you want to follow need a file in `src/DataProducts` folder.
Two keys are expected:
* selector: a [DomCrawler selector](https://symfony.com/doc/current/components/dom_crawler.html#node-filtering)
* products: an array of products

For each product, three keys are expected:
* title: the name of the product or service to analyze (displayed in the logs)
* url: the page to analyze
* desiredPrice: the price at which you wish to be notified (in euros, without spaces and commas)

A fourth is automatically created if you are notified, **alertedPrice**.

## Usage
An unique command: `bin/console app:prices:check`.

A common use case is to run this command with a CRON, once a day for example.

It parses all urls, analyzes the price (if displayed) and compares it to the desired price.
If the price is equal or lower than the desired price, three options:
1) The user has already been notified for this new price, nothing happens.
2) The user is notified once for this new price.
3) The user has already been notified for this price drop but this one is even more important, he is notified.

## Exemple with Amazon
Just create a **amazon.json** file in the `src/DataProducts` folder with the following structure and it's done.
```sh
# src/DataProducts/amazon.json
{
    "selector": "#priceblock_ourprice",
    "products": [
        {
            "title": "Echo dot 4 Alexa",
            "url": "https://www.amazon.fr/nouvel-echo-dot-4e-generation-enceinte-connectee-avec-alexa-anthracite/dp/B084DWG2VQ/",
            "desiredPrice": 35
        },
        {
            "title": "Xbox Series S",
            "url": "https://www.amazon.fr/Xbox-Console-Next-Gen-compacte-digitale/dp/B087VM5XC6/",
            "desiredPrice": 220
        }
    ]
}
```

## How it works
SymPriceAlert uses mainly [HTTP Client](https://symfony.com/doc/5.2/http_client.html), [DomCrawler](https://symfony.com/doc/5.2/components/dom_crawler.html) and [Notifier](https://symfony.com/doc/5.2/notifier.html) components.

A request is made on the page to be analyzed to retrieve the current price.

A comparison is then made with the desired price. If necessary, a notification is sent to the user to warn him of this price drop.

A sleep is performed between each request to avoid the effects of rate limiting (captcha etc).

### Notifications
By default an email and a SMS are sent but you can easily change this. The channels are adjustable in `config/packages/notifier.yaml`.
The cannel used is `hight`.

Look at the [documentation](https://symfony.com/doc/5.2/notifier.html) for more information.
