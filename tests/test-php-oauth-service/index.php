<?php
require 'src/curl_request.php';
require 'src/response.php';

// handle cross origin resource sharing (CORS)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

$response = new ReponseController();
$origin = $response->get_origin_from_header();

if ($origin !== true) {
  $error = [
    'response_type' => 'error',
    'response_value' => $origin
  ];

  echo json_encode($error);
  exit();
}

$encrypted_authentication_token = $response->get_token_from_header();

if (!is_array($encrypted_authentication_token))
{
  $error = [
    'response_type' => 'error',
    'response_value' => $encrypted_authentication_token
  ];

  echo json_encode($error);
  exit();
}

if (isset($encrypted_authentication_token)) {
  if ($uri[1] === 'validate') {
    // service: request authorization token and authorize user
    // $response = new ReponseController();
    // $origin = $response->get_origin_from_header();
    //
    // if ($origin !== true) {
    //   $error = [
    //     'response_type' => 'error',
    //     'response_value' => $origin
    //   ];
    //
    //   echo json_encode($error);
    //   exit();
    // }
    //
    // $encrypted_authentication_token = $response->get_token_from_header();
    //
    // if (!is_array($encrypted_authentication_token))
    // {
    //   $error = [
    //     'response_type' => 'error',
    //     'response_value' => $encrypted_authentication_token
    //   ];
    //
    //   echo json_encode($error);
    //   exit();
    // }

    $encrypted_authorization_token = CurlController::request('http://ser.local/auth/access_token', $encrypted_authentication_token['response_value']);

    // return error and stop script
    if (substr($encrypted_authorization_token, 0, 1) === '{' || substr($encrypted_authorization_token, 0, 1) === '<') {
      echo $encrypted_authorization_token;
      exit();
    }

    $validate_authorization_token = $response->verify_authorization_token($encrypted_authorization_token);

    // if (!$validate_authorization_token) {
    //   exit("Authorization token couldn't be validated.\n");
    // }

    if (!is_array($validate_authorization_token))
    {
      $error = [
        'response_type' => 'error',
        'response_value' => $validate_authorization_token
      ];

      echo json_encode($error);
      exit();
    }

    echo $encrypted_authorization_token;

  } elseif ($uri[1] === 'service') {
    // client: access service
    // $token = new ReponseController();
    //
    // if (!$token->get_origin_from_header()) {
    //   exit("403: Unauthorized.\n");
    // }
    //
    // $encrypted_authentication_token = $token->get_token_from_header();

    // $response = new ReponseController();
    // $origin = $response->get_origin_from_header();
    //
    // if ($origin !== true) {
    //   $error = [
    //     'response_type' => 'error',
    //     'response_value' => $origin
    //   ];
    //
    //   echo json_encode($error);
    //   exit();
    // }
    //
    // $encrypted_authentication_token = $response->get_token_from_header();
    //
    // if (!is_array($encrypted_authentication_token))
    // {
    //   $error = [
    //     'response_type' => 'error',
    //     'response_value' => $encrypted_authentication_token
    //   ];
    //
    //   echo json_encode($error);
    //   exit();
    // }

    $validate_authentication_token = CurlController::request('http://ser.local/auth/verify_token', $encrypted_authentication_token['response_value']);

    // if (!$validate_authentication_token) {
    //   $response = [
    //     'error' => true,
    //     'error_message' => "Authentication token couldn't be validated.\n"
    //   ];
    //
    //   exit(json_encode($response));
    // }

    // return error and stop script
    if (substr($validate_authentication_token, 0, 1) === '{' || substr($validate_authentication_token, 0, 1) === '<') {
      echo $validate_authentication_token;
      exit();
    }

    // if (!is_array($validate_authentication_token))
    // {
    //   $error = [
    //     'response_type' => 'error',
    //     'response_value' => $validate_authentication_token
    //   ];
    //
    //   echo json_encode($error);
    //   exit();
    // }

    // $response = [
    //   'success_message' => "Welcome back, whatever your name is.\nIf you can see this message, it means that you really have access."
    // ];
    //
    // echo json_encode($response);

    echo "Welcome back, whatever your name is.\nIf you can see this message, it means that you really have access.";
  } else {
    exit("Page not found.\n");
  }
}
