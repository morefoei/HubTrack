<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://accounts.zoho.com/oauth/v2/token');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'authorization_code',
    'client_id' => '1000.RGWVVU0PVZBJPU296BNBVZZWVIFA2E',
    'client_secret' => '776b414b7c2cc27d81e50d7a12c966e0a8e5f09ea2',
    'code' => '1000.e574a13a804f9c552eb4c1a059f8a90b.1a1aa969908baa4294106ed98227da99'
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
echo $response;
