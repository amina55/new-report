<?php
session_start();
include "database_access.php";
if (!$connection) {
    $message = "Connection Failed.";
} else {
    if(!empty($_SESSION['step3_query'])) {
        $queryCondition = $_SESSION['step3_query'];
    } else {
        $caseId = $_GET['case_id'];
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
        $queryCondition = $_SESSION['step1']. " and filcase_type = $caseId ";
        $queryCondition .= ($purposeType) ? " and purpose_today = $purposeId " : '';
    }

    $query = "select case_no, cino, fil_no, fil_year  from civil_t where $queryCondition";
    $statement = $connection->prepare($query);
    $statement->execute();
    $caseReports = $statement->fetchAll();
}
include "search.php"; ?>
<br><br><br><br>
<div class="list-shops">
    <div class="visible-block sorted-records-wrapper sorted-records">
        <table class="table data-tables">
            <thead>
            <tr>
                <th>Case No.</th>
                <th>CINO</th>
                <th>Fill Year</th>
                <th>Fill No.</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($caseReports as $caseDetail) { ?>
                <tr>
                    <td><?php echo $caseDetail['case_no'] ?></td>
                    <td><?php echo $caseDetail['cino'] ?></td>
                    <td><?php echo $caseDetail['fil_year'] ?></td>
                    <td><?php echo $caseDetail['fil_no'] ?></td>

                    <td>
                        <a href="view-detail.php?id=<?php echo $caseDetail['cino']; ?>" class="no-text-decoration" title="View Detail of Record">
                            View Detail
                        </a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "footer.php"?>


