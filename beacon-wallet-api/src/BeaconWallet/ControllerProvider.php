<?php

namespace BeaconWallet;

class ControllerProvider implements \Silex\ControllerProviderInterface
{
    public function connect(\Silex\Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'controller.home:indexAction')->bind('home');
        $controllers->get('/accounts/{card}', 'controller.accounts:getAccountAction')->bind('account');

        return $controllers;
    }
}
