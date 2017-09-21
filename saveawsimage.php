<?php
require './app/start.php';
use Aws\S3\Exception\S3Exception;
//$filename =  time() . '.jpg';
//$filepath = 'saved_images/';

$encoded_data = $_POST['mydata'];
	$binary_data = base64_decode( $encoded_data );
	
	$id_img=  $_POST["nombre_img"];
	$path="{$id_img}.jpg";
	$result = file_put_contents( $path, $binary_data );
	if (!$result) die("Could not save image!  Check file permissions.");


$tmp_file_path=$path;
 $final_name=$tmp_file_path;

$gestor=fopen($tmp_file_path, 'rb');
     try {
 
        $s3->putObject([
            'Bucket' => $config['s3']['bucket'],
            'Key' => "uploads/{$final_name}",
            'Body' =>  $gestor,
            'ACL' => 'public-read'
        ]);
 
        // Remove the file
       
 
        // Print the URL to the object.

      $result = $s3->getObject([
    'Bucket' => $config['s3']['bucket'],
    'Key' => "uploads/{$final_name}",

]);

      
       $enlace=$result["@metadata"]["effectiveUri"]."\n";
       echo "<a href='".$enlace."' target='_blank'>".$enlace."</a>";
       echo "<br>";

$face=$rek->detectFaces([
    'Image' => [
        'S3Object' => [ 
            'Bucket' => $config['s3']['bucket'],
            'Name' => "uploads/{$final_name}",
        ],

    ],
]);



$c=$face['FaceDetails'];
$d=json_encode($c);
if(strlen($d)>2){
$a=$face['FaceDetails'][0]['Confidence'];
}else{
  $a=null;
}

$b=json_encode($a);

echo "<br>";

if(!is_null($a)){
echo "Confidencialidad FaceDetection: ";
echo $b." %";
}else{
echo "No se detectó algún rostro";
}


echo "<br>";
$file='FaceDetails.json';
file_put_contents($file, $d);
echo "<br>";
echo "<a href='FaceDetails.json' target='_blank' >Detalles faciales</a>";


$labels = $rek->detectLabels([
    'Image' => [
        'S3Object' => [
            'Bucket' => $config['s3']['bucket'],
            'Name' => "uploads/{$final_name}",
        ],
    ],
    //'MaxLabels' => 123,
    //'MinConfidence' => 70,
]);



$l=json_encode($labels['Labels']);
echo "<br>";
$file='Labels.json';
file_put_contents($file, $l);
echo "<br>";
echo "<a href='Labels.json' target='_blank' >Etiquetas</a>";
echo "<br>";



fclose($gestor);
unlink($tmp_file_path);



    } catch (S3Exception $e) {
    //die("There was an error uploading that file.");
    echo $e->getMessage() . "\n";
    }
 
?>
