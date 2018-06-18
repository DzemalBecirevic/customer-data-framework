<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace CustomerManagementFrameworkBundle\Service\Auth\Repositories;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use CustomerManagementFrameworkBundle\Service\Auth\Entities\ClientEntity;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier, $grantType = null, $clientSecret = null, $mustValidateSecret = true)
    {
        $clients = \Pimcore::getContainer()->getParameter("pimcore_customer_management_framework.oauth_server");

        $clients = array_map(function($client){
            $transformedClient = [
                'client_id' =>       $client['client_id'],
                'secret'          => password_hash($client['secret'], PASSWORD_BCRYPT),
                'name'            => $client['name'],
                'redirect_uri'    => $client['redirect_uri'],
                'is_confidential' => $client['is_confidential']
            ];
            return $transformedClient;
        },$clients);

        $currentClient = array_filter($clients, function($client) use ($clientIdentifier) {
            return $client['client_id'] == $clientIdentifier;
        });

        // Check if client is registered
        if(!count($currentClient))return;

        $currentClient = $currentClient[0];

        if (
            $mustValidateSecret === true
            && $currentClient['is_confidential'] === true
            && password_verify($clientSecret, $currentClient['secret']) === false
        ) {
            return;
        }

        $client = new ClientEntity();
        $client->setIdentifier($clientIdentifier);
        $client->setName($currentClient['name']);
        $client->setRedirectUri($currentClient['redirect_uri']);

        return $client;
    }
}