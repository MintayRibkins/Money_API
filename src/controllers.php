<?php

use Symfony\Component\HttpFoundation\Request;

//Request::setTrustedProxies(array('127.0.0.1'));
ini_set('display_errors', 1);

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig', array('result' => null, 'pagination' => null));
})
    ->bind('homepage');

$app->post('/form', function (Request $request) use ($app) {

    //
    $summ = $request->request->get('money'); //get number of sum from form
    $app['db']->insert('transaction', array('money_sum' => $summ));//insert into `transaction` table
//    dump($app['db']->lastInsertId());
//    dump($summ);
    $last_id = $app['db']->lastInsertId(); //get last inserted id from transaction
    $currency = array(
        [
            'type' => 'Note',
            'amount' => 50
        ],
        [
            'type' => 'Note',
            'amount' => 20
        ],
        [
            'type' => 'Note',
            'amount' => 10
        ],
        [
            'type' => 'Note',
            'amount' => 5
        ],
        [
            'type' => 'Coin',
            'amount' => 2
        ],
        [
            'type' => 'Coin',
            'amount' => 1
        ],
        [
            'type' => 'Coin',
            'amount' => 0.50
        ],
        [
            'type' => 'Coin',
            'amount' => 0.20
        ],
        [
            'type' => 'Coin',
            'amount' => 0.10
        ],
        [
            'type' => 'Coin',
            'amount' => 0.5
        ],
        [
            'type' => 'Coin',
            'amount' => 0.2
        ],
        [
            'type' => 'Coin',
            'amount' => 0.1
        ],
    ); // array with currency values and types

    $result = array(); // create result variable

    if ($summ <= 1000 and round($summ) > 0) {
        foreach ($currency as $value) {
            $i = 1;
            while (($summ - $value['amount']) >= 0) {

                $summ = $summ - $value['amount'];
                $result[$value['type']][(string)$value['amount']] = $i;
                $i++;
            }
        }
        foreach ($result as $type => $items) {
            foreach ($items as $key => $item) {
                $app['db']
                    ->insert('combinations', array(
                        'transaction_id' => $last_id,
                        'quantity' => $item,
                        'amount' => $key,
                        'type' => $type
                    ));
                echo " quantity " . $item . " amount " . $key . " type " . $type . "<br>";
            }
        }
//        $json_result = json_encode($app['db']->fetchAll('SELECT * FROM `combinations` WHERE `transaction_id` = :id ', array('id' => $last_id)));
        $json_result = $app['db']->fetchAll('SELECT * FROM `transaction` JOIN `combinations` WHERE `transaction`.id = `combinations`.transaction_id');
        $pagination = $app['knp_paginator']->paginate($json_result);

        return $app['twig']->render('index.html.twig', array('pagination' => json_encode($pagination->getItems())));//return result to frontend

    } // Logic for counting system

    else {
        return $app['twig']->render('index.html.twig', array('pagination' => json_encode(array('error_message' => 'Out of range'))));
    }
})->bind('form');

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/' . $code . '.html.twig',
        'errors/' . substr($code, 0, 2) . 'x.html.twig',
        'errors/' . substr($code, 0, 1) . 'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
