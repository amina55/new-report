<?php
session_start()       ;
$message = $query = $purpose = '';
$_SESSION['step3_query'] = '';
$_SESSION['step2_query'] = '';
$_SESSION['extra_params'] = '';
$tableHeading = 'Full Data';
include "database_access.php";
$step = 'step1';
if (!$connection) {
    $message = "Connection Failed.";
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $startYear = trim($_POST['start_year']);
        $endYear = trim($_POST['end_year']);
        $selector = trim($_POST['case_type_selector']);
        $caseType = trim($_POST[$selector.'_case_types']);
        $orderId = trim($_POST['order_id']);
        $advocateName = strtoupper(trim($_POST['advocate_name']));
        $judgeName = strtoupper(trim($_POST['judge_name']));
        $tableHeading = $purpose = '';

        $whereQuery = $yearQuery = $caseQuery = "";
        if($startYear || $endYear) {
            if($startYear && $endYear) {
                if ($startYear > $endYear) {
                    $message = "Start Year should be less than End Year.";
                } else {
                    $str = '';
                    for ($i = $startYear; $i <= $endYear; $i++) {
                        $str .= " '$i',";
                    }
                    $str = rtrim($str, ',');
                    $yearQuery = " fil_year in ($str) ";
                    $tableHeading = 'Year ('.$startYear.'-'.$endYear.') ,';
                }
            } else {
                $year = ($startYear) ? $startYear : $endYear;
                $yearQuery = "fil_year = '$year'";
                $tableHeading = 'Year ('.$year.'),';
            }
            if($yearQuery) {
                $whereQuery .= $yearQuery." and";
            }
        }

        if($selector != 'all') {
            $caseQuery = "";
            if($selector == 'criminal') {
                $caseQuery = " branch_id = 2 ";
            } else {
                $caseQuery = " branch_id = 1 ";
            }
            $step = 'step2';
            $whereQuery .= $caseQuery." and";
            $tableHeading .= "Case ($selector),";
        }

        if($caseType != 'all') {
            $step = 'step2';
            $whereQuery .= " filcase_type = $caseType and";
        }

        if($advocateName) {
            $step = 'step2';
            $_SESSION['extra_params'] = 'advocate';
            $whereQuery .= " ( res_adv like '%$advocateName%' or pet_adv like '%$advocateName%' ) and";
            $tableHeading .= " Advocate ($advocateName),";
        }
        if($judgeName) {
            $step = 'step2';
            $judgeQuery = '';
            $_SESSION['extra_params'] = 'judge';
            $judgeCodeQuery = "select judge_code from judge_name_t where judge_name like '%$judgeName%'";
            $judgeCodes = $connection->query($judgeCodeQuery);
            foreach ($judgeCodes as $judgeCodeObject) {
                $judgeCode = $judgeCodeObject['judge_code'];
                $judgeQuery .= ($judgeCode == 1 ) ? " judge_code = '$judgeCode' OR" : " judge_code = '$judgeCode' OR" ;
            }
            $judgeQuery = ($judgeQuery) ? rtrim($judgeQuery, 'OR') : 'judge_code = ""';
            $whereQuery .= "( $judgeQuery ) and";
            $tableHeading .= " Judge ($judgeName),";
        }
        if($orderId) {
            $step = 'step3';
            $whereQuery .= " fil_no = $orderId and";
            $tableHeading .= " Case No. ($orderId),";
        }
        $whereQuery = rtrim($whereQuery, 'and');
        $query = ($whereQuery) ? $whereQuery : 'true';

    } else {
        $query = "true";
    }
    $_SESSION['step1'] = $query;
    $_SESSION['table_name'] = rtrim($tableHeading, ',');
    $tableHeading = $tableHeading.' Report';

    if ($step == 'step2') {
        $_SESSION['step2_query'] = $query;
        header('Location: step2.php'); exit();
    }
    if ($query) {
        $query = "select count(cino) as total_count from civil_t where " . $query;
        $statement = $connection->prepare($query);
        $statement->execute();
        $reports = $statement->fetch();

        if($reports['total_count'] > 0 ) {
            $criminalCaseIdsStr = !empty($_SESSION['criminal_case_ids']) ? $_SESSION['criminal_case_ids'] : '';
            if (empty($criminalCaseIdsStr)) {
                $criminalCaseIdsQuery = "select DISTINCT filcase_type from civil_t where branch_id = 2";
                $criminalCaseIds = $connection->query($criminalCaseIdsQuery);
                foreach ($criminalCaseIds as $criminalCaseId) {
                    $criminalCaseIdsStr .= $criminalCaseId['filcase_type'].",";
                }
                $criminalCaseIdsStr = rtrim($criminalCaseIdsStr, ',');
                $_SESSION['criminal_case_ids'] = $criminalCaseIdsStr;
            }

            $civilCaseIdStr = !empty($_SESSION['civil_case_ids']) ? $_SESSION['civil_case_ids'] : '';
            if (empty($civilCaseIdStr)) {
                $civilCaseIdsQuery = "select DISTINCT filcase_type from civil_t where branch_id = 1";
                $civilCaseIds = $connection->query($civilCaseIdsQuery);
                foreach ($civilCaseIds as $civilCaseId) {
                    $civilCaseIdStr .= $civilCaseId['filcase_type'].",";
                }
                $civilCaseIdStr = rtrim($civilCaseIdStr, ',');
                $_SESSION['civil_case_ids'] = $civilCaseIdStr;
            }

            switch ($purpose) {
                case 'all' :
                    $countQuery = ', sum(case when purpose_today = 2 then 1 else 0 end) admission, sum(case when purpose_today = 4 then 1 else 0 end) orders, sum(case when purpose_today = 8 then 1 else 0 end) hearing';
                    break;
                case 'admission' :
                    $countQuery = ', sum(case when purpose_today = 2 then 1 else 0 end) admission ';
                    break;
                case 'order' :
                    $countQuery = ', sum(case when purpose_today = 4 then 1 else 0 end) orders ';
                    break;
                case 'hearing' :
                    $countQuery = ', sum(case when purpose_today = 8 then 1 else 0 end) hearing ';
                    break;
                default :
                    $countQuery = ', sum(case when purpose_today = 2 then 1 else 0 end) admission, sum(case when purpose_today = 4 then 1 else 0 end) orders, sum(case when purpose_today = 8 then 1 else 0 end) hearing';
            }

            $query = $_SESSION['step1'] . " and filcase_type in ($criminalCaseIdsStr) ";
            $query = "select count(cino) as count $countQuery from civil_t where " . $query;
            $statement = $connection->prepare($query);
            $statement->execute();
            $criminalReport = $statement->fetch();

            $query = $_SESSION['step1'] . " and filcase_type in ($civilCaseIdStr) ";
            $query = "select count(cino) as count $countQuery from civil_t where " . $query;
            $statement = $connection->prepare($query);
            $statement->execute();
            $civilReport = $statement->fetch();

            $query = "select count(cino) as count $countQuery from civil_t where " . $_SESSION['step1'];
            $statement = $connection->prepare($query);
            $statement->execute();
            $reports = $statement->fetch();

        }
    }
}

