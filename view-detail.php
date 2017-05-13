<?php
include "database_access.php";
try {
    if (!$connection) {
        $message = "Connection Failed.";
    } else {
        $id = $_GET['id'];
        $query = "select * from civil_t where cino = '$id'";
        $statement = $connection->prepare($query);
        $statement->execute();
        $record = $statement->fetch();
        $caseTypeQuery = "select type_name, case_type from case_type_t where case_type = ".$record['filcase_type'];
        $statement = $connection->prepare($caseTypeQuery);
        $statement->execute();
        $caseType = $statement->fetch();
    }
    include "master.php";
} catch (Exception $ex) {
    $message = "Error : ".$ex->getMessage();
}

?>

    <!------------------------------ Page Header -------------------------------->
    <div class="box-header">
        <h3 class="pull-left"> Case Detail</h3>
        </a>
    </div>
    <!------------------------------- Page Body --------------------------------->
    <div class="box-body">
        <div class="mt15">

            <?php if (!empty($message)) { ?>
                <div class="alert alert-danger">
                    <?php echo $message?>
                </div>
            <?php } ?>

            <?php  if(!empty($record)) { ?>

                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#home">Basic Info</a></li>
                    <li><a data-toggle="tab" href="#pet_info">Petitioner Info</a></li>
                    <li><a data-toggle="tab" href="#res_info">Respondent Info</a></li>
                </ul>

                <div class="tab-content">
                    <div id="home" class="tab-pane fade in active">
                        <br><br>
                        <div class="form-group col-sm-12">
                            <label class="col-sm-2 col-xs-12 control-label text-right">
                                CINO
                            </label>
                            <label class="col-sm-8 col-xs-12 text-left bold">
                                <?php echo $record['cino'] ?>
                            </label>
                        </div>

                        <div class="form-group col-sm-12">
                            <label class="col-sm-2 col-xs-12 control-label text-right">
                                Case No.
                            </label>
                            <label class="col-sm-8 col-xs-12 text-left bold">
                                <?php echo $record['fil_no'] ?>
                            </label>
                        </div>

                        <div class="form-group col-sm-12">
                            <label class="col-sm-2 col-xs-12 control-label text-right">
                                Filling No.
                            </label>
                            <label class="col-sm-8 col-xs-12 text-left bold">
                                <?php echo $record['filing_no'] ?>
                            </label>
                        </div>

                        <div class="form-group col-sm-12">
                            <label class="col-sm-2 col-xs-12 control-label text-right">
                                Filling Year
                            </label>
                            <label class="col-sm-8 col-xs-12 text-left bold">
                                <?php echo $record['fil_year'] ?>
                            </label>
                        </div>

                        <div class="form-group col-sm-12">
                            <label class="col-sm-2 col-xs-12 control-label text-right">
                                Filling Date
                            </label>
                            <label class="col-sm-8 col-xs-12 text-left bold">
                                <?php echo $record['date_of_filing'] ?>
                            </label>
                        </div>
                        <div class="form-group col-sm-12">
                            <label class="col-sm-2 col-xs-12 control-label text-right">
                                Case Type
                            </label>
                            <label class="col-sm-8 col-xs-12 text-left bold">
                                <?php echo $caseType['type_name'] ?>
                            </label>
                        </div>
                        <div class="form-group col-sm-12">
                            <label class="col-sm-2 col-xs-12 control-label text-right">
                                Purpose Type
                            </label>
                            <label class="col-sm-8 col-xs-12 text-left bold">
                                <?php switch ($record['purpose_today']) {
                                    case 2 :
                                        echo "Admission"; break;
                                    case 4 :
                                        echo "Order"; break;
                                    case 8 :
                                        echo "Hearing"; break;
                                    default :
                                        echo "Other";
                                } ?>
                            </label>
                        </div>
                    </div>
                    <div id="pet_info" class="tab-pane fade">
                        <br><br>
                        <div class="form-group col-sm-12">
                            <label class="col-sm-2 col-xs-12 control-label text-right">
                                Petitioner name
                            </label>
                            <label class="col-sm-8 col-xs-12 text-left bold">
                                <?php echo $record['pet_name'] ?>
                            </label>
                        </div>

                        <div class="form-group col-sm-12">
                            <label class="col-sm-2 col-xs-12 control-label text-right">
                                Petitioner Advocate
                            </label>
                            <label class="col-sm-8 col-xs-12 text-left bold">
                                <?php echo  $record['pet_adv']?>
                            </label>
                        </div>
                    </div>
                    <div id="res_info" class="tab-pane fade">
                        <br><br>
                        <div class="form-group col-sm-12">
                            <label class="col-sm-2 col-xs-12 control-label text-right">
                                Respondent name
                            </label>
                            <label class="col-sm-8 col-xs-12 text-left bold">
                                <?php echo $record['res_name'] ?>
                            </label>
                        </div>

                        <div class="form-group col-sm-12">
                            <label class="col-sm-2 col-xs-12 control-label text-right">
                                Respondent Advocate
                            </label>
                            <label class="col-sm-8 col-xs-12 text-left bold">
                                <?php echo $record['res_adv'] ?>
                            </label>
                        </div>
                    </div>
                </div>
            <?php }?>
        </div>
    </div>
<?php include "footer.php" ?>