<?php echo $header; ?>
<link rel="stylesheet" href="catalog/view/theme/default/template/extension/module/css/ml_calc_salon.css">

<div class="container">
  <div class="row">
    <?php echo $column_left; ?>

    <div id="content" class="col-sm-12">
      <?php echo $column_right; ?>

      <h1 class="ml-calc-salon-title"><?php echo $heading_title; ?></h1>

      <div class="ml-calc-salon-wrapper">

        <!-- Корзина товаров -->
        <div class="ml-calc-salon-cart">
          <h3><?php echo $text_product_name; ?></h3>
          <div id="salon-products-list" class="salon-products-list">
            <!-- Здесь будут карточки товаров -->
          </div>

          <div class="ml-calc-salon-add-product">
            <select id="product-selector" class="form-control">
              <option value="">-- <?php echo $text_add_product; ?> --</option>
            </select>
          </div>
        </div>

        <!-- Общие параметры салона -->
        <div class="ml-calc-salon-common-params">
          <h3>Общие параметры салона</h3>

          <div class="ml-calc-row">
            <div class="ml-calc-field">
              <label><?php echo $entry_working_days; ?></label>
              <div class="ml-calc-value" id="working-days-value"><?php echo $default_working_days; ?></div>
              <input type="range"
                     id="working-days-slider"
                     class="ml-calc-slider"
                     min="1"
                     max="31"
                     value="<?php echo $default_working_days; ?>"
                     step="1">
            </div>

            <div class="ml-calc-field">
              <label><?php echo $entry_rent; ?></label>
              <div class="ml-calc-value" id="rent-value"><?php echo $default_rent; ?></div>
              <input type="range"
                     id="rent-slider"
                     class="ml-calc-slider"
                     min="0"
                     max="100000"
                     value="<?php echo $default_rent; ?>"
                     step="1000">
            </div>
          </div>

          <div class="ml-calc-row">
            <div class="ml-calc-field">
              <label><?php echo $entry_utilities; ?></label>
              <div class="ml-calc-value" id="utilities-value"><?php echo $default_utilities; ?></div>
              <input type="range"
                     id="utilities-slider"
                     class="ml-calc-slider"
                     min="0"
                     max="20000"
                     value="<?php echo $default_utilities; ?>"
                     step="500">
            </div>
          </div>
        </div>

        <!-- Кнопки действий -->
        <div class="ml-calc-salon-actions">
          <button id="calculate-btn" class="btn btn-primary btn-lg"><?php echo $text_calculate; ?></button>
          <button id="save-btn" class="btn btn-success" style="display:none;"><?php echo $text_save_calculation; ?></button>
        </div>

        <!-- Результаты -->
        <div id="results-block" class="ml-calc-salon-results" style="display:none;">
          <h3><?php echo $text_results; ?></h3>

          <div class="ml-calc-salon-total-results">
            <div class="result-item">
              <span class="result-label"><?php echo $text_total_investment; ?>:</span>
              <span class="result-value" id="total-investment">0 ГРН</span>
            </div>
            <div class="result-item">
              <span class="result-label"><?php echo $text_total_payback; ?>:</span>
              <span class="result-value" id="total-payback"><?php echo $text_not_applicable; ?></span>
            </div>
            <div class="result-item">
              <span class="result-label"><?php echo $text_total_profit; ?>:</span>
              <span class="result-value" id="total-profit">0 ГРН</span>
            </div>
          </div>

          <h4><?php echo $text_product_results; ?></h4>
          <div id="product-results" class="ml-calc-salon-product-results">
            <!-- Здесь будут результаты по каждому товару -->
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<input type="hidden" id="default-clients-per-day" value="<?php echo $default_clients_per_day; ?>">
<input type="hidden" id="default-procedure-cost" value="<?php echo $default_procedure_cost; ?>">
<input type="hidden" id="default-master-percent" value="<?php echo $default_master_percent; ?>">
<input type="hidden" id="saved-calculation" value='<?php echo $saved_calculation ? htmlspecialchars($saved_calculation, ENT_QUOTES, 'UTF-8') : ''; ?>'>

<script src="catalog/view/javascript/ml_calc_salon.js"></script>

<?php echo $footer; ?>
