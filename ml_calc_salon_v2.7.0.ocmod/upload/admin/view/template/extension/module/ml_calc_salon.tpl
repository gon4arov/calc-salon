<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-ml-calc-salon" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?> <small>v<?php echo $module_version; ?></small></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-ml-calc-salon" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="module_ml_calc_salon_status" id="input-status" class="form-control">
                <?php if ($module_ml_calc_salon_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <hr>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-clients-per-day"><?php echo $entry_default_clients_per_day; ?></label>
            <div class="col-sm-10">
              <input type="text" name="module_ml_calc_salon_default_clients_per_day" value="<?php echo $module_ml_calc_salon_default_clients_per_day; ?>" placeholder="<?php echo $entry_default_clients_per_day; ?>" id="input-clients-per-day" class="form-control" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-procedure-cost"><?php echo $entry_default_procedure_cost; ?></label>
            <div class="col-sm-10">
              <input type="text" name="module_ml_calc_salon_default_procedure_cost" value="<?php echo $module_ml_calc_salon_default_procedure_cost; ?>" placeholder="<?php echo $entry_default_procedure_cost; ?>" id="input-procedure-cost" class="form-control" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-working-days"><?php echo $entry_default_working_days; ?></label>
            <div class="col-sm-10">
              <input type="text" name="module_ml_calc_salon_default_working_days" value="<?php echo $module_ml_calc_salon_default_working_days; ?>" placeholder="<?php echo $entry_default_working_days; ?>" id="input-working-days" class="form-control" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-rent"><?php echo $entry_default_rent; ?></label>
            <div class="col-sm-10">
              <input type="text" name="module_ml_calc_salon_default_rent" value="<?php echo $module_ml_calc_salon_default_rent; ?>" placeholder="<?php echo $entry_default_rent; ?>" id="input-rent" class="form-control" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-utilities"><?php echo $entry_default_utilities; ?></label>
            <div class="col-sm-10">
              <input type="text" name="module_ml_calc_salon_default_utilities" value="<?php echo $module_ml_calc_salon_default_utilities; ?>" placeholder="<?php echo $entry_default_utilities; ?>" id="input-utilities" class="form-control" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-master-percent"><?php echo $entry_default_master_percent; ?></label>
            <div class="col-sm-10">
              <input type="text" name="module_ml_calc_salon_default_master_percent" value="<?php echo $module_ml_calc_salon_default_master_percent; ?>" placeholder="<?php echo $entry_default_master_percent; ?>" id="input-master-percent" class="form-control" />
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>
