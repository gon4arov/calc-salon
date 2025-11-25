<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-ml-calc" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $statistics; ?>" data-toggle="tooltip" title="<?php echo $text_statistics; ?>" class="btn btn-info"><i class="fa fa-bar-chart"></i> <?php echo $text_statistics; ?></a>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
      </div>
      <h1><?php echo $heading_title; ?> <small style="color: #999;">v<?php echo $module_version; ?></small></h1>
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
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-ml-calc" class="form-horizontal">

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="module_ml_calc_status" id="input-status" class="form-control">
                <?php if ($module_ml_calc_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-show-regular-payback"><?php echo $entry_show_regular_payback; ?></label>
            <div class="col-sm-10">
              <select name="module_ml_calc_show_regular_payback" id="input-show-regular-payback" class="form-control">
                <?php if ($module_ml_calc_show_regular_payback) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
              <p class="help-block"><?php echo $help_show_regular_payback; ?></p>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-show-calc-button"><?php echo $entry_show_calc_button; ?></label>
            <div class="col-sm-10">
              <select name="module_ml_calc_show_calc_button" id="input-show-calc-button" class="form-control">
                <?php if ($module_ml_calc_show_calc_button) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
              <p class="help-block"><?php echo $help_show_calc_button; ?></p>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-tab-highlight"><?php echo $entry_tab_highlight; ?></label>
            <div class="col-sm-10">
              <select name="module_ml_calc_tab_highlight" id="input-tab-highlight" class="form-control">
                <option value="none" <?php echo (!isset($module_ml_calc_tab_highlight) || $module_ml_calc_tab_highlight == 'none') ? 'selected="selected"' : ''; ?>><?php echo $text_tab_highlight_none; ?></option>
                <option value="red" <?php echo (isset($module_ml_calc_tab_highlight) && $module_ml_calc_tab_highlight == 'red') ? 'selected="selected"' : ''; ?>><?php echo $text_tab_highlight_red; ?></option>
                <option value="yellow" <?php echo (isset($module_ml_calc_tab_highlight) && $module_ml_calc_tab_highlight == 'yellow') ? 'selected="selected"' : ''; ?>><?php echo $text_tab_highlight_yellow; ?></option>
                <option value="new_badge" <?php echo (isset($module_ml_calc_tab_highlight) && $module_ml_calc_tab_highlight == 'new_badge') ? 'selected="selected"' : ''; ?>><?php echo $text_tab_highlight_new_badge; ?></option>
              </select>
              <p class="help-block"><?php echo $help_tab_highlight; ?></p>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_customer_groups; ?></label>
            <div class="col-sm-10">
              <input type="hidden" name="module_ml_calc_customer_groups[]" value="0">
              <?php if ($customer_groups) { ?>
              <?php foreach ($customer_groups as $customer_group) { ?>
              <div class="checkbox">
                <label>
                  <input type="checkbox"
                         name="module_ml_calc_customer_groups[]"
                         value="<?php echo $customer_group['customer_group_id']; ?>"
                         <?php echo in_array($customer_group['customer_group_id'], $module_ml_calc_customer_groups) ? 'checked="checked"' : ''; ?>>
                  <?php echo $customer_group['name']; ?>
                </label>
              </div>
              <?php } ?>
              <?php } else { ?>
              <p class="form-control-static"><?php echo $text_none; ?></p>
              <?php } ?>
              <p class="help-block"><?php echo $help_customer_groups; ?></p>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-clients"><?php echo $entry_default_clients_per_day; ?></label>
            <div class="col-sm-10">
              <input type="number" name="module_ml_calc_default_clients_per_day" value="<?php echo $module_ml_calc_default_clients_per_day; ?>" placeholder="<?php echo $entry_default_clients_per_day; ?>" id="input-clients" class="form-control" min="1" max="100" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-cost"><?php echo $entry_default_procedure_cost; ?></label>
            <div class="col-sm-10">
              <input type="number" name="module_ml_calc_default_procedure_cost" value="<?php echo $module_ml_calc_default_procedure_cost; ?>" placeholder="<?php echo $entry_default_procedure_cost; ?>" id="input-cost" class="form-control" min="100" step="100" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-days"><?php echo $entry_default_working_days; ?></label>
            <div class="col-sm-10">
              <input type="number" name="module_ml_calc_default_working_days" value="<?php echo $module_ml_calc_default_working_days; ?>" placeholder="<?php echo $entry_default_working_days; ?>" id="input-days" class="form-control" min="1" max="31" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-rent"><?php echo $entry_default_rent; ?></label>
            <div class="col-sm-10">
              <input type="number" name="module_ml_calc_default_rent" value="<?php echo $module_ml_calc_default_rent; ?>" placeholder="<?php echo $entry_default_rent; ?>" id="input-rent" class="form-control" min="0" step="1000" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-utilities"><?php echo $entry_default_utilities; ?></label>
            <div class="col-sm-10">
              <input type="number" name="module_ml_calc_default_utilities" value="<?php echo $module_ml_calc_default_utilities; ?>" placeholder="<?php echo $entry_default_utilities; ?>" id="input-utilities" class="form-control" min="0" step="500" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-percent"><?php echo $entry_default_master_percent; ?></label>
            <div class="col-sm-10">
              <input type="number" name="module_ml_calc_default_master_percent" value="<?php echo $module_ml_calc_default_master_percent; ?>" placeholder="<?php echo $entry_default_master_percent; ?>" id="input-percent" class="form-control" min="0" max="100" />
            </div>
          </div>

          <hr style="margin: 30px 0;">
          <h3 style="margin-bottom: 20px;"><i class="fa fa-paint-brush"></i> <?php echo $text_style_settings; ?></h3>

          <!-- Налаштування кольорів -->
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-primary-color"><?php echo $entry_primary_color; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="text" name="module_ml_calc_primary_color" value="<?php echo $module_ml_calc_primary_color; ?>" placeholder="#007bff" id="input-primary-color" class="form-control" />
                <span class="input-group-addon">
                  <input type="color" value="<?php echo $module_ml_calc_primary_color; ?>" style="width: 50px; border: none; cursor: pointer;" onchange="document.getElementById('input-primary-color').value = this.value">
                </span>
              </div>
              <small class="help-block"><?php echo $help_primary_color; ?></small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-button-color"><?php echo $entry_button_color; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="text" name="module_ml_calc_button_color" value="<?php echo $module_ml_calc_button_color; ?>" placeholder="#28a745" id="input-button-color" class="form-control" />
                <span class="input-group-addon">
                  <input type="color" value="<?php echo $module_ml_calc_button_color; ?>" style="width: 50px; border: none; cursor: pointer;" onchange="document.getElementById('input-button-color').value = this.value">
                </span>
              </div>
              <small class="help-block"><?php echo $help_button_color; ?></small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-text-color"><?php echo $entry_text_color; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="text" name="module_ml_calc_text_color" value="<?php echo $module_ml_calc_text_color; ?>" placeholder="#333333" id="input-text-color" class="form-control" />
                <span class="input-group-addon">
                  <input type="color" value="<?php echo $module_ml_calc_text_color; ?>" style="width: 50px; border: none; cursor: pointer;" onchange="document.getElementById('input-text-color').value = this.value">
                </span>
              </div>
              <small class="help-block"><?php echo $help_text_color; ?></small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-background-color"><?php echo $entry_background_color; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="text" name="module_ml_calc_background_color" value="<?php echo $module_ml_calc_background_color; ?>" placeholder="#f8f9fa" id="input-background-color" class="form-control" />
                <span class="input-group-addon">
                  <input type="color" value="<?php echo $module_ml_calc_background_color; ?>" style="width: 50px; border: none; cursor: pointer;" onchange="document.getElementById('input-background-color').value = this.value">
                </span>
              </div>
              <small class="help-block"><?php echo $help_background_color; ?></small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-result-border-color"><?php echo $entry_result_border_color; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="text" name="module_ml_calc_result_border_color" value="<?php echo $module_ml_calc_result_border_color; ?>" placeholder="#28a745" id="input-result-border-color" class="form-control" />
                <span class="input-group-addon">
                  <input type="color" value="<?php echo $module_ml_calc_result_border_color; ?>" style="width: 50px; border: none; cursor: pointer;" onchange="document.getElementById('input-result-border-color').value = this.value">
                </span>
              </div>
              <small class="help-block"><?php echo $help_result_border_color; ?></small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-income-color"><?php echo $entry_income_color; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="text" name="module_ml_calc_income_color" value="<?php echo $module_ml_calc_income_color; ?>" placeholder="#28a745" id="input-income-color" class="form-control" />
                <span class="input-group-addon">
                  <input type="color" value="<?php echo $module_ml_calc_income_color; ?>" style="width: 50px; border: none; cursor: pointer;" onchange="document.getElementById('input-income-color').value = this.value">
                </span>
              </div>
              <small class="help-block"><?php echo $help_income_color; ?></small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-expense-color"><?php echo $entry_expense_color; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="text" name="module_ml_calc_expense_color" value="<?php echo $module_ml_calc_expense_color; ?>" placeholder="#dc3545" id="input-expense-color" class="form-control" />
                <span class="input-group-addon">
                  <input type="color" value="<?php echo $module_ml_calc_expense_color; ?>" style="width: 50px; border: none; cursor: pointer;" onchange="document.getElementById('input-expense-color').value = this.value">
                </span>
              </div>
              <small class="help-block"><?php echo $help_expense_color; ?></small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-calc-button-bg-color"><?php echo $entry_calc_button_bg_color; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="text" name="module_ml_calc_button_bg_color" value="<?php echo $module_ml_calc_button_bg_color; ?>" placeholder="#4169E1" id="input-calc-button-bg-color" class="form-control" />
                <span class="input-group-addon">
                  <input type="color" value="<?php echo $module_ml_calc_button_bg_color; ?>" style="width: 50px; border: none; cursor: pointer;" onchange="document.getElementById('input-calc-button-bg-color').value = this.value">
                </span>
              </div>
              <small class="help-block"><?php echo $help_calc_button_bg_color; ?></small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-calc-button-text-color"><?php echo $entry_calc_button_text_color; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="text" name="module_ml_calc_button_text_color" value="<?php echo $module_ml_calc_button_text_color; ?>" placeholder="#ffffff" id="input-calc-button-text-color" class="form-control" />
                <span class="input-group-addon">
                  <input type="color" value="<?php echo $module_ml_calc_button_text_color; ?>" style="width: 50px; border: none; cursor: pointer;" onchange="document.getElementById('input-calc-button-text-color').value = this.value">
                </span>
              </div>
              <small class="help-block"><?php echo $help_calc_button_text_color; ?></small>
            </div>
          </div>

          <!-- Налаштування шрифтів -->
          <hr style="margin: 30px 0;">
          <h4 style="margin-bottom: 20px;"><i class="fa fa-font"></i> <?php echo $text_font_settings; ?></h4>
          <p class="help-block" style="margin-left: 15px;"><i class="fa fa-info-circle"></i> <?php echo $help_desktop_fonts; ?></p>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-title-font-size"><?php echo $entry_title_font_size; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="number" name="module_ml_calc_title_font_size" value="<?php echo $module_ml_calc_title_font_size; ?>" placeholder="24" id="input-title-font-size" class="form-control" min="12" max="48" />
                <span class="input-group-addon">px</span>
              </div>
              <small class="help-block"><?php echo $help_title_font_size; ?></small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-label-font-size"><?php echo $entry_label_font_size; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="number" name="module_ml_calc_label_font_size" value="<?php echo $module_ml_calc_label_font_size; ?>" placeholder="14" id="input-label-font-size" class="form-control" min="10" max="24" />
                <span class="input-group-addon">px</span>
              </div>
              <small class="help-block"><?php echo $help_label_font_size; ?></small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-result-font-size"><?php echo $entry_result_font_size; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="number" name="module_ml_calc_result_font_size" value="<?php echo $module_ml_calc_result_font_size; ?>" placeholder="18" id="input-result-font-size" class="form-control" min="12" max="36" />
                <span class="input-group-addon">px</span>
              </div>
              <small class="help-block"><?php echo $help_result_font_size; ?></small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-button-font-size"><?php echo $entry_button_font_size; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="number" name="module_ml_calc_button_font_size" value="<?php echo $module_ml_calc_button_font_size; ?>" placeholder="16" id="input-button-font-size" class="form-control" min="10" max="24" />
                <span class="input-group-addon">px</span>
              </div>
              <small class="help-block"><?php echo $help_button_font_size; ?></small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-breakdown-font-size"><?php echo $entry_breakdown_font_size; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="number" name="module_ml_calc_breakdown_font_size" value="<?php echo $module_ml_calc_breakdown_font_size; ?>" placeholder="14" id="input-breakdown-font-size" class="form-control" min="10" max="24" />
                <span class="input-group-addon">px</span>
              </div>
              <small class="help-block"><?php echo $help_breakdown_font_size; ?></small>
            </div>
          </div>

          <!-- Налаштування шрифтів для мобільних -->
          <hr style="margin: 30px 0;">
          <h4 style="margin-bottom: 20px;"><i class="fa fa-mobile"></i> <?php echo $text_mobile_font_settings; ?></h4>
          <p class="help-block" style="margin-left: 15px;"><i class="fa fa-info-circle"></i> <?php echo $help_mobile_fonts; ?></p>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-mobile-title-font-size"><?php echo $entry_mobile_title_font_size; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="number" name="module_ml_calc_mobile_title_font_size" value="<?php echo $module_ml_calc_mobile_title_font_size; ?>" placeholder="20" id="input-mobile-title-font-size" class="form-control" min="10" max="36" />
                <span class="input-group-addon">px</span>
              </div>
              <small class="help-block"><?php echo $help_mobile_title_font_size; ?></small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-mobile-label-font-size"><?php echo $entry_mobile_label_font_size; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="number" name="module_ml_calc_mobile_label_font_size" value="<?php echo $module_ml_calc_mobile_label_font_size; ?>" placeholder="12" id="input-mobile-label-font-size" class="form-control" min="8" max="20" />
                <span class="input-group-addon">px</span>
              </div>
              <small class="help-block"><?php echo $help_mobile_label_font_size; ?></small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-mobile-result-font-size"><?php echo $entry_mobile_result_font_size; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="number" name="module_ml_calc_mobile_result_font_size" value="<?php echo $module_ml_calc_mobile_result_font_size; ?>" placeholder="16" id="input-mobile-result-font-size" class="form-control" min="10" max="28" />
                <span class="input-group-addon">px</span>
              </div>
              <small class="help-block"><?php echo $help_mobile_result_font_size; ?></small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-mobile-button-font-size"><?php echo $entry_mobile_button_font_size; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="number" name="module_ml_calc_mobile_button_font_size" value="<?php echo $module_ml_calc_mobile_button_font_size; ?>" placeholder="14" id="input-mobile-button-font-size" class="form-control" min="8" max="20" />
                <span class="input-group-addon">px</span>
              </div>
              <small class="help-block"><?php echo $help_mobile_button_font_size; ?></small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-mobile-breakdown-font-size"><?php echo $entry_mobile_breakdown_font_size; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="number" name="module_ml_calc_mobile_breakdown_font_size" value="<?php echo $module_ml_calc_mobile_breakdown_font_size; ?>" placeholder="12" id="input-mobile-breakdown-font-size" class="form-control" min="8" max="20" />
                <span class="input-group-addon">px</span>
              </div>
              <small class="help-block"><?php echo $help_mobile_breakdown_font_size; ?></small>
            </div>
          </div>

          <hr style="margin: 30px 0;">
          <h3 style="margin-bottom: 20px;"><i class="fa fa-question-circle"></i> <?php echo $text_tooltip_settings; ?></h3>
          <p class="help-block" style="margin-left: 15px;"><i class="fa fa-info-circle"></i> <?php echo $help_tooltips; ?></p>

          <!-- Tooltip Payback -->
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_tooltip_payback; ?></label>
            <div class="col-sm-10">
              <div style="margin-bottom: 10px;">
                <select name="module_ml_calc_tooltip_payback_status" class="form-control" style="max-width: 200px;">
                  <?php if ($module_ml_calc_tooltip_payback_status) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select>
              </div>
              <ul class="nav nav-tabs">
                <?php foreach ($languages as $language) { ?>
                <li<?php echo ($language == reset($languages)) ? ' class="active"' : ''; ?>><a href="#tooltip-payback-<?php echo $language['language_id']; ?>" data-toggle="tab"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a></li>
                <?php } ?>
              </ul>
              <div class="tab-content">
                <?php foreach ($languages as $language) { ?>
                <div class="tab-pane<?php echo ($language == reset($languages)) ? ' active' : ''; ?>" id="tooltip-payback-<?php echo $language['language_id']; ?>">
                  <textarea name="module_ml_calc_tooltip_payback[<?php echo $language['language_id']; ?>]" rows="2" class="form-control"><?php echo isset($module_ml_calc_tooltip_payback[$language['language_id']]) ? $module_ml_calc_tooltip_payback[$language['language_id']] : ''; ?></textarea>
                </div>
                <?php } ?>
              </div>
            </div>
          </div>

          <!-- Tooltip Payback Regular -->
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_tooltip_payback_regular; ?></label>
            <div class="col-sm-10">
              <div style="margin-bottom: 10px;">
                <select name="module_ml_calc_tooltip_payback_regular_status" class="form-control" style="max-width: 200px;">
                  <?php if ($module_ml_calc_tooltip_payback_regular_status) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select>
              </div>
              <ul class="nav nav-tabs">
                <?php foreach ($languages as $language) { ?>
                <li<?php echo ($language == reset($languages)) ? ' class="active"' : ''; ?>><a href="#tooltip-payback-regular-<?php echo $language['language_id']; ?>" data-toggle="tab"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a></li>
                <?php } ?>
              </ul>
              <div class="tab-content">
                <?php foreach ($languages as $language) { ?>
                <div class="tab-pane<?php echo ($language == reset($languages)) ? ' active' : ''; ?>" id="tooltip-payback-regular-<?php echo $language['language_id']; ?>">
                  <textarea name="module_ml_calc_tooltip_payback_regular[<?php echo $language['language_id']; ?>]" rows="2" class="form-control"><?php echo isset($module_ml_calc_tooltip_payback_regular[$language['language_id']]) ? $module_ml_calc_tooltip_payback_regular[$language['language_id']] : ''; ?></textarea>
                </div>
                <?php } ?>
              </div>
            </div>
          </div>

          <!-- Tooltip Annual Profit -->
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_tooltip_annual_profit; ?></label>
            <div class="col-sm-10">
              <div style="margin-bottom: 10px;">
                <select name="module_ml_calc_tooltip_annual_profit_status" class="form-control" style="max-width: 200px;">
                  <?php if ($module_ml_calc_tooltip_annual_profit_status) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select>
              </div>
              <ul class="nav nav-tabs">
                <?php foreach ($languages as $language) { ?>
                <li<?php echo ($language == reset($languages)) ? ' class="active"' : ''; ?>><a href="#tooltip-annual-profit-<?php echo $language['language_id']; ?>" data-toggle="tab"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a></li>
                <?php } ?>
              </ul>
              <div class="tab-content">
                <?php foreach ($languages as $language) { ?>
                <div class="tab-pane<?php echo ($language == reset($languages)) ? ' active' : ''; ?>" id="tooltip-annual-profit-<?php echo $language['language_id']; ?>">
                  <textarea name="module_ml_calc_tooltip_annual_profit[<?php echo $language['language_id']; ?>]" rows="2" class="form-control"><?php echo isset($module_ml_calc_tooltip_annual_profit[$language['language_id']]) ? $module_ml_calc_tooltip_annual_profit[$language['language_id']] : ''; ?></textarea>
                </div>
                <?php } ?>
              </div>
            </div>
          </div>

          <!-- Tooltip Monthly Profit -->
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_tooltip_monthly_profit; ?></label>
            <div class="col-sm-10">
              <div style="margin-bottom: 10px;">
                <select name="module_ml_calc_tooltip_monthly_profit_status" class="form-control" style="max-width: 200px;">
                  <?php if ($module_ml_calc_tooltip_monthly_profit_status) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select>
              </div>
              <ul class="nav nav-tabs">
                <?php foreach ($languages as $language) { ?>
                <li<?php echo ($language == reset($languages)) ? ' class="active"' : ''; ?>><a href="#tooltip-monthly-profit-<?php echo $language['language_id']; ?>" data-toggle="tab"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a></li>
                <?php } ?>
              </ul>
              <div class="tab-content">
                <?php foreach ($languages as $language) { ?>
                <div class="tab-pane<?php echo ($language == reset($languages)) ? ' active' : ''; ?>" id="tooltip-monthly-profit-<?php echo $language['language_id']; ?>">
                  <textarea name="module_ml_calc_tooltip_monthly_profit[<?php echo $language['language_id']; ?>]" rows="2" class="form-control"><?php echo isset($module_ml_calc_tooltip_monthly_profit[$language['language_id']]) ? $module_ml_calc_tooltip_monthly_profit[$language['language_id']] : ''; ?></textarea>
                </div>
                <?php } ?>
              </div>
            </div>
          </div>

          <!-- Tooltip Monthly Expenses -->
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_tooltip_monthly_expenses; ?></label>
            <div class="col-sm-10">
              <div style="margin-bottom: 10px;">
                <select name="module_ml_calc_tooltip_monthly_expenses_status" class="form-control" style="max-width: 200px;">
                  <?php if ($module_ml_calc_tooltip_monthly_expenses_status) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select>
              </div>
              <ul class="nav nav-tabs">
                <?php foreach ($languages as $language) { ?>
                <li<?php echo ($language == reset($languages)) ? ' class="active"' : ''; ?>><a href="#tooltip-monthly-expenses-<?php echo $language['language_id']; ?>" data-toggle="tab"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a></li>
                <?php } ?>
              </ul>
              <div class="tab-content">
                <?php foreach ($languages as $language) { ?>
                <div class="tab-pane<?php echo ($language == reset($languages)) ? ' active' : ''; ?>" id="tooltip-monthly-expenses-<?php echo $language['language_id']; ?>">
                  <textarea name="module_ml_calc_tooltip_monthly_expenses[<?php echo $language['language_id']; ?>]" rows="2" class="form-control"><?php echo isset($module_ml_calc_tooltip_monthly_expenses[$language['language_id']]) ? $module_ml_calc_tooltip_monthly_expenses[$language['language_id']] : ''; ?></textarea>
                </div>
                <?php } ?>
              </div>
            </div>
          </div>

          <hr style="margin: 30px 0;">
          <h3 style="margin-bottom: 20px;"><i class="fa fa-calculator"></i> <?php echo $text_result_tooltip_settings; ?></h3>
          <p class="help-block" style="margin-left: 15px;"><i class="fa fa-info-circle"></i> <?php echo $help_result_tooltips; ?></p>

          <!-- Show Result Tooltips -->
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_show_result_tooltips; ?></label>
            <div class="col-sm-10">
              <select name="module_ml_calc_show_result_tooltips" class="form-control" style="max-width: 200px;">
                <?php if ($module_ml_calc_show_result_tooltips) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

        </form>
      </div>
      <div class="panel-footer" style="text-align: center; color: #999;">
        <small>
          <strong>ML Calculator</strong> v<?php echo $module_version; ?> | Compatible with OpenCart 2.3.0.x
        </small>
      </div>
    </div>

    <!-- Інструкція по використанню шорткодів -->
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-info-circle"></i> Як використовувати калькулятор</h3>
      </div>
      <div class="panel-body">
        <h4>1. Автоматичне відображення на сторінці товару</h4>
        <p>Калькулятор автоматично з'являється в окремому табі <strong>"Калькулятор окупності"</strong> для товарів, у яких поле <strong>JAN</strong> = <code>1</code></p>
        <p><strong>Як увімкнути:</strong></p>
        <ol>
          <li>Перейдіть в <strong>Catalog → Products</strong></li>
          <li>Відредагуйте потрібний товар</li>
          <li>На вкладці <strong>Data</strong> знайдіть поле <strong>JAN</strong></li>
          <li>Введіть значення <code>1</code></li>
          <li>Натисніть <strong>Save</strong></li>
        </ol>

        <hr>

        <h4>2. Використання шорткодів</h4>
        <p>Ви можете вставити калькулятор в будь-яке місце сайту (опис товару, статтю, CMS-сторінку) за допомогою шорткодів.</p>

        <h5>Простий шорткод (базовий)</h5>
        <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;">[ml_calc]</pre>
        <p>Відображає калькулятор для поточного товару (якщо вставлено в опис товару).</p>

        <h5>Шорткод з параметрами</h5>
        <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;">[ml_calc product_id="118" show_title="1" title="Розрахунок окупності обладнання"]</pre>

        <h5>Доступні параметри:</h5>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Параметр</th>
              <th>Опис</th>
              <th>Значення</th>
              <th>За замовчуванням</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><code>product_id</code></td>
              <td>ID товару для калькулятора</td>
              <td>Число (наприклад: 118)</td>
              <td>Поточний товар</td>
            </tr>
            <tr>
              <td><code>current</code></td>
              <td>Показати калькулятор для поточного товару на його сторінці (ігнорує перевірку JAN=1, компактний вигляд)</td>
              <td>1 (так) або 0 (ні)</td>
              <td>0</td>
            </tr>
            <tr>
              <td><code>show_title</code></td>
              <td>Показувати заголовок</td>
              <td>1 (так) або 0 (ні)</td>
              <td>1</td>
            </tr>
            <tr>
              <td><code>title</code></td>
              <td>Власний заголовок калькулятора (текст або мовний ключ)</td>
              <td>
                Текст: <code>"Мій заголовок"</code><br>
                Мовний ключ: <code>lang:text_shortcode_title_alt1</code>
              </td>
              <td>За замовчуванням з мовного файлу</td>
            </tr>
          </tbody>
        </table>

        <div class="alert alert-info">
          <i class="fa fa-language"></i>
          <strong>Багатомовність:</strong> Щоб заголовок автоматично перекладався, використовуйте формат <code>lang:ключ</code>. Наприклад: <code>title="lang:text_shortcode_title_alt1"</code> автоматично покаже українською "Розрахуйте вигоду від покупки", російською "Рассчитайте выгоду от покупки", англійською "Calculate your purchase benefit".
        </div>

        <h5>Доступні мовні ключі для заголовків:</h5>
        <ul>
          <li><code>text_shortcode_title</code> - "Калькулятор окупності обладнання" (за замовчуванням)</li>
          <li><code>text_shortcode_title_alt1</code> - "Розрахуйте вигоду від покупки"</li>
          <li><code>text_shortcode_title_alt2</code> - "Дізнайтесь термін окупності"</li>
          <li><code>text_shortcode_title_alt3</code> - "Розрахунок окупності інвестицій"</li>
        </ul>

        <h5>Приклади використання:</h5>

        <p><strong>Калькулятор для поточного товару (компактний вигляд на сторінці товару):</strong></p>
        <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;">[ml_calc current="1"]</pre>
        <div class="alert alert-success">
          <i class="fa fa-star"></i> <strong>Рекомендовано!</strong> Використовуйте цей варіант при вставці в опис товару на його сторінці. Калькулятор буде компактним, без вибору товару та без самопосилання.
        </div>

        <p><strong>Калькулятор для конкретного товару:</strong></p>
        <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;">[ml_calc product_id="118"]</pre>

        <p><strong>Калькулятор з багатомовним заголовком:</strong></p>
        <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;">[ml_calc title="lang:text_shortcode_title_alt1"]</pre>

        <p><strong>Калькулятор з власним текстовим заголовком (тільки одна мова):</strong></p>
        <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;">[ml_calc title="Розрахуйте вигоду від покупки"]</pre>

        <p><strong>Калькулятор без заголовка:</strong></p>
        <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;">[ml_calc show_title="0"]</pre>

        <p><strong>Компактний калькулятор для поточного товару без заголовка:</strong></p>
        <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;">[ml_calc current="1" show_title="0"]</pre>

        <p><strong>Повний приклад з усіма параметрами:</strong></p>
        <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;">[ml_calc product_id="118" show_title="1" title="Дізнайтесь термін окупності обладнання!"]</pre>

        <hr>

        <h4>3. Важливо знати</h4>
        <ul>
          <li>Параметр <code>current="1"</code> ігнорує перевірку поля JAN та застосовує компактний вигляд калькулятора (зменшені відступи для вузького контентного блоку)</li>
          <li>При використанні <code>current="1"</code> назва товару не буде посиланням на себе, а селектор товару буде приховано</li>
          <li>Калькулятор використовує <strong>акційну ціну</strong> (special price), якщо вона встановлена, та показує окремо окупність без акції (якщо увімкнено в налаштуваннях)</li>
          <li>Ціни автоматично конвертуються з USD в поточну валюту за актуальним курсом</li>
          <li>Калькулятор враховує опції товару (якщо вони впливають на ціну)</li>
          <li>Всі параметри за замовчуванням можна налаштувати вище на цій сторінці</li>
          <li>На мобільних пристроях (< 768px) ползунки автоматично збільшуються для зручності</li>
        </ul>

        <div class="alert alert-info">
          <i class="fa fa-info-circle"></i>
          <strong>Порада:</strong> Для додавання калькулятора на CMS-сторінку, просто вставте шорткод в редакторі сторінки через <strong>Design → Layouts</strong> або безпосередньо в контент сторінки.
        </div>
      </div>
    </div>

  </div>
</div>
<?php echo $footer; ?>
