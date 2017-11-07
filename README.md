# Recurly PHP library
Library to integrate recurly :- https://recurly.com/

**Note** :- This library is developed in cakephp 2.x but it will work with normal PHP or with some other framework as well with some modifications. 

Important files are listed below with some description.

1. Webhook (*Recurly_PHP_library/Controller/RecurlyWebhooksController.php*)

    This file is a webhook for recurly. Submit this webhook url to your recurly application and you will be notified here if any changes made from the recurly end. Webhook URL example :-  http://YOUR_DOMAIN/recurly_webhooks/mainWebhook

2. Cron (*Recurly_PHP_library/Controller/CronsRecurlyController.php*)
 
    This is cron for recurly that will run at some time interval or when triggered and will help you to check subscriptions, account and some billing details.

3. Recurly php library (*Recurly_PHP_library/Vendor/recurly/*)

    This is PHP library for the recurly. It is available at https://github.com/recurly/recurly-client-php

4. MySQL file for tables (*Recurly_PHP_library/recurly.sql*)

The simple logic is to create one function which will become endpoint of your recurly. (*`mainWebhook` of RecurlyWebhooksController in our case*). Create separate functions for the recurly types and based on the notification type received in the `mainWebhook` we will call appropriate type function and will save data received from the endpoint in respective table.

Then we can use crons to be run on this tables that are being filled with the webhooks (*Or in some case it is better to call recurly api directly*) and we can update subscription plan of our user accordingly in our application.

**Webhook** :- Webhook is your application endpoint where recurly can share data or can notify your application if any changes had been made from the recurly login. Example :- You or Your client upgrade subscription plan of any user then recurly will notify your application that subscription plan has been updated for this user. There are other activities as well like account close, update, subscription renewed, subscription expired etc.

**Basic Understanding** :- Recurly have accounts and subscriptions are part of these accounts. So you need to create recurly account for your application/website users and each account will have subscription that you have defined in term of subscription plan. You will comunicate with recurly on basis of account, UUID etc.

#### Reference URLs

Recurly :- https://recurly.com/
Recurly Documentation :- https://dev.recurly.com/docs/
Recurly Application Login :- https://app.recurly.com/login