include  "search.php"; ?>

    <?php if (!empty($reports)) { ?>

    <br><br>

    <button class="btn btn-global btn-global-thin pull-right ml10" onclick="exportPdf()"> Export Pdf</button>
    <button class="btn btn-global btn-global-thin pull-right ml10" onclick="exportExcel()"> Export Excel</button>
    <!--<button class="btn btn-global btn-global-thin pull-right ml10" onclick="exportPowerPoint()"> Export Power Point</button>-->

    <br><br><br><br>

    <table id="step1_table" class="table">
        <thead>
        <tr>
            <th>Type</th>

            <?php
            switch ($purpose) {
                case 'admission' :
                    echo "<th>Admission</th>"; break;
                case 'hearing' :
                    echo "<th>Hearing</th>"; break;
                case 'order' :
                    echo "<th>Order</th>"; break;
                default :
                    echo "<th>Admission</th><th>Hearing</th><th>Order</th><th>Others</th>";
            }
            ?>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($criminalReport)) { ?>

        <tr>
            <td>Criminal</td>
            <td><?php echo ($criminalReport['admission'] > 0) ? "<a href='step2.php?type=criminal&purpose=admission'>".$criminalReport['admission']."</a>" : $criminalReport['admission']?></td>
            <td><?php echo ($criminalReport['hearing'] > 0) ? "<a href='step2.php?type=criminal&purpose=hearing'>".$criminalReport['hearing']."</a>" : $criminalReport['hearing']?></td>
            <td><?php echo ($criminalReport['orders'] > 0) ? "<a href='step2.php?type=criminal&purpose=orders'>".$criminalReport['orders']."</a>" : $criminalReport['orders']?></td>
            <td><?php echo $criminalReport['count'] - ($criminalReport['admission'] + $criminalReport['hearing'] + $criminalReport['orders']) ?></td>
            <td><?php echo ($criminalReport['count'] > 0) ? "<a href='step2.php?type=criminal'>".$criminalReport['count']."</a>" : $criminalReport['count']?></td>
        </tr>

        <?php }
        if (!empty($civilReport)) { ?>

        <tr>
            <td>Civil</td>
            <td><?php echo ($civilReport['admission'] > 0) ? "<a href='step2.php?type=civil&purpose=admission'>".$civilReport['admission']."</a>" : $civilReport['admission']?></td>
            <td><?php echo ($civilReport['hearing'] > 0) ? "<a href='step2.php?type=civil&purpose=hearing'>".$civilReport['hearing']."</a>" : $civilReport['hearing']?></td>
            <td><?php echo ($civilReport['orders'] > 0) ? "<a href='step2.php?type=civil&purpose=orders'>".$civilReport['orders']."</a>" : $civilReport['orders']?></td>
            <td><?php echo $civilReport['count'] - ($civilReport['admission'] + $civilReport['hearing'] + $civilReport['orders']) ?></td>
            <td><?php echo ($civilReport['count'] > 0) ? "<a href='step2.php?type=civil'>".$civilReport['count']."</a>" : $civilReport['count']?></td>
        </tr>

        <?php }
        if (!empty($reports)) { ?>

        <tr>
            <td>Total</td>
            <td><?php echo  $reports['admission']?></td>
            <td><?php echo  $reports['hearing']?></td>
            <td><?php echo  $reports['orders']?></td>
            <td><?php echo $reports['count'] - ($reports['admission'] + $reports['hearing'] + $reports['orders']) ?></td>
            <td><?php echo  $reports['count']?></td>
        </tr>
        <?php } ?>
        </tbody>
    </table>

    <br><br>

    <div class="col-sm-12">
        <div class="col-sm-6">
            <h3>Criminal Graph</h3>
            <div id="criminalDiv" style="width: 480px; height: 380px;"></div>
        </div>
        <div class="col-sm-6">
            <h3>Civil Graph</h3>
            <div id="civilDiv" style="width: 480px; height: 380px;"></div>
        </div>
    </div>

    <script src="js/jspdf.debug.js"></script>
    <script src="js/jspdf.plugin.autotable.js"></script>
    <script src="js/faker.min.js"></script>
    <script>
        var data = [{
            values: [ <?php echo $criminalReport['admission']?>, <?php echo $criminalReport['orders']?>, <?php echo $criminalReport['hearing']?>, <?php echo $criminalReport['count'] - ($criminalReport['admission']+$criminalReport['orders']+ $criminalReport['hearing']) ?>],
            labels: ['Admission', 'Orders', 'Hearing', 'Others'],
            type: 'pie'
        }];
        var data2 = [{
            values: [ <?php echo $civilReport['admission']?>, <?php echo $civilReport['orders']?>, <?php echo $civilReport['hearing']?>, <?php echo $civilReport['count'] - ($civilReport['admission']+$civilReport['orders']+ $civilReport['hearing']) ?>],
            labels: ['Admission', 'Orders', 'Hearing', 'Others'],
            type: 'pie'
        }];
        var layout = {
            height: 380,
            width: 480
        };
        Plotly.newPlot('criminalDiv', data, layout);
        Plotly.newPlot('civilDiv', data2, layout);

        function exportPdf() {
            var doc = new jsPDF();
            var title = '<?php echo $tableHeading?>'+' Report';
            console.log(title);
            doc.text(title, 14, 16);
            var elem = document.getElementById("step1_table");
            var res = doc.autoTableHtmlToJson(elem);
            doc.autoTable(res.columns, res.data, {startY: 20});
            doc.output('dataurlnewwindow');
        }
        
        function exportPdf1() {
            $('#step1_table').tableExport({type:'pdf',escape:'false',pdfFontSize:'14',pdfLeftMargin:10});

        }
        function exportExcel() {
            $('#step1_table').tableExport({type:'excel',escape:'false',title:'<?php echo $tableHeading;?>'+' Report'});

        }
    </script>
    <br><br>
<?php }


if(!empty($orderDetail)) {

}

include "footer.php"; ?>