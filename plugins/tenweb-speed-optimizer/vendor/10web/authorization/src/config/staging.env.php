<?php
if (!defined('TENWEB_SITE_URL')) {
    define('TENWEB_SITE_URL', "https://dev.10web.io");
}
if (!defined('TENWEB_DASHBOARD')) {
    define('TENWEB_DASHBOARD', "https://devmy.10web.io");
}
if (!defined('TENWEB_API_URL')) {
    define('TENWEB_API_URL', 'https://devmanager.10web.io/api');
}
if (!defined('TENWEB_S3_BUCKET')) {
    define('TENWEB_S3_BUCKET', '10web-products-staging');
}
if (!defined('TENWEB_MANAGER_ID')) {
    define('TENWEB_MANAGER_ID', 51);
}
if (!defined('TENWEB_DEACTIVATION_REASONS_URL')) {
    define('TENWEB_DEACTIVATION_REASONS_URL', 'https://devcore.10web.io/api/deactivation_reasons');
}
if (!defined('TENWEB_SO_CRITICAL_URL')) {
    define("TENWEB_SO_CRITICAL_URL",'https://devperformance.10web.io');
}
if (!defined('TENWEBIO_API_URL')) {
    define('TENWEBIO_API_URL', "https://devoptimizer.10web.io");
}

if(!defined('TENWEB_SO_FREE_SUBSCRIPTION_ID')) {
    define("TENWEB_SO_FREE_SUBSCRIPTION_ID",323);
}

if(!defined('TENWEB_SO_AUTOCONNECT')) {
    define("TENWEB_SO_AUTOCONNECT",false);
}

if(!defined('TENWEB_SO_FREE_SUBSCRIPTION_IDS')) {
    define("TENWEB_SO_FREE_SUBSCRIPTION_IDS",[
        323, //booster free subscription id
        // no ai assistant pro booster free subscription ids
    ]);
}

if(!defined('TWBB_S3_BUCKET')) {
    define('TWBB_S3_BUCKET', '10webtemplates-testing');
}

if(!defined('TENWEB_SIGNUP_MAGIC_LINK_URL')) {
    define('TENWEB_SIGNUP_MAGIC_LINK_URL', 'https://devcore.10web.io/api/checkout/signup-via-magic-link');
}

if(!defined('TENWEB_AI_ASSISTANT')) {
    define('TENWEB_AI_ASSISTANT', 'https://devai-assistant.10web.io/');
}

if(!defined('TENWEB_AI_ASSISTANT_SUBSCRIPTION_ID')) {
    define('TENWEB_AI_ASSISTANT_SUBSCRIPTION_ID', 333);
}

if(!defined('TWM_ENABLE_PAYMENT')) {
    define('TWM_ENABLE_PAYMENT', true);
}
if(!defined('TENWEB_WOOCOM_API_URL')) {
    define('TENWEB_WOOCOM_API_URL', 'https://devwoocom.10web.io/');
}

global $tenweb_services;

$tenweb_services = array(
  'devoptimizer.10web.io',
  'devsecurity.10web.io',
  'devseo.10web.io',
  'devbackup.10web.io',
  'devmanager.10web.io',
  'devcore.10web.io',
  'devlxd.10web.io'
);

global $tenweb_regions;

$tenweb_regions = array(
    "asia-east1" => "Changhua County, Taiwan",
    "asia-northeast1" => "Tokyo, Japan",
    "asia-south1" => "Mumbai, India",
    "asia-southeast1" => "Jurong West, Singapore",
    "australia-southeast1" => "Sydney, Australia",
    "europe-north1" => "Hamina, Finland",
    "europe-west1" => "St. Ghislain, Belgium",
    "europe-west2" => "London, England, UK",
    "europe-west3" => "Frankfurt, Germany",
    "europe-west4" => "Eemshaven, Netherlands",
    "northamerica-northeast1" => "Montréal, Québec, Canada",
    "southamerica-east1" => "São Paulo, Brazil",
    "us-central1" => "Council Bluffs, Iowa, USA",
    "us-east1" => "Moncks Corner, South Carolina, USA",
    "us-east4" => "Ashburn, Northern Virginia, USA",
    "us-west1" => "The Dalles, Oregon, USA",
    "us-west2" => "Los Angeles, California, USA"
);

global $tenweb_firebaseConfig;

$tenweb_firebaseConfig = [
    'apiKey' => "AIzaSyBP3zPfSTMZxUqJsfmHMd6EJV2aITZ-3FY",
    'authDomain'=> "copilot-dev-64f1d.firebaseapp.com",
    'projectId'=> "copilot-dev-64f1d",
    'storageBucket'=> "copilot-dev-64f1d.appspot.com",
    'messagingSenderId'=> "101114320744",
    'appId'=> "1:101114320744:web:3a98c2010c93b6e7a0a8dd",
    'databaseURL' => "https://copilot-dev-64f1d.firebaseio.com",
];

