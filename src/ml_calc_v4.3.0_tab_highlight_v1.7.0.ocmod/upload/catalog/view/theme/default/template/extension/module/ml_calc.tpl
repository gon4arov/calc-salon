<style>
.ml-calc-wrapper {
    background-color: <?php echo $background_color; ?>;
    color: <?php echo $text_color; ?>;
    --ml-calc-income-color: <?php echo $income_color; ?>;
    --ml-calc-expense-color: <?php echo $expense_color; ?>;
}
.ml-calc-title {
    color: <?php echo $primary_color; ?>;
    font-size: <?php echo $title_font_size; ?>px;
}
.ml-calc-field label {
    font-size: <?php echo $label_font_size; ?>px;
    color: <?php echo $text_color; ?>;
}
.ml-calc-value {
    color: <?php echo $primary_color; ?>;
    font-size: <?php echo $result_font_size; ?>px;
}
.ml-calc-result-main {
    color: <?php echo $primary_color; ?>;
    font-size: <?php echo $result_font_size; ?>px;
}
.ml-calc-result-label {
    font-size: <?php echo $label_font_size; ?>px;
}
.ml-calc-label-nowrap {
    white-space: nowrap;
}
.ml-calc-result-main#payback-result {
    background-color: #ffe066;
    color: <?php echo $primary_color; ?>;
    padding: 6px 14px;
    border-radius: 8px;
    display: inline-block;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
    font-size: <?php echo $result_font_size + 1; ?>px;
    font-weight: bold;
}
#payback-result-regular {
    background-color: #fff3cd;
    color: <?php echo $primary_color; ?>;
    padding: 6px 10px;
    border-radius: 8px;
    display: inline-block;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
    font-size: <?php echo $result_font_size + 1; ?>px;
    font-weight: bold;
    white-space: nowrap;
}
/* Tooltip Icon */
.ml-calc-tooltip-icon {
    display: inline-block;
    width: 14px;
    height: 14px;
    background: <?php echo $primary_color; ?>;
    color: #fff;
    border-radius: 50%;
    text-align: center;
    line-height: 14px;
    font-size: 9px;
    font-weight: bold;
    margin-left: 1px;
    cursor: help;
    position: relative;
    vertical-align: super;
    top: -2px;
}
.ml-calc-tooltip-icon::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: calc(100% + 8px);
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.9);
    color: #fff;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: normal;
    white-space: normal;
    max-width: 250px;
    width: max-content;
    text-align: left;
    line-height: 1.4;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s, transform 0.3s;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}
