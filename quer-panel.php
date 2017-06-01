<?php

$query = "select ci_cri, count(*) from civil_t group by ci_cri";

include "database_access.php";

$statement = $connection->prepare($query);
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

if($result) {
    echo "<pre>result count : ".count($result);

    print_r($result);
}

?>