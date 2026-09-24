<h1>PHP MySQL Test</h1>
<p>Some lorem ipsum here.</p>
<?php



mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli('127.0.0.1:3308', 'fingerrocket', 'L@sirena23', 'studiobl_fingerrocket');
$mysqli->set_charset('utf8mb4');
printf("Success... %s\n", $mysqli->host_info);
       

        $sql = "SELECT * FROM fortress";
        $result = $mysqli->query($sql);
        while($row = $result->fetch_array(MYSQLI_ASSOC)){
            print "Flak: " . $row['flak'];
            
        }
?>