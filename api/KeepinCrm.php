<?php

/**
 * KeepinCRM module for Simpla CMS
 *
 * @copyright   2021 Michael Khiminets
 * @link        https://keepincrm.com/
 * @author      Michael Khiminets
 *
 */

class KeepinCrm extends Simpla {
  private $api_key, $source_id;

  public function __construct() {
    $config = require_once(__DIR__ . '/../config/keepincrm.php');

    $this->api_key   = $config['api_key'];
    $this->source_id = $config['source_id'];
  }

  public function createAgreement($order_id) {
    if (!($order = $this->orders->get_order(intval($order_id)))) {
      return false;
    }

    $i = 0;
    $products_list = array();

    if ($products = $this->orders->get_purchases(array('order_id' => $order->id))) {
      foreach ($products as $item) {
        if ($item->variant_id) {
          $product_title = $item->product_name . ' - '.  $item->variant_name;
        } else {
          $product_title = $item->product_name;
        }

        $products_list[$i] = array (
          'amount'              => $item->amount,
          'product_attributes'  => array (
            'sku'               => $item->sku,
            'title'             => $product_title,
            'price'             => $item->price
          )
        );

        $i++;
      }
    };

    $comment  = $order->comment;

    if (isset($order->delivery_id)) {
      $delivery = $this->delivery->get_delivery(intval($order->delivery_id));
      $comment .= '. Доставка: ' . $delivery->name . ' (' . $order->delivery_price . '). Адреса: ' . $order->address;
    }

    if (isset($order->payment_method_id)) {
      $payment = $this->payment->get_payment_method($order->payment_method_id);
      $comment .= '. Оплата: ' . $payment->name;
    }

    $params = array (
      'title'                 => 'Замовлення #' . $order->id,
      'comment'               => $comment,
      'source_id'             => $this->source_id,
      'client_attributes'     => array (
        'person'              => $order->name,
        'lead'                => true,
        'source_id'           => $this->source_id,
        'email'               => $order->email,
        'phones'              => array (
          0                   => $order->phone
        )
      ),
      'jobs_attributes'       => $products_list
    );

    $this->sendApiRequest("agreements", $params);
  }

  public function createTask($feedback_id) {
    if (!($feedback = $this->feedbacks->get_feedback(intval($feedback_id)))) {
      return false;
    }

    $params = array (
      'title'                 => "Зворотний зв'язок #" . $feedback_id,
      'comment'               => $feedback->message,
      'client_attributes'     => array (
        'person'              => $feedback->name,
        'lead'                => true,
        'source_id'           => $this->source_id,
        'email'               => $feedback->email
      )
    );

    $this->sendApiRequest("tasks", $params);
  }

  private function sendApiRequest($method, $params) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://api.keepincrm.com/v1/' . $method);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', 'X-Auth-Token: ' . $this->api_key . '', 'Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));

    $response = curl_exec($curl);
    $info = curl_getinfo($curl);
    $response = print_r($response, true);
    $params = print_r($params, true);
    $http_code = print_r($info["http_code"], true);
    curl_close($curl);

    $file = 'logs/keepincrm.log';

    $dirname = dirname($file);
    if (!is_dir($file)) {
      mkdir($dirname, 0755, true);
    }

    $find = array("\n", " ");

    $log = date('d.m.Y H:i:s') . ' - ';
    $log .= str_replace($find, '', $http_code).' - ';
    $log .= str_replace($find, '', $response).' - ';
    $log .= str_replace($find, '', $params);

    if (!file_exists($file)) {
      $log_file = fopen($file, "w");
      file_put_contents($file, $log . PHP_EOL, FILE_APPEND);
      fclose($log_file);
    } else {
      file($file, FILE_IGNORE_NEW_LINES);
      file_put_contents($file, $log . PHP_EOL, FILE_APPEND);
    }
  }
}
