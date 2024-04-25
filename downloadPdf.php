<?php
// require('PDFGenerator/rotation.php');
// class PDF extends PDF_Rotate
// {
// }
// $pdf = new PDF();
// $pdf->Output("/var/www/trinityapplab.in/html/AzurePower/api/files/Apr-2024-23/08042024_Readiness.pdf");
// exit();

$file = "https://www.trinityapplab.in/AzurePower/api/files/Apr-2024-23/08042024_Readiness.pdf";

// // We will be outputting a PDF 
// header('Content-Type: application/pdf'); 
  
// // It will be called downloaded.pdf 
// header('Content-Disposition: attachment; filename="gfgpdf.pdf"'); 
  
// $imagpdf = file_put_contents($image, file_get_contents($file));  
  
// echo $imagepdf;

header("Content-Type: application/octet-stream"); 
  
// $file = $_GET["file"]  . ".pdf"; 
  
header("Content-Disposition: attachment; filename=" . urlencode($file));    
header("Content-Type: application/download"); 
header("Content-Description: File Transfer");             
header("Content-Length: " . filesize($file)); 
  
flush(); // This doesn't really matter. 
  
$fp = fopen($file, "r"); 
while (!feof($fp)) { 
    echo fread($fp, 65536); 
    flush(); // This is essential for large downloads 
}  
  
fclose($fp);   
?>