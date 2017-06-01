<?php

$query = "select count(*) from civil_t where branch_id = 1";

include "database_access.php";

$statement = $connection->prepare($query);
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

if($result) {
    echo "<pre>result count : ".count($result);

    print_r($result);
}

?>