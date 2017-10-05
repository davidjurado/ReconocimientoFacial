<?php

use Aws\S3\S3Client;
use Aws\Rekognition\RekognitionClient;
require 'vendor/autoload.php';




$config =require('config.php');


$rek= new Aws\Rekognition\RekognitionClient([
    'version' => $config["s3"]["version"],
    'region' => $config["s3"]["region"],
    'credentials' => [
        'key' => $config["s3"]["key"],
        'secret' => $config["s3"]["secret"]
    ],
    'http'    => [
        'verify' => false
    ]
]);



$s3=new Aws\S3\S3Client([
     'version' => $config["s3"]["version"],
    'region' => $config["s3"]["region"],
    'credentials' => [
        'key' => $config["s3"]["key"],
        'secret' => $config["s3"]["secret"]
    ],
    'http'    => ['decode_content' => false],
    'scheme' => 'http',
]);


