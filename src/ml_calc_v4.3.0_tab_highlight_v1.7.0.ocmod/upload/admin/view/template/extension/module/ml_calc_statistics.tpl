<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-statistics" formaction="<?php echo $delete_selected_action; ?>" class="btn btn-warning" onclick="return confirm('<?php echo htmlspecialchars($text_confirm_delete_selected, ENT_QUOTES, 'UTF-8'); ?>');"><i class="fa fa-minus-circle"></i> <?php echo $button_delete_selected; ?></button>
        <a href="<?php echo $export_xls; ?>" data-toggle="tooltip" title="<?php echo $button_export_xls; ?>" class="btn btn-success"><i class="fa fa-download"></i> <?php echo $button_export_xls; ?></a>
        <a href="<?php echo $clear; ?>" data-toggle="tooltip" title="<?php echo $button_clear; ?>" class="btn btn-danger" onclick="return confirm('<?php echo htmlspecialchars($text_confirm_clear, ENT_QUOTES, 'UTF-8'); ?>');"><i class="fa fa-trash-o"></i> <?php echo $button_clear; ?></a>
        <a href="<?php echo $back; ?>" data-toggle="tooltip" title="<?php echo $button_back; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
      </div>
      <h1><?php echo $heading_title; ?> - <?php echo $text_statistics; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-bar-chart"></i> <?php echo $text_statistics; ?> (<?php echo $total; ?>)</h3>
      </div>
      <div class="panel-body">
        <?php if ($statistics) { ?>
        <style>
          .stat-highlighted {
            background-color: #fff3cd !important;
            font-weight: bold;
            color: #856404;
          }
          .stat-group-0 td { background-color: #f8f9fa; }
          .stat-group-1 td { background-color: #eef5ff; }
          .stat-old { background-color: #ffe5e5 !important; color: #8b0000; }
          .stat-new { background-color: #e6ffed !important; color: #0f5132; }
        </style>
        <div class="table-responsive">
          <form action="<?php echo $delete_selected_action; ?>" method="post" id="form-statistics">
          <table class="table table-bordered table-hover">
            <thead>
              <tr>
                <td style="width: 40px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\\'selected\\']').prop('checked', this.checked);" /></td>
                <td class="text-center" style="width: 60px;"><?php echo $column_calc_number; ?></td>
                <td class="text-left"><?php echo $column_product; ?></td>
                <td class="text-left"><?php echo $column_changed_parameter; ?></td>
                <td class="text-right"><?php echo $column_value_old; ?></td>
                <td class="text-right"><?php echo $column_value_new; ?></td>
                <td class="text-right"><?php echo $column_payback_special; ?></td>
                <td class="text-right"><?php echo $column_payback_regular; ?></td>
                <td class="text-right"><?php echo $column_clients_per_day; ?></td>
                <td class="text-right"><?php echo $column_procedure_cost; ?></td>
                <td class="text-right"><?php echo $column_working_days; ?></td>
                <td class="text-right"><?php echo $column_rent; ?></td>
                <td class="text-right"><?php echo $column_utilities; ?></td>
                <td class="text-right"><?php echo $column_master_percent; ?></td>
                <td class="text-left"><?php echo $column_ip; ?></td>
                <td class="text-left"><?php echo $column_date; ?></td>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($statistics as $stat) { ?>
              <tr class="<?php echo isset($stat['group_class']) ? $stat['group_class'] : ''; ?>">
                <td class="text-center"><input type="checkbox" name="selected[]" value="<?php echo (int)$stat['id']; ?>" /></td>
                <td class="text-center"><?php echo isset($stat['calc_number']) ? (int)$stat['calc_number'] : '-'; ?></td>
                <td class="text-left"><?php if (!empty($stat['product_url'])) { ?><a href="<?php echo htmlspecialchars($stat['product_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($stat['product_name'], ENT_QUOTES, 'UTF-8'); ?></a><?php } else { echo htmlspecialchars($stat['product_name'], ENT_QUOTES, 'UTF-8'); } ?></td>
                <td class="text-left"><?php echo trim((string)$stat['changed_parameter_display']) !== '' ? $stat['changed_parameter_display'] : '-'; ?></td>
                <td class="text-right<?php echo ($stat['changed_parameter']) ? ' stat-highlighted stat-old' : ''; ?>"><?php echo trim((string)$stat['value_old']) !== '' ? $stat['value_old'] : '-'; ?></td>
                <td class="text-right<?php echo ($stat['changed_parameter']) ? ' stat-highlighted stat-new' : ''; ?>"><?php echo trim((string)$stat['value_new']) !== '' ? $stat['value_new'] : '-'; ?></td>
                <td class="text-right"><?php echo $stat['payback_special']; ?></td>
                <td class="text-right"><?php echo $stat['payback_regular']; ?></td>
                <td class="text-right<?php echo ($stat['changed_parameter'] === 'clients_per_day') ? ' stat-highlighted' : ''; ?>"><?php echo $stat['clients_per_day']; ?></td>
                <td class="text-right<?php echo ($stat['changed_parameter'] === 'procedure_cost') ? ' stat-highlighted' : ''; ?>"><?php echo $stat['procedure_cost']; ?></td>
                <td class="text-right<?php echo ($stat['changed_parameter'] === 'working_days') ? ' stat-highlighted' : ''; ?>"><?php echo $stat['working_days']; ?></td>
                <td class="text-right<?php echo ($stat['changed_parameter'] === 'rent') ? ' stat-highlighted' : ''; ?>"><?php echo $stat['rent']; ?></td>
                <td class="text-right<?php echo ($stat['changed_parameter'] === 'utilities') ? ' stat-highlighted' : ''; ?>"><?php echo $stat['utilities']; ?></td>
                <td class="text-right<?php echo ($stat['changed_parameter'] === 'master_percent') ? ' stat-highlighted' : ''; ?>"><?php echo $stat['master_percent']; ?></td>
                <td class="text-left"><?php echo htmlspecialchars($stat['ip_address'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="text-left"><?php echo $stat['date_added']; ?></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
          </form>
        </div>
        <div class="row">
          <div class="col-sm-6 text-left"><?php echo $results; ?></div>
          <div class="col-sm-6 text-right"><?php echo $pagination; ?></div>
        </div>
        <?php } else { ?>
        <p><?php echo $text_no_results; ?></p>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>
