<?php

class JWTModel extends DatabaseModel
{

  protected function store()
  {
    //
  }

  protected function refresh()
  {
    //
  }

  protected function revoke()
  {
    //
  }

  public function get_keys($url)
  {
    // this is just a model
    return json_decode(file_get_contents(json_decode(file_get_contents($url, false, stream_context_create([
      'http'=>[
        'method'=>'GET',
        // 'header'=>''
      ]
    ])), 1)['jwks_uri'], false, stream_context_create([
      'http'=>[
        'method'=>'GET',
        // 'header'=>''
      ]
    ])), 1)['keys'];
  }
}
