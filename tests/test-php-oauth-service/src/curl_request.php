<?php

class CurlController
{

  // methods used for running tests
  public static function request()
  {
    $num_args = func_num_args();
    $args = func_get_args();
    $curl_opts = [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => false, // fixed bug 'curl: (60) SSL certificate problem: unable to get local issuer certificate'
      CURLOPT_SSL_VERIFYHOST => false, // 'curl: (51) SSL peer certificate or SSH remote key was not OK'
      CURLOPT_SSL_VERIFYSTATUS => false,
      CURLOPT_VERBOSE => true
    ];

    if ($num_args === 2)
    {
      $curl_opts[CURLOPT_HTTPHEADER] = [
        'Origin: ' . $_SERVER['HTTP_HOST'],
        'Content-Type: application/json',
        'Authorization: Bearer ' . $args[1]
      ];
    }
    else
    {
      return self::error_handler('Invalid request.');
    }

    $url = filter_var($args[0], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);

    return CurlController::execute_request($curl_opts, $url);
  }

  private static function execute_request($curl_opts, $url)
  {
    $ch = curl_init($url);

    if ($ch === false)
    {
      return self::error_handler('Failed to initialize request.');
    }

    curl_setopt_array($ch, $curl_opts);
    $response = curl_exec($ch); // process request and return response

    if ($response === false)
    {
      return self::error_handler('Curl error ' . curl_errno($ch) . ': ' . curl_strerror(curl_errno($ch)));
    }

    curl_close($ch);
    return $response;
  }

  private static function error_handler($value)
  {
    $response = [
      'response_type' => 'error',
      'response_value' => $value
    ];

    return json_encode($response);
  }
}
