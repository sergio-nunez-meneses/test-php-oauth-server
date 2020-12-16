<?php

class CurlController
{

  // methods used for running tests
  public static function request()
  {
    $num_args = func_num_args();
    $args = func_get_args();

    if ($num_args === 3)
    {
      // build header and body
      // $token = base64_encode($args[1] . ':' . $args[2]);
      $payload = http_build_query([
        'grant_type' => 'client_credentials',
        'scope' => '' // optional ?
      ]);

      $curl_opts = [
        CURLOPT_HTTPHEADER => [
          'Origin: ' . $_SERVER['HTTP_HOST'],
          'Content-Type: application/x-www-form-urlencoded',
          // "Authorization: Basic $token",
          'Authorization: Basic ' . $args[1],
        ],
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false, // fixed bug 'Curl failed with error #60'
        CURLOPT_VERBOSE => TRUE
      ];
    }
    elseif ($num_args === 2)
    {
      $curl_opts = [
        CURLOPT_HTTPHEADER => [
          'Origin: ' . $_SERVER['HTTP_HOST'],
          'Content-Type: application/json',
          'Authorization: Bearer ' . $args[1]
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_VERBOSE => TRUE
      ];
    }
    else
    {
      throw new \Exception('Invalid request.');
    }

    $url = filter_var($args[0], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);

    return CurlController::execute_request($curl_opts, $url);
  }

  private static function execute_request($curl_opts, $url)
  {
    try
    {
      $ch = curl_init($url);

      if ($ch === false)
      {
        throw new \Exception('Failed to initialize request.');
      }

      curl_setopt_array($ch, $curl_opts);
      $response = curl_exec($ch); // process request and return response

      if ($response === false)
      {
        echo curl_error($ch) . "\n" . curl_strerror(curl_errno($ch));
      }

      curl_close($ch);
      return $response;
    }
    catch (\Exception $e)
    {
      trigger_error(
        sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()),
      E_USER_ERROR);
    }
  }
}
