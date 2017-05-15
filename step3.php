<?php
session_start();
include "database_access.php";
$selectColumns = '';
if (!$connection) {
    $message = "Connection Failed.";
} else {
    if(!empty($_SESSION['step3_query'])) {
        $queryCondition = $_SESSION['step3_query'];
    } else {
        $caseId = !empty($_GET['case_id']) ? $_GET['case_id'] : 0;
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
        $queryCondition = $_SESSION['step1'];
        ;
        $queryCondition .= ($caseId) ? " and filcase_type = $caseId " : '';
        $queryCondition .= ($purposeType) ? " and purpose_today = $purposeId " : '';
    }
    if(!empty($_SESSION['extra_params']) && $_SESSION['extra_params'] == 'advocate') {
        $selectColumns = ',pet_adv, res_adv';

    }
    $query = "select case_no, cino, fil_no, fil_year $selectColumns from civil_t where $queryCondition";
    $statement = $connection->prepare($query);
    $statement->execute();
    $caseReports = $statement->fetchAll();
}
include "search.php"; ?>
<br><br><br><br>

<button class="btn btn-global btn-global-thin pull-right ml10" onclick="exportPdf()"> Export Pdf</button>
<button class="btn btn-global btn-global-thin pull-right ml10" onclick="exportExcel()"> Export Excel</button>
<!--<button class="btn btn-global btn-global-thin pull-right ml10" onclick="exportPowerPoint()"> Export Power Point</button>
-->
<br><br><br><br>
<div class="list-shops">
    <div class="visible-block sorted-records-wrapper sorted-records">
        <table id="step3_table" class="table data-tables">
            <thead>
            <tr>
                <th>Case No.</th>
                <th>CINO</th>
                <th>Fill Year</th>
                <th>Fill No.</th>
                <?php if($selectColumns) {
                    echo "<th>Petitioner Advocate Name</th>";
                    echo "<th>Respondent Advocate Name</th>";
                }
                ?>
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

                    <?php if($selectColumns) {
                        echo "<td>".$caseDetail['pet_adv']."</td>";
                        echo "<td>".$caseDetail['res_adv']."</td>";
                    } ?>

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

<script>

    function exportPdf() {
        $('#step3_table').tableExport({type:'pdf',escape:'false',pdfFontSize:'14',pdfLeftMargin:10});

    }
    function exportPowerPoint() {
        $('#step3_table').tableExport({type:'powerpoint',escape:'false',pdfFontSize:'14',pdfLeftMargin:10,ignoreColumn:[4]});

    }
    function exportExcel() {
        $('#step3_table').tableExport({type:'excel',escape:'false',pdfFontSize:'14',pdfLeftMargin:10});

    }
</script>

<?php include "footer.php"?>


