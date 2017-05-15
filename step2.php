<?php
session_start();
$purposeId = 0;
$purposeType = '';

include "database_access.php";
if (!$connection) {
    $message = "Connection Failed.";
} else {

    $purposeType = !empty($_GET['purpose']) ? $_GET['purpose'] : '';
    switch ($purposeType) {
        case 'admission' :
            $purposeId = 2;
            break;
        case 'orders' :
            $purposeId = 4;
            break;
        case 'hearing' :
            $purposeId = 8;
            break;
        default :
            $purposeId = 0;
    }
    if(!empty($_SESSION['step2_query'])) {
        $queryCondition = $_SESSION['step2_query'];
    } else {
        $queryModify = (!empty($_GET['type']) && $_GET['type'] == 'civil') ? '1' : '2';
        $queryCondition = $_SESSION['step1']. " and branch_id = $queryModify";
        $queryCondition .= ($purposeId) ? ' and purpose_today = '.$purposeId : '';
    }

    $query = "select civil_t.filcase_type, case_type_t.type_name, count(civil_t.cino) as count";
    $query .= (!$purposeId) ? ", sum(case when purpose_today = 2 then 1 else 0 end) admission, sum(case when purpose_today = 4 then 1 else 0 end) orders, sum(case when purpose_today = 8 then 1 else 0 end) hearing ": '';
    $query .= " from civil_t INNER JOIN case_type_t ON civil_t.filcase_type = case_type_t.case_type  and " . $queryCondition . " group by civil_t.filcase_type, case_type_t.type_name";
    $statement = $connection->prepare($query);
    $statement->execute();
    $caseReports = $statement->fetchAll();
}

include "search.php";

if (!empty($caseReports)) {
    $graphValues = [];
    $graphLabels = [];
    $totalCount = $admTotal = $orderTotal = $hearingTotal = $othersTotal = 0;
    ?>

    <table class="table">
        <thead>
        <tr>
            <th>Case Type</th>

            <?php if(!$purposeId) { ?>
                <th>Admission</th>
                <th>Hearing</th>
                <th>Order</th>
                <th>Others</th>
            <?php } ?>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>

        <?php foreach ($caseReports as $caseReport) {
            $caseId = $caseReport['filcase_type'];
            $caseCount = $caseReport['count'];
            $graphValues[] = $caseCount;
            $graphLabels[] = $caseReport['type_name'];
            $totalCount += $caseCount;
            ?>

        <tr>
            <td><?php echo $caseReport['type_name']; ?></td>
            <?php if(!$purposeId) {
                $admTotal += $caseReport['admission'];
                $orderTotal += $caseReport['orders'];
                $hearingTotal += $caseReport['hearing'];
                $others = $caseCount - ($caseReport['orders'] + $caseReport['admission'] + $caseReport['hearing']);
                $othersTotal += $others;
                ?>
                <td><?php echo ($caseReport['admission']  > 0) ? "<a href='step3.php?case_id=".$caseId. "&purpose=admission'>" . $caseReport['admission']  . "</a>" : $caseReport['admission']  ?></td>
                <td><?php echo ($caseReport['hearing']  > 0) ? "<a href='step3.php?case_id=".$caseId. "&purpose=hearing'>" . $caseReport['hearing']  . "</a>" : $caseReport['hearing']  ?></td>
                <td><?php echo ($caseReport['orders']  > 0) ? "<a href='step3.php?case_id=".$caseId. "&purpose=orders'>" . $caseReport['orders']  . "</a>" : $caseReport['orders']  ?></td>
                <td><?php echo $others ?></td>

            <?php } ?>
            <td><?php echo ($caseCount > 0) ? "<a href='step3.php?case_id=".$caseId."&purpose=$purposeType'>" . $caseCount . "</a>" : $caseCount ?></td>
        </tr>
        <?php } ?>

        <tr>
            <td>Total</td>
            <?php if(!$purposeId) { ?>
                <td><?php echo ($admTotal  > 0) ? "<a href='step3.php?purpose=admission'>" . $admTotal  . "</a>" : $admTotal  ?></td>
                <td><?php echo ($hearingTotal  > 0) ? "<a href='step3.php?purpose=hearing'>" . $hearingTotal  . "</a>" : $hearingTotal  ?></td>
                <td><?php echo ($orderTotal  > 0) ? "<a href='step3.php?purpose=orders'>" . $orderTotal  . "</a>" : $orderTotal  ?></td>
                <td><?php echo $othersTotal ?></td>

            <?php } ?>
            <td><?php echo ($totalCount  > 0) ? "<a href='step3.php'>" . $totalCount  . "</a>" : $totalCount  ?></td>

        </tr>
        </tbody>
    </table>
    <br><br>

    <div class="col-sm-12">

            <h3>Case Type Graph</h3>
            <div id="caseTypeGraph"></div>
    </div>

    <script>
        var data = [{
            y: <?php echo json_encode($graphValues);?>,
            x: <?php echo json_encode($graphLabels);?>,
            type: 'bar'
        }];

        console.log(data);
        /*var layout = {
            height: 600,
            width: 1200
        };*/
        Plotly.newPlot('caseTypeGraph', data);
    </script>

<?php } else {
    echo "<div> <h3>There is no record available</h3></div>";
}
include "footer.php"?>
