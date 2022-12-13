# Senior PHP Developer Assignment

## Initial setup dev environment
0. Clone this repo
> git clone git@github.com:dhavalbhavsar/mmd.git

1. Install composer dependencies:
> composer install

2. Edit env file
> copy .env.example to .env and update DATABASE_URL and ETH_HOST_URL

3. Run migration
> bin/console doctrine:migrations:migrate

## Below are the API with sample request

1) List all Items<br />
  Method - GET <br />
  URI - http://base-uri/items<br />
  
2) Create Item (Seller)<br />
  Method - POST<br />
  URI - http://base-uri/item/create<br />
  Request - <br />
  {
     "name": "Test Item",
     "sku": "SKUL-001",
     "description": "Test",
     "short_description": "Test desc",
     "price": 10,
     "mediaFile": "https://cdn.pixabay.com/photo/2015/03/10/17/23/youtube-667451_1280.png"
 }
 
3) Setup Auction Item (Seller)<br />
  Method - POST <br />
  URI - http://base-uri/item/{id}/auction<br />
  Request - <br />
  {
     "auction_price": 2,
     "auction_start_date": "2022-12-12 00:00:00",
     "auction_end_date": "2022-12-31 23:59:59"
  }

4) Create purchase (Buyer)<br />
  Method - POST<br />
  URI - http://base-uri/buy/item/{id}<br />
  Request - <br />
  {
     "from": "0x2e94757df1267f244f4b9ef049416c6794a60552",
     "to": "0x22c5071a37432ac845a57c4a69339d161b6baa22"
  }
  
