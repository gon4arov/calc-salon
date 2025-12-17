<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content" class="ml-salon">
  <?php echo $content_top; ?>
  <div class="container">
    <h1 class="ml-salon__title"><?php echo $heading_title; ?></h1>
    <p class="ml-salon__intro"><?php echo $text_intro; ?></p>

    <div class="ml-salon__section">
      <div class="ml-salon__section-header">
        <h3><?php echo $text_presets; ?></h3>
        <span class="ml-salon__hint"><?php echo $text_global_hint; ?></span>
      </div>
      <div class="ml-salon__presets" id="ml-salon-presets"></div>
      <div class="ml-salon__globals">
        <label>
          <span><?php echo $label_working_days; ?></span>
          <input type="number" id="ml-salon-working-days" min="10" max="31" step="1">
        </label>
        <label>
          <span><?php echo $label_rent; ?></span>
          <input type="number" id="ml-salon-rent" min="0" step="500">
        </label>
        <label>
          <span><?php echo $label_utilities; ?></span>
          <input type="number" id="ml-salon-utilities" min="0" step="200">
        </label>
      </div>
    </div>

    <div class="ml-salon__section">
      <div class="ml-salon__section-header">
        <h3><?php echo $text_procedures; ?></h3>
        <span class="ml-salon__hint"><?php echo $text_procedure_hint; ?></span>
      </div>
      <div class="ml-salon__procedures" id="ml-salon-procedures"></div>
      <div class="ml-salon__suggestions" id="ml-salon-suggestions"></div>
    </div>

    <div class="ml-salon__section">
      <div class="ml-salon__section-header">
        <h3><?php echo $text_devices; ?></h3>
        <span class="ml-salon__hint"><?php echo $text_device_hint; ?></span>
      </div>

      <div class="ml-salon__add">
        <label>
          <span><?php echo $label_add_device; ?></span>
          <select id="ml-salon-device-select"></select>
        </label>
        <button type="button" class="ml-salon__btn" id="ml-salon-add-device"><?php echo $button_add_device; ?></button>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered ml-salon__table">
          <thead>
            <tr>
              <th><?php echo $column_device; ?></th>
              <th><?php echo $column_clients; ?></th>
              <th><?php echo $column_price; ?></th>
              <th><?php echo $column_cost; ?></th>
              <th><?php echo $column_revenue; ?></th>
              <th><?php echo $column_actions; ?></th>
            </tr>
          </thead>
          <tbody id="ml-salon-device-rows"></tbody>
        </table>
      </div>
    </div>

    <div class="ml-salon__section">
      <div class="ml-salon__totals" id="ml-salon-totals">
        <div class="ml-salon__card">
          <div class="ml-salon__card-label"><?php echo $text_total_capex; ?></div>
          <div class="ml-salon__card-value" data-field="capex">—</div>
        </div>
        <div class="ml-salon__card">
          <div class="ml-salon__card-label"><?php echo $text_total_revenue; ?></div>
          <div class="ml-salon__card-value" data-field="revenue">—</div>
        </div>
        <div class="ml-salon__card">
          <div class="ml-salon__card-label"><?php echo $text_total_profit; ?></div>
          <div class="ml-salon__card-value" data-field="profit">—</div>
        </div>
        <div class="ml-salon__card">
          <div class="ml-salon__card-label"><?php echo $text_total_payback; ?></div>
          <div class="ml-salon__card-value" data-field="payback">—</div>
        </div>
      </div>
    </div>

    <div class="ml-salon__section">
      <div class="ml-salon__section-header">
        <h3><?php echo $text_email; ?></h3>
      </div>
      <div class="ml-salon__email">
        <input type="email" id="ml-salon-email" placeholder="<?php echo $label_email; ?>">
        <button type="button" class="ml-salon__btn" id="ml-salon-send"><?php echo $button_send; ?></button>
        <div class="ml-salon__email-status" id="ml-salon-email-status"></div>
      </div>
    </div>
  </div>
  <?php echo $content_bottom; ?>
</div>
<?php echo $footer; ?>

<script>
window.MLSalonCalcConfig = <?php echo json_encode(array(
  'lang' => array(
    'currency' => $text_currency,
    'months' => $text_months,
    'total_capex' => $text_total_capex,
    'total_revenue' => $text_total_revenue,
    'total_profit' => $text_total_profit,
    'total_payback' => $text_total_payback,
    'email_success' => $text_email_success,
    'email_error' => $text_email_error,
    'email_required' => $error_email_required,
    'email_invalid' => $error_email_invalid,
    'add_device' => $button_add_device,
    'remove' => '×'
  ),
  'presets' => $ml_salon_calc['presets'],
  'procedures' => $ml_salon_calc['procedures'],
  'devices' => $ml_salon_calc['devices'],
  'defaults' => $ml_salon_calc['defaults'],
  'action_send' => $action_send
)); ?>;
</script>
