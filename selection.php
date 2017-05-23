<?php
session_start();
include "search.php";
$whereQuery = $_SESSION['step1'];
$judgeName = !empty($_SESSION['judge_name']) ? $_SESSION['judge_name'] : '';
$advocateName = !empty($_SESSION['advocate_name']) ? $_SESSION['advocate_name'] : '';
$resAdvocateRecords = $petAdvocateRecords = $judgeRecords = null;
$message = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selectedResAdv = !empty($_POST['res_advocate_name']) ? $_POST['res_advocate_name'] : [];
    $selectedPetAdv = !empty($_POST['pet_advocate_name']) ? $_POST['pet_advocate_name'] : [];
    $selectedJudgeCodes = !empty($_POST['judge_codes']) ? $_POST['judge_codes'] : [];

    if($advocateName && empty($selectedPetAdv) && empty($selectedResAdv)) {
        $message = "Choose atleast 1 advocate for searching";
    }
    if($judgeName && empty($selectedJudgeCodes)) {
        $message = "Choose atleast 1 judge for searching";
    }

    if(!$message) {
        $_SESSION['step2_query'] = $_SESSION['step1'];
        if($advocateName) {
            $advocateQuery = (!empty($selectedPetAdv)) ? " pet_adv in ('". implode("','", $selectedPetAdv). "') OR" : '';
            $advocateQuery .= (!empty($selectedResAdv)) ? " res_adv in ('". implode("','", $selectedResAdv). "') OR" : '';
            $advocateQuery = " ( ". rtrim($advocateQuery, 'OR'). " )";
            $_SESSION['step2_query'] .= " AND $advocateQuery ";
        }

        if($judgeName) {
            $judgeQuery = "";
            foreach ($selectedJudgeCodes as $judgeCode) {
                $judgeQuery .= " judge_code = '$judgeCode' OR judge_code like '$judgeCode,%' OR judge_code like '%,$judgeCode' OR judge_code like '%,$judgeCode,%' OR";
            }
            $judgeQuery = rtrim($judgeQuery, 'OR');
            $_SESSION['step2_query'] .= " AND ( $judgeQuery )";

        }
        echo '<script>window.location = "step2.php";</script>'; exit();
       // header('Location: step2.php'); exit();
    }
}
if($advocateName) {

    $query = "select DISTINCT res_adv from civil_t where $whereQuery and res_adv LIKE '%$advocateName%'";
    $statement = $connection->prepare($query);
    $statement->execute();
    $resAdvocateRecords = $statement->fetchAll();
    echo $query;


    $query = "select DISTINCT pet_adv from civil_t where $whereQuery and pet_adv LIKE '%$advocateName%'";
    $statement = $connection->prepare($query);
    $statement->execute();
    $petAdvocateRecords = $statement->fetchAll();
}
if($judgeName) {
    $query = "select judge_code, judge_name, count(*) from judge_name_t where judge_name like '%$judgeName%' group by judge_name, judge_code";
    $statement = $connection->prepare($query);
    $statement->execute();
    $judgeRecords = $statement->fetchAll();
}
if(empty($resAdvocateRecords) && empty($petAdvocateRecords) && empty($judgeRecords)) { ?>
    <div class="col-sm-12">
        <div class='alert alert-danger'>There is no result</div>
    </div>
<?php } else {
    if($message) { ?>
        <div class="col-sm-12">
            <div class='alert alert-danger'><?php echo $message; ?></div>
        </div>
    <?php } ?>

    <form class="form-horizontal" method="post" action="<?php htmlentities($_SERVER['PHP_SELF']); ?>">
        <div class="col-sm-12">
            <?php if(!empty($petAdvocateRecords)) { ?>
            <div class='col-sm-6'>
                <h3> Petitioner Advocates </h3><br><br>

                <?php foreach ($petAdvocateRecords as $petAdvocateRecord) { ?>
                    <input type="checkbox" name="pet_advocate_name[]" value="<?php echo $petAdvocateRecord['pet_adv']; ?>"> <label><?php echo $petAdvocateRecord['pet_adv']; ?></label><br>
                <?php } ?>
            </div>
            <?php }
            if(!empty($resAdvocateRecords)) { ?>
            <div class='col-sm-6'>
                <h3> Respondent Advocates </h3><br><br>

                <?php foreach ($resAdvocateRecords as $resAdvocateRecord) { ?>
                    <input type="checkbox" name="res_advocate_name[]" value="<?php echo $resAdvocateRecord['res_adv']; ?>"> <label><?php echo $resAdvocateRecord['res_adv']; ?></label><br>
                <?php } ?>
            </div>
            <?php } ?>
        </div>

        <?php if(!empty($judgeRecords)) { ?>
            <div class="col-sm-12">
                <div class="col-sm-6">
                    <br><br>
                    <h3> Judge Name</h3><br><br>

                    <?php foreach ($judgeRecords as $judgeRecord) { ?>
                        <input type="checkbox" name="judge_codes[]" value="<?php echo $judgeRecord['judge_code']; ?>"> <label><?php echo $judgeRecord['judge_name']; ?></label><br>
                    <?php } ?>
                    <br><br>
                </div>
            </div>
            <br><br><br>
        <?php } ?>
        <div class="col-lg-offset-2 col-sm-10">
            <br><br><br>
            <input class="btn btn-global btn-global-thin" type="submit" value="Next">
        </div>

    </form>

<?php } ?>

<?php include "footer.php"; ?>