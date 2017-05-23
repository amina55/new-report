<?php
session_start();
include "database_access.php";
$removeColumn = 4;
$selectColumns = $tableHeading = '';
$advocateName = (!empty($_SESSION['advocate_name'])) ? $_SESSION['advocate_name'] : '';
$judgeName = (!empty($_SESSION['judge_name'])) ? $_SESSION['judge_name'] : '';

if (!$connection) {
    $message = "Connection Failed.";
} else {
    if(!empty($_SESSION['step3_query'])) {
        $queryCondition = $_SESSION['step3_query'];
    } else {
        $caseId = !empty($_GET['case_id']) ? $_GET['case_id'] : 0;
        $caseType = !empty($_GET['case_type']) ? $_GET['case_type'] : '';

        $purposeType = !empty($_GET['purpose']) ? $_GET['purpose'] : '';
        $tableHeading .= ($caseType) ? " $caseType," : '';
        $tableHeading .= ($purposeType) ? " $purposeType," : '';
        $tableHeading = ($tableHeading) ? rtrim($tableHeading, ',') : '';
        $tableHeading = $_SESSION['table_name']. $tableHeading.' Report';

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
        $queryCondition = $_SESSION['step2_query'];
        $queryCondition .= ($caseId) ? " and filcase_type = $caseId " : '';
        $queryCondition .= ($purposeType) ? " and purpose_today = $purposeId " : '';
    }
    if($advocateName) {
        $selectColumns .= ',pet_adv, res_adv';
        $removeColumn += 2;
    }
    if($judgeName) {
        $selectColumns .= ',judge_code';
        $removeColumn += 1;

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
                <?php if($advocateName) {
                    echo "<th>Petitioner Advocate Name</th>";
                    echo "<th>Respondent Advocate Name</th>";
                } if ($judgeName) {
                    echo "<th>Judge Code</th>";
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

                    <?php if($advocateName) {
                        echo "<td>".$caseDetail['pet_adv']."</td>";
                        echo "<td>".$caseDetail['res_adv']."</td>";
                    } if ($judgeName) {
                        echo "<td>".$caseDetail['judge_code']."</td>";
                    }?>
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

<script src="js/jspdf.debug.js"></script>
<script src="js/jspdf.plugin.autotable.js"></script>
<script src="js/faker.min.js"></script>
<script>

    function exportExcel1() {
        $('#step3_table').tableExport({type:'csv',escape:'false',ignoreColumn:[<?php echo $removeColumn; ?>],title:'<?php echo $tableHeading?>'});

    }
    function exportExcel() {
        $('#step3_table').tableExport({type:'excel',escape:'false',ignoreColumn:[<?php echo $removeColumn; ?>],title:'<?php echo $tableHeading?>'});
    }

    function exportPdf() {
        var doc = new jsPDF('l');
        var title = '<?php echo $tableHeading; ?>';
        doc.text(title, 14, 16);
        var elem = document.getElementById("step3_table");
        var res = doc.autoTableHtmlToJson(elem);
        res.columns.splice(-1,1);
        doc.autoTable(res.columns, res.data, {
            startY: 20,
            margin: {horizontal: 7},
            bodyStyles: {valign: 'top'},
            styles: {overflow: 'linebreak', columnWidth: 'wrap'},
            columnStyles: {text: {columnWidth: 'auto'}}
        });

        doc.output('dataurlnewwindow');
    }

</script>

<?php include "footer.php"?>


