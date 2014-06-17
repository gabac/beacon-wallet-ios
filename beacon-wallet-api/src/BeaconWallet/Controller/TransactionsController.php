<?php

namespace BeaconWallet\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class TransactionsController
{
    protected $transactions;

    protected $accounts;

    protected $crypt;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGenerator
     */
    protected $url;

    public function __construct($transactions, $accounts, $crypt, $url)
    {
        $this->transactions = $transactions;
        $this->accounts = $accounts;
        $this->crypt = $crypt;
        $this->url = $url;
    }

    public function createTransaction(Request $request)
    {
        // encrypt cart
        $encrypted = $request->get('cart');

        $decrypted = $this->crypt->decrypt($encrypted);

        $data = json_decode($decrypted);

        $card = isset($data->card) ? $data->card : null;
        $branch = isset($data->branch) ? $data->branch : null;
        $products = isset($data->products) && is_array($data->products) ? $data->products : array();

        // create transaction
        $transactionId = $this->transactions->createTransaction($card, $branch, $products);

        // prepare transaction response
        $json = $this->getTransactionJson($transactionId);

        // sign response
        $signature = $this->crypt->sign($json);

        return new JsonResponse(array(
            'transaction' => $json,
            'signature' => $signature,
        ));
    }

    protected function getTransactionJson($transactionId)
    {
        $transaction = $this->transactions->getTransaction($transactionId);

        $data = array(
            'id' => (int) $transaction['id'],
            'status' => $transaction['status'],
            'card' => $transaction['card'],
            'branch' => (int) $transaction['branch'],
            'amount' => 0.0,
            'products' => array(),
        );

        foreach ($transaction['products'] as $product) {

            $data['products'][] = array(
                'id' => (int) $product['product'],
                'quantity' => (int) $product['quantity'],
                'amount' => (float) $product['amount'],
            );

            $data['amount'] += (float) $product['amount'];
        }

        return json_encode($data);
    }

    public function payTransaction(Request $request)
    {
        // encrypt payment
        $encrypted = $request->get('payment');

        $decrypted = $this->crypt->decrypt($encrypted);

        $data = json_decode($decrypted);

        $id = isset($data->id) ? $data->id : null;
        $card = isset($data->card) ? $data->card : null;
        $pin = isset($data->pin) ? $data->pin : null;

        if (!$this->accounts->verifyPin($card, $pin)) {
            throw new AccessDeniedHttpException('Invalid card and pin');
        }

        $this->transactions->payTransaction($id, $card);

        // prepare transaction response
        $json = $this->getTransactionJson($id);

        // sign response
        $signature = $this->crypt->sign($json);
        
        return new JsonResponse(array(
            'transaction' => $json,
            'signature' => $signature,
        ));
    }

    public function getTransactions(Request $request)
    {
        $transaction = array(
            'id' => 42,
        );
        $json = json_encode($transaction);

        $encrypted = 'Wezl9GKjvdoGrbVjV20iMApNoQpckL7DDkB1eQOkhrTIjCAV7cMvtWMa+VWUKgiVPr0tsl+dOvg413NEIWFuwuaMjLQJ8u+ZuBKVGRvic/MV0bLDefNAk03wbFEtHtRdqxLeVV0igbELc7rzkXPF6E/QYKZf/AGz+Nh5gkyVznNBaZ+0MwEifABwQRauSNmJb6ZG3Ze8jidgjSyyn/Jbzxllr58rhkbKnutuDnDpCGytPz87ZtoaEiyPBzOAb8OlxYKh5zNhI8jIc7V2lGk1/4ZnsTId15YdCfbu6eLvEK6fOXzPTLgzOPXm4dRu952vgbAfvVj5XKUC23nHcxojhg==';

        // prepare and return response
        $data = array(
            'transaction' => $json,
            'signature' => $this->crypt->sign($json),
            'decrypted' => $this->crypt->decrypt($encrypted),
        );

        return new JsonResponse(array(
            'transactions' => $data
        ));
    }
}
