<?php

use Aws\S3\S3Client;
use Aws\Rekognition\RekognitionClient;
require 'vendor/autoload.php';




$config =require('config.php');


$rek= new Aws\Rekognition\RekognitionClient([
    'version' => 'latest',
    'region' => 'us-west-2',
    'credentials' => [
        'key' => '',
        'secret' => ''
    ],
    'http'    => [
        'verify' => false
    ]
]);



$s3=new Aws\S3\S3Client([
     'version' => 'latest',
    'region' => 'us-west-2',
    'credentials' => [
        'key' => '',
        'secret' => ''
    ],
    'http'    => ['decode_content' => false],
    'scheme' => 'http',
]);