.ml-calc-tooltip-icon::before {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 6px solid transparent;
    border-top-color: rgba(0, 0, 0, 0.9);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
    z-index: 1001;
}
.ml-calc-tooltip-icon:hover::after,
.ml-calc-tooltip-icon:hover::before,
.ml-calc-tooltip-icon:focus::after,
.ml-calc-tooltip-icon:focus::before {
    opacity: 1;
}
.ml-calc-tooltip-icon:focus {
    outline: 2px solid <?php echo $primary_color; ?>;
    outline-offset: 2px;
}
/* Result Calculation Tooltips */
.ml-calc-result-tooltip {
    position: relative;
    cursor: help;
}
.ml-calc-result-tooltip::after {
    content: attr(data-formula);
    position: absolute;
    bottom: calc(100% + 8px);
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.95);
    color: #fff;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: normal;
    font-family: 'Courier New', monospace;
    white-space: pre-line;
    min-width: 200px;
    max-width: 350px;
    text-align: left;
    line-height: 1.6;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s, transform 0.3s;
    z-index: 1000;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
}
.ml-calc-result-tooltip::before {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 7px solid transparent;
    border-top-color: rgba(0, 0, 0, 0.95);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
    z-index: 1001;
}
.ml-calc-result-tooltip:hover::after,
.ml-calc-result-tooltip:hover::before {
    opacity: 1;
}
.ml-calc-slider {
    --progress: 0%;
}
.ml-calc-slider::-webkit-slider-thumb {
    background: <?php echo $button_color; ?>;
}
.ml-calc-slider::-moz-range-thumb {
    background: <?php echo $button_color; ?>;
}
.ml-calc-slider[data-track="income"]::-webkit-slider-runnable-track {
    background: linear-gradient(to right, var(--ml-calc-income-color) 0%, var(--ml-calc-income-color) var(--progress), #ddd var(--progress), #ddd 100%);
}
.ml-calc-slider[data-track="expense"]::-webkit-slider-runnable-track {
    background: linear-gradient(to right, var(--ml-calc-expense-color) 0%, var(--ml-calc-expense-color) var(--progress), #ddd var(--progress), #ddd 100%);
}
.ml-calc-slider[data-track="income"]::-moz-range-progress,
.ml-calc-slider[data-track="income"]::-ms-fill-lower {
    background: var(--ml-calc-income-color);
    border-radius: 8px;
}
.ml-calc-slider[data-track="expense"]::-moz-range-progress,
.ml-calc-slider[data-track="expense"]::-ms-fill-lower {
    background: var(--ml-calc-expense-color);
    border-radius: 8px;
}
.ml-calc-slider[data-track="income"]::-moz-range-track,
.ml-calc-slider[data-track="income"]::-ms-fill-upper {
    background: #ddd;
    border-radius: 8px;
}
.ml-calc-slider[data-track="expense"]::-moz-range-track,
.ml-calc-slider[data-track="expense"]::-ms-fill-upper {
    background: #ddd;
    border-radius: 8px;
}
.ml-calc-breakdown-title {
    color: <?php echo $primary_color; ?>;
    font-size: <?php echo $breakdown_font_size; ?>px;
}
.ml-calc-breakdown-label,
.ml-calc-breakdown-value {
    font-size: <?php echo $breakdown_font_size; ?>px;
}
.ml-calc-result {
    border-color: <?php echo $result_border_color; ?>;
    padding: 30px 60px;
}
.ml-calc-email {
    margin-top: 24px;
    padding: 0;
    background: transparent;
    border: none;
    border-radius: 0;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 12px;
    width: 100%;
}
.ml-calc-email__label {
    font-weight: 600;
    color: <?php echo $text_color; ?>;
}
.ml-calc-email__controls {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    justify-content: center;
}
.ml-calc-email input[type="email"] {
    min-width: 245px;
    padding: 8px 10px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 14px;
}
.ml-calc-email button {
    background: <?php echo $button_color; ?>;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 8px 14px;
    font-size: <?php echo $button_font_size; ?>px;
    cursor: pointer;
    transition: transform 0.1s ease, box-shadow 0.2s ease;
}
.ml-calc-email button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
.ml-calc-email__status {
    display: none;
    font-size: 13px;
    line-height: 1.4;
    width: 100%;
    text-align: center;
    margin-top: 6px;
    padding: 8px 10px;
    border-radius: 6px;
    color: #111;
}
.ml-calc-status--success {
    background: #d7f5e0;
}
.ml-calc-status--error {
    background: #f8d7da;
}
.ml-calc-status--info {
    background: #e9ecef;
}
.ml-calc-product-options {
    margin-top: 15px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.ml-calc-product-option__title {
    font-weight: 600;
    margin-bottom: 6px;
}
.ml-calc-product-option__choices {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.ml-calc-product-option__choice {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: <?php echo $label_font_size * 0.9; ?>px;
    padding: 4px 10px;
    background: rgba(0, 0, 0, 0.04);
    border-radius: 20px;
}
.ml-calc-product-option__choice input[type="radio"] {
    margin: 0;
}
.ml-calc-product-option__choice span {
    font-size: 0.9em;
}
.ml-calc-product-option__select select {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
/* Дохідна частина (прибуток) - зелений колір */
#monthly-profit-result,
#profit-result {
    color: <?php echo $income_color; ?>;
    font-weight: bold;
}
/* Витратна частина (витрати) - червоний колір */
#monthly-expenses-result,
#monthly-expense-rent,
#monthly-expense-utilities,
#monthly-expense-master {
    color: <?php echo $expense_color; ?>;
    font-weight: bold;
}

/* Мобільні стилі для екранів до 768px */
@media (max-width: 768px) {
    .ml-calc-title {
        font-size: <?php echo $mobile_title_font_size; ?>px;
    }
    .ml-calc-field label {
        font-size: <?php echo $mobile_label_font_size; ?>px;
    }
    .ml-calc-value {
        font-size: <?php echo $mobile_result_font_size; ?>px;
    }
    .ml-calc-result-main {
        font-size: <?php echo $mobile_result_font_size; ?>px;
    }
    .ml-calc-result-label {
        font-size: <?php echo $mobile_label_font_size; ?>px;
    }
    .ml-calc-result {
        padding: 20px;
    }
    .ml-calc-breakdown-title {
        font-size: <?php echo $mobile_breakdown_font_size; ?>px;
    }
    .ml-calc-breakdown-label,
    .ml-calc-breakdown-value {
        font-size: <?php echo $mobile_breakdown_font_size; ?>px;
    }
    .ml-calc-product-option__choice {
        font-size: <?php echo $mobile_label_font_size * 0.9; ?>px;
    }
    .ml-calc-result-main#payback-result,
    #payback-result-regular {
        font-size: <?php echo max(12, $mobile_result_font_size - 2); ?>px;
        padding: 4px 8px;
    }
    .ml-calc-email {
        flex-direction: column;
        align-items: flex-start;
    }
    .ml-calc-email__controls {
        width: 100%;
    }
    .ml-calc-email input[type="email"] {
        width: 100%;
        min-width: unset;
    }
    .ml-calc-email button {
        width: 100%;
        text-align: center;
    }
    /* Tooltips на мобильных - по клику */
    .ml-calc-tooltip::after {
        max-width: 200px;
        font-size: 12px;
    }
}
</style>
<script>
// Переводы для формул тултипов
window.mlCalcFormulaLabels = {
    dailyIncome: <?php echo json_encode($formula_daily_income); ?>,
    monthlyIncome: <?php echo json_encode($formula_monthly_income); ?>,
    masterExpenses: <?php echo json_encode($formula_master_expenses); ?>,
    totalExpenses: <?php echo json_encode($formula_total_expenses); ?>,
    netProfit: <?php echo json_encode($formula_net_profit); ?>,
    payback: <?php echo json_encode($formula_payback); ?>,
    days: <?php echo json_encode($formula_days); ?>,
    monthsShort: <?php echo json_encode($formula_months_short); ?>,
    monthlyProfit: <?php echo json_encode($formula_monthly_profit); ?>,
    annualProfit: <?php echo json_encode($formula_annual_profit); ?>,
    regularPrice: <?php echo json_encode($formula_regular_price); ?>,
    perMonth: <?php echo json_encode($formula_per_month); ?>
};

window.mlCalcEmailTexts = {
    sending: <?php echo json_encode($text_email_sending); ?>,
    success: <?php echo json_encode($text_email_success); ?>,
    errorEmailRequired: <?php echo json_encode($error_email_required); ?>,
    errorEmailInvalid: <?php echo json_encode($error_email_invalid); ?>,
    errorCalculation: <?php echo json_encode($error_email_calculation); ?>,
    errorSend: <?php echo json_encode($error_email_send); ?>
};
</script>
<div class="ml-calc-wrapper" data-show-regular-payback="<?php echo $show_regular_payback ? '1' : '0'; ?>" data-show-result-tooltips="<?php echo $show_result_tooltips ? '1' : '0'; ?>">
    <h2 class="ml-calc-title"><?php echo $heading_title; ?></h2>
    <!-- Результат -->
    <div class="ml-calc-result" id="ml-calc-result" style="display: none;" aria-live="polite" aria-atomic="true">
        <!-- Индикатор загрузки -->
        <div class="ml-calc-loading-overlay">
            <div class="ml-calc-spinner"></div>
        </div>
        <div class="ml-calc-result__layout">
            <?php if (!empty($product_summary)) { ?>
            <div class="ml-calc-result__product">
                <div class="ml-calc-result__image">
                    <img src="<?php echo $product_summary['image']; ?>" alt="<?php echo $product_summary['name']; ?>">
                </div>
                <div class="ml-calc-result__name">
                    <?php if (!empty($product_summary['href'])) { ?>
                    <a href="<?php echo $product_summary['href']; ?>" target="_blank" rel="noopener noreferrer"><?php echo $product_summary['name']; ?></a>
                    <?php } else { ?>
                    <?php echo $product_summary['name']; ?>
                    <?php } ?>
                </div>
                <?php if (!empty($product_summary['price_special']) || !empty($product_summary['price_regular'])) { ?>
                <div class="ml-calc-result__price">
                    <?php if (!empty($product_summary['price_special'])) { ?>
                        <?php if (!empty($product_summary['price_regular'])) { ?>
                        <span class="ml-calc-result__price-old" data-ml-calc-regular="<?php echo $product_summary['price_regular_value']; ?>"><?php echo $product_summary['price_regular']; ?></span>
                        <?php } ?>
                        <span class="ml-calc-result__price-current"
                              data-ml-calc-special="<?php echo $product_summary['price_special_value']; ?>"
                              data-ml-calc-discount-ratio="<?php echo $product_summary['price_discount_ratio']; ?>">
                            <?php echo $product_summary['price_special']; ?>
                        </span>
                    <?php } elseif (!empty($product_summary['price_regular'])) { ?>
                        <span class="ml-calc-result__price-regular"><?php echo $product_summary['price_regular']; ?></span>
                    <?php } ?>
                </div>
                <?php } ?>
                <?php if (!empty($product_options)) { ?>
                <div class="ml-calc-product-options">
                    <?php foreach ($product_options as $option) { ?>
                        <div class="ml-calc-product-option">
                            <div class="ml-calc-product-option__title">
                                <?php echo $option['name']; ?>
                                <?php if ($option['required']) { ?>
                                    <span style="color: #dc3545;">*</span>
                                <?php } ?>
                            </div>
                            <?php if ($option['type'] === 'radio') { ?>
                                <div class="ml-calc-product-option__choices">
                                    <?php foreach ($option['values'] as $value) { ?>
                                        <label class="ml-calc-product-option__choice">
                                            <input type="radio"
                                                   name="option[<?php echo $option['product_option_id']; ?>]"
                                                   value="<?php echo $value['product_option_value_id']; ?>"
                                                   data-price="<?php echo number_format($value['price_raw'], 4, '.', ''); ?>"
                                                   data-prefix="<?php echo $value['price_prefix']; ?>"
                                                   <?php if ($value['selected']) { ?>checked="checked"<?php } ?>
                                                   data-ml-calc-option="1">
                                            <span><?php echo $value['name']; ?></span>
                                        </label>
                                    <?php } ?>
                                </div>
                            <?php } elseif ($option['type'] === 'select') { ?>
                                <div class="ml-calc-product-option__select">
                                    <select name="option[<?php echo $option['product_option_id']; ?>]" data-ml-calc-option="1">
                                        <?php foreach ($option['values'] as $value) { ?>
                                            <option value="<?php echo $value['product_option_value_id']; ?>"
                                                    data-price="<?php echo number_format($value['price_raw'], 4, '.', ''); ?>"
                                                    data-prefix="<?php echo $value['price_prefix']; ?>"
                                                    <?php if ($value['selected']) { ?>selected="selected"<?php } ?>>
                                                <?php echo $value['name']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
            <div class="ml-calc-result__content">
                <div class="ml-calc-warning" id="ml-calc-warning" style="display: none;"></div>
                <div class="ml-calc-result-item">
                    <span class="ml-calc-result-label">
                        <?php echo $text_payback; ?><span class="ml-calc-label-nowrap">:<?php if ($tooltip_payback_status && !empty($tooltip_payback)) { ?><span class="ml-calc-tooltip-icon" data-tooltip="<?php echo htmlspecialchars($tooltip_payback, ENT_QUOTES, 'UTF-8'); ?>" tabindex="0" role="button" aria-label="<?php echo htmlspecialchars($tooltip_payback, ENT_QUOTES, 'UTF-8'); ?>">?</span><?php } ?></span>
                    </span>
                    <span class="ml-calc-result-value ml-calc-result-main" id="payback-result"><?php echo $text_not_applicable; ?></span>
                </div>
                <div class="ml-calc-result-item" id="payback-regular-row" style="display: none;">
                    <span class="ml-calc-result-label">
                        <?php echo $text_payback_regular; ?><span class="ml-calc-label-nowrap">:<?php if ($tooltip_payback_regular_status && !empty($tooltip_payback_regular)) { ?><span class="ml-calc-tooltip-icon" data-tooltip="<?php echo htmlspecialchars($tooltip_payback_regular, ENT_QUOTES, 'UTF-8'); ?>" tabindex="0" role="button" aria-label="<?php echo htmlspecialchars($tooltip_payback_regular, ENT_QUOTES, 'UTF-8'); ?>">?</span><?php } ?></span>
                    </span>
                    <span class="ml-calc-result-value" id="payback-result-regular"><?php echo $text_not_applicable; ?></span>
                </div>
                <div class="ml-calc-result-item">
                    <span class="ml-calc-result-label">
                        <?php echo $text_profit; ?><span class="ml-calc-label-nowrap">:<?php if ($tooltip_annual_profit_status && !empty($tooltip_annual_profit)) { ?><span class="ml-calc-tooltip-icon" data-tooltip="<?php echo htmlspecialchars($tooltip_annual_profit, ENT_QUOTES, 'UTF-8'); ?>" tabindex="0" role="button" aria-label="<?php echo htmlspecialchars($tooltip_annual_profit, ENT_QUOTES, 'UTF-8'); ?>">?</span><?php } ?></span>
                    </span>
                    <span class="ml-calc-result-value ml-calc-result-main" id="profit-result">0 ГРН</span>
                </div>
                <br id="ml-calc-breakdown-separator" style="display: none;">
                <div class="ml-calc-breakdown" id="ml-calc-breakdown" style="display: none;">
                    <div class="ml-calc-breakdown-title"><?php echo $text_monthly_breakdown; ?></div>
                    <div class="ml-calc-breakdown-row">
                        <span class="ml-calc-breakdown-label">
                            <?php echo $text_monthly_profit; ?><span class="ml-calc-label-nowrap">:<?php if ($tooltip_monthly_profit_status && !empty($tooltip_monthly_profit)) { ?><span class="ml-calc-tooltip-icon" data-tooltip="<?php echo htmlspecialchars($tooltip_monthly_profit, ENT_QUOTES, 'UTF-8'); ?>">?</span><?php } ?></span>
                        </span>
                        <span class="ml-calc-breakdown-value" id="monthly-profit-result">0 ГРН</span>
                    </div>
                    <div class="ml-calc-breakdown-row">
                        <span class="ml-calc-breakdown-label">
                            <?php echo $text_monthly_expenses; ?><span class="ml-calc-label-nowrap">:<?php if ($tooltip_monthly_expenses_status && !empty($tooltip_monthly_expenses)) { ?><span class="ml-calc-tooltip-icon" data-tooltip="<?php echo htmlspecialchars($tooltip_monthly_expenses, ENT_QUOTES, 'UTF-8'); ?>">?</span><?php } ?></span>
                        </span>
                        <span class="ml-calc-breakdown-value" id="monthly-expenses-result">0 ГРН</span>
                    </div>
                    <div class="ml-calc-breakdown-subrow" id="monthly-expense-rent-row" style="display: none;">
                        <span class="ml-calc-breakdown-label"><?php echo $text_monthly_rent; ?>:</span>
                        <span class="ml-calc-breakdown-value" id="monthly-expense-rent">0 ГРН</span>
                    </div>
                    <div class="ml-calc-breakdown-subrow" id="monthly-expense-utilities-row" style="display: none;">
                        <span class="ml-calc-breakdown-label"><?php echo $text_monthly_utilities; ?>:</span>
                        <span class="ml-calc-breakdown-value" id="monthly-expense-utilities">0 ГРН</span>
                    </div>
                    <div class="ml-calc-breakdown-subrow" id="monthly-expense-master-row" style="display: none;">
                        <span class="ml-calc-breakdown-label"><?php echo $text_monthly_master; ?>:</span>
                        <span class="ml-calc-breakdown-value" id="monthly-expense-master">0 ГРН</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="ml-calc-email">
            <div class="ml-calc-email__controls">
                <input type="email"
                       id="ml-calc-email-input"
                       placeholder="<?php echo $entry_email; ?>"
                       aria-label="<?php echo $entry_email; ?>">
                <button type="button" id="ml-calc-email-send"><?php echo $button_send_email; ?></button>
            </div>
            <div class="ml-calc-email__status" id="ml-calc-email-status"></div>
        </div>
    </div>

    <div class="ml-calc-container">
        <div class="ml-calc-row">
            <!-- Кількість клієнтів на день -->
            <div class="ml-calc-field">
                <label><?php echo $entry_clients_per_day; ?></label>
                <div class="ml-calc-value" id="clients-value"><?php echo $default_clients_per_day; ?></div>
                <input type="range"
                       id="clients-slider"
                       class="ml-calc-slider"
                       data-track="income"
                       min="1"
                       max="20"
                       value="<?php echo $default_clients_per_day; ?>"
                       step="1">
            </div>

            <!-- Вартість процедури -->
            <div class="ml-calc-field">
                <label><?php echo $entry_procedure_cost; ?></label>
                <div class="ml-calc-value" id="cost-value"><?php echo $default_procedure_cost; ?></div>
                <input type="range"
                       id="cost-slider"
                       class="ml-calc-slider"
                       data-track="income"
                       min="100"
                       max="6000"
                       value="<?php echo $default_procedure_cost; ?>"
                       step="50">
            </div>
        </div>

        <div class="ml-calc-row">
            <!-- Кількість робочих днів -->
            <div class="ml-calc-field">
                <label><?php echo $entry_working_days; ?></label>
                <div class="ml-calc-value" id="days-value"><?php echo $default_working_days; ?></div>
                <input type="range"
                       id="days-slider"
                       class="ml-calc-slider"
                       data-track="income"
                       min="1"
                       max="31"
                       value="<?php echo $default_working_days; ?>"
                       step="1">
            </div>

            <!-- Оренда приміщення -->
            <div class="ml-calc-field">
                <label><?php echo $entry_rent; ?></label>
                <div class="ml-calc-value" id="rent-value"><?php echo $default_rent; ?></div>
                <input type="range"
                       id="rent-slider"
                       class="ml-calc-slider"
                       data-track="expense"
                       min="0"
                       max="50000"
                       value="<?php echo $default_rent; ?>"
                       step="1000">
            </div>
        </div>

        <div class="ml-calc-row">
            <!-- Відсоток майстра -->
            <div class="ml-calc-field">
                <label><?php echo $entry_master_percent; ?></label>
                <div class="ml-calc-value" id="percent-value"><?php echo $default_master_percent; ?></div>
                <input type="range"
                       id="percent-slider"
                       class="ml-calc-slider"
                       data-track="expense"
                       min="0"
                       max="50"
                       value="<?php echo $default_master_percent; ?>"
                       step="1">
            </div>

            <!-- Комунальні послуги -->
            <div class="ml-calc-field">
                <label><?php echo $entry_utilities; ?></label>
                <div class="ml-calc-value" id="utilities-value"><?php echo $default_utilities; ?></div>
                <input type="range"
                       id="utilities-slider"
                       class="ml-calc-slider"
                       data-track="expense"
                       min="0"
                       max="10000"
                       value="<?php echo $default_utilities; ?>"
                       step="500">
            </div>
        </div>
    </div>

    <input type="hidden"
           id="ml-product-price"
           value="<?php echo $product_price; ?>"
           data-price-base="<?php echo $product_price_base; ?>"
           data-price-regular-base="<?php echo $product_price_regular_base; ?>"
           data-price-regular="<?php echo $product_price_regular; ?>"
           data-price-special-ratio="<?php echo $product_price_special_ratio; ?>"
           data-currency-rate="<?php echo $currency_rate; ?>"
           data-currency-code="<?php echo $currency_code; ?>">
    <input type="hidden" id="ml-product-id" value="<?php echo $product_id; ?>">
</div>
