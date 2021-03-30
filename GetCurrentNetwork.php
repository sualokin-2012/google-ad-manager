<?php

require __DIR__ . '/vendor/autoload.php';

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Admanager\AdManagerSession;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\v202008\ApiExceoption;
use Google\AdsApi\AdManager\V202008\ServiceFactory;

// Generate a refreshable OAuth2 credential for authentication.
$oAuth2Credential = (new OAuth2TokenBuilder())
    ->fromFile()
    ->build();

// Construct an API session configured from a properties file and the OAuth2
// credentials above.   
$session = (new AdManagerSessionBuilder())
    ->fromFile()
    ->withOAuth2Credential($oAuth2Credential)
    ->build();

// Get a service.
$serviceFactory = new ServiceFactory();
$networkService = $serviceFactory->createNetworkService($session);

// Make a request
$network = $networkService->getCurrentNetwork();
printf(
    "Network with code %d and display name '%s' was found.\n",
    $network->getNetworkCode(),
    $network->getDisplayName(),
    PHP_EOL
);