<?php
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://apis.google.com");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header('Access-Control-Allow-Methods: GET, POST');
header('Allow: GET, POST');

  if(isset($_POST['examplePost'])){
    $result = array();
    echo json_encode($result);
  }
    
  if(isset($_GET['exampleGet'])){
    echo json_encode (array());
  }