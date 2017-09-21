<?php

use Aws\S3\S3Client;
use Aws\Rekognition\RekognitionClient;
require 'vendor/autoload.php';




$config =require('config.php');


$rek= new Aws\Rekognition\RekognitionClient([
    'version' => 'latest',
    'region' => 'us-west-2',
    'credentials' => [
        'key' => 'AKIAJMT2MAZBR3C4EPMA',
        'secret' => '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'
    ],
    'http'    => [
        'verify' => false
    ]
]);



$s3=new Aws\S3\S3Client([
     'version' => 'latest',
    'region' => 'us-west-2',
    'credentials' => [
        'key' => 'AKIAJMT2MAZBR3C4EPMA',
        'secret' => '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'
    ],
    'http'    => ['decode_content' => false],
    'scheme' => 'http',
]);


