# District Integrations: Spydus

Type: API Integration

Spydus is library manangement software, this module integrates with Spydus API to handle things such as
book listings, etc.

More info: 
* https://www.civica.com/en-au/container---product-pages/spydus-integrated-library-management-solution/
* https://www.civica.com/globalassets/7.document-downloads/3.au-docs/product-information/web_spydus_brochure.pdf

## What it does

This gets the content via scraping the DOM of the Spydus library web application. In the future, this
should consider a more robust implementation via an proper REST API. But for now it does the job.

## How to use

1. Visit District integrations page
2. Edit Spydus settings
    * Identify the listing urls on the Spydus site
    * Add a Id, Label and the URL to the settings page
    * These will be scraped and can be selected when adding a block
3. Add a "District Integrations Spydus" block to your page
    * Select the type (these are what was defined in the settings page)
    * Select the limit of items to display
4. Profit!

## Gotchas

Spydus isn't ideal, especially dealing with images, the JS gets a bit hacky to check if valid images
ideally this gets fixed in the future.

## Author

Jeremy Graham
