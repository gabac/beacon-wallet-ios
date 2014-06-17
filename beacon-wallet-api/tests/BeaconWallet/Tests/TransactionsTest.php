<?php

namespace BeaconWallet\Tests;

class TransactionsTest extends ApiTest
{
    public function testTransaction()
    {
        $encrypted = file_get_contents(__DIR__ . '/fixtures/createTransaction.txt');

        $client = $this->createClient();
        $crawler = $client->request('POST', '/transactions', array('cart' => $encrypted));

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/fixtures/createTransaction.json', $client->getResponse()->getContent());

        // pay transaction
        $encrypted = file_get_contents(__DIR__ . '/fixtures/payTransaction.txt');

        $crawler = $client->request('POST', '/transactions/1/payment', array('payment' => $encrypted));

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/fixtures/payTransaction.json', $client->getResponse()->getContent());
    }
}
