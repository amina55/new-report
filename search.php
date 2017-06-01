<?php
include  "master.php";
$currentYear = date('Y');

include "database_access.php";


$criminalCaseIdsStr = !empty($_SESSION['criminal_case_ids']) ? $_SESSION['criminal_case_ids'] : '';
if (empty($criminalCaseIdsStr)) {
    $criminalCaseIdsQuery = "select DISTINCT filcase_type from civil_t where ci_cri = 3 ";
    $criminalCaseIds = $connection->query($criminalCaseIdsQuery);
    foreach ($criminalCaseIds as $criminalCaseId) {
        $criminalCaseIdsStr .= $criminalCaseId['filcase_type'].",";
    }
    $criminalCaseIdsStr = rtrim($criminalCaseIdsStr, ',');
    $_SESSION['criminal_case_ids'] = $criminalCaseIdsStr;
}

$civilCaseIdStr = !empty($_SESSION['civil_case_ids']) ? $_SESSION['civil_case_ids'] : '';
if (empty($civilCaseIdStr)) {
    $civilCaseIdsQuery = "select DISTINCT filcase_type from civil_t where ci_cri = 2 ";
    $civilCaseIds = $connection->query($civilCaseIdsQuery);
    foreach ($civilCaseIds as $civilCaseId) {
        $civilCaseIdStr .= $civilCaseId['filcase_type'].",";
    }
    $civilCaseIdStr = rtrim($civilCaseIdStr, ',');
    $_SESSION['civil_case_ids'] = $civilCaseIdStr;
}

$criminalQuery = "select case_type, type_name from case_type_t where case_type in ($criminalCaseIdsStr)";
$statement = $connection->prepare($criminalQuery);
$statement->execute();
$criminalCases = $statement->fetchAll(PDO::FETCH_ASSOC);

$civilQuery = "select case_type, type_name from case_type_t where case_type in ($civilCaseIdStr)";
$statement = $connection->prepare($civilQuery);
$statement->execute();
$civilCases = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

    <!------------------------------ Page Header -------------------------------->
    <div class="box-header">
        <h3 class="pull-left"> View Report </h3>

    </div>
    <!------------------------------- Page Body --------------------------------->
    <div class="box-body mt15">
        <form action="step1.php" method="post" class="form-horizontal">

            <?php if (!empty($message)) { ?>
                <div class="alert alert-danger">
                    <?php echo $message?>
                </div>
            <?php } ?>

            <div class="form-group">
                <div class="col-sm-12">
                    <label class="col-sm-2 mt10"> Choose Case Year. </label>

                    <div class="col-sm-4">
                        <input placeholder="Start Year" class="form-control" type="number" name="start_year" min="1898" max="<?php echo $currentYear; ?>">
                    </div>

                    <div class="col-sm-4">
                        <input placeholder="End Year" class="form-control" type="number" name="end_year" min="1898" max="<?php echo $currentYear; ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-12">
                    <label class="col-sm-2 mt10"> Choose Case Type </label>

                    <div class="col-sm-4">
                        <select id="case_type_selector" name="case_type_selector" class="form-control">
                            <option value="all">All</option>
                            <option value="civil">Civil</option>
                            <option value="criminal">Criminal</option>
                        </select>
                    </div>

                    <div class="col-sm-4">
                       <select id="civil_case_types" name="civil_case_types" class="form-control">
                           <option value="all">All</option>
                           <?php foreach ($civilCases as $civilCase) {
                               echo "<option value='".$civilCase['case_type']."'>".$civilCase['type_name']."</option>";
                           }?>
                       </select>

                        <select id="criminal_case_types" name="criminal_case_types" class="form-control">
                            <option value="all">All</option>
                            <?php foreach ($criminalCases as $criminalCase) {
                                echo "<option value='".$criminalCase['case_type']."'>".$criminalCase['type_name']."</option>";
                            }?>
                        </select>

                        <select id="all_case_types" name="all_case_types" class="form-control">
                            <option value="all">All</option>
                            <?php foreach ($civilCases as $civilCase) {
                                echo "<option value='".$civilCase['case_type']."'>".$civilCase['type_name']."</option>";
                            }
                            foreach ($criminalCases as $criminalCase) {
                                echo "<option value='".$criminalCase['case_type']."'>".$criminalCase['type_name']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <br><br>


                <div id="detailed_search">

                    <div class="mt20 col-sm-12">
                        <label class="col-sm-2 mt10"> Search By </label>

                        <div class="col-sm-4">
                            <input placeholder="Advocate Name" class="form-control" type="text" name="advocate_name">
                        </div>
                        <div class="col-sm-4">
                            <input placeholder="Judge Name" class="form-control" type="text" name="judge_name">
                        </div>
                    </div>
                    <br>

                    <div class="mt20 col-sm-12">
                        <label class="col-sm-2 mt10"> Specific Case </label>

                        <div class="col-sm-4">
                            <input placeholder="Case No." class="form-control" type="number" name="order_id" min="0">
                        </div>

                        <!--<div class="col-sm-4">
                            <select id="purpose_selector" name="purpose_selector" class="form-control">
                                <option value="all">All</option>
                                <option value="admission">Admission</option>
                                <option value="order">Order</option>
                                <option value="hearing">hearing</option>
                            </select>-->

                        <div class="col-sm-2">
                            <input class="btn btn-green btn-global btn-global-thick" type="submit" value="Search">
                        </div>
                    </div>
                    <br><br>
                </div>
            </div>
        </form>

        <script>
            var caseValue = $('#case_type_selector').val();
            showCases(caseValue);

            $('#case_type_selector').change(function () {
                var value = $(this).val();
                showCases(value);
            });
            function showCases(value) {
                $('#criminal_case_types').hide();
                $('#civil_case_types').hide();
                $('#all_case_types').hide();

                if(value == 'civil') {
                    $('#civil_case_types').show();
                } else if (value == 'criminal') {
                    $('#criminal_case_types').show();
                } else if (value == 'all') {
                    $('#all_case_types').show();
                }
            }
        </script>
        <br><br>
