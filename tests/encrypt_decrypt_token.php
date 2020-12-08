<?php

// data to be ecrypted and decrypted
$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI1ZmJkODkzMWE3MTE5MC41ODUwMTAxMSIsImlkX3VzZXIiOiIxIiwiaXNzIjoiaHR0cDpcL1wvc2VyLmxvY2FsIiwic3ViIjoiaHR0cDpcL1wvZXhhbXBsZS5sb2NhbFwvYWxsb3dlZCIsImlhdCI6MTYwNjI1Njk0NSwiZXhwIjoxNjA2MjY0MTQ1LCJ0b2tlbl90eXBlIjoiQmVhcmVyIiwic2NvcGUiOiJzb21lX3Njb3BlIn0.p_Ql0AoISox4TjOA82Zy_pwki9NahqgKTBoLUzWn-MWH2F1H5EO7tdzLRfCDYla5j6mEO4lmLgzR2xBS_erUm7AACdL6hgie22rnnFTYczSy8Z0WIoWithYEVfW0s-5iJTgSFzIAkvhZKxAoPM5v6bxxcyOz9sRnE3vNAzDfrWwQ3_x83fsqJC-uidTEDdk4gL0WkXE-pmbCFNBdBokKcjwdNLa92AhLNS7_NCRSIMIzUKPgFxnyyA3vnmefCStKBkhtOgSFlKK3I5sg4An_2Y93UkafrEU41Y36gDpOKRPL2dwo_WFcyZm0AyY7ixv7qTuu76gySUUI2R6GXf1V5A';

// create openssl resource and generate private and public keys
$res = openssl_pkey_new([
  'private_key_bits' => 8192,
  'private_key_type' => OPENSSL_KEYTYPE_RSA
]);
openssl_pkey_export($res, $private_key);
$public_key = openssl_pkey_get_details($res);

// encrypt and decrypt
if (!openssl_private_encrypt($token, $encrypted, $private_key, OPENSSL_PKCS1_PADDING)) {
  throw new \Exception(openssl_error_string());
}

echo "\n\nEncrypted:\n";
echo base64_encode($encrypted);

if (!openssl_public_decrypt($encrypted, $decrypted, $public_key['key'], OPENSSL_PKCS1_PADDING)) {
  throw new \Exception(openssl_error_string());
}

echo "\n\nDecrypted:\n";
echo $decrypted;
