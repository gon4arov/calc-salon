/**
 * ML Calculator - инициализация экземпляра калькулятора
 */
window.MLCalc = window.MLCalc || {};

window.MLCalc.init = function(wrapper) {
    'use strict';

    if (typeof jQuery === 'undefined') {
        console.error('[ML Calc] jQuery not loaded');
        return;
    }

    var $ = jQuery;

    // Генерируем уникальный ID для этого экземпляра калькулятора
    var calcId = 'mlcalc_' + Math.random().toString(36).substr(2, 9);

    // Добавляем уникальный ID к обёртке
    $(wrapper).attr('data-calc-id', calcId);
    var hasInternalOptions = $(wrapper).find('[data-ml-calc-option="1"]').length > 0;

    // Переменная для хранения текущего AJAX-запроса
    var currentAjaxRequest = null;

    // Функция поиска элементов только внутри этого калькулятора
    // Используем calcId для поиска актуальной обёртки в DOM
    function find(selector) {
        var $currentWrapper = $('[data-calc-id="' + calcId + '"]');
        if (!$currentWrapper.length) {
            console.error('[ML Calc] Wrapper not found for calcId:', calcId);
            return $();
        }
        return $currentWrapper.find(selector);
    }

    var $productPriceInput = find('#ml-product-price');
    var $productIdInput = find('#ml-product-id');
    var productId = $productIdInput.length ? parseInt($productIdInput.val(), 10) : 0;

    var basePriceUsd = 0;
    var regularBasePriceUsd = 0;
    var currencyRate = 1;
    var currencyCode = '';
    var specialToRegularRatio = 1;

    if ($productPriceInput.length) {
        var dataBasePrice = parseFloat($productPriceInput.data('priceBase'));
        if (!isNaN(dataBasePrice)) {
            basePriceUsd = dataBasePrice;
        }

        var dataRegularBasePrice = parseFloat($productPriceInput.data('priceRegularBase'));
        if (!isNaN(dataRegularBasePrice) && dataRegularBasePrice > 0) {
            regularBasePriceUsd = dataRegularBasePrice;
        }

        var dataCurrencyRate = parseFloat($productPriceInput.data('currencyRate'));
        if (!isNaN(dataCurrencyRate) && dataCurrencyRate > 0) {
            currencyRate = dataCurrencyRate;
        }

        var dataCurrencyCode = $productPriceInput.data('currencyCode');
        if (typeof dataCurrencyCode === 'string') {
            currencyCode = dataCurrencyCode;
        }

        if (!basePriceUsd && currencyRate) {
            var currentValue = parseFloat($productPriceInput.val());
            if (!isNaN(currentValue)) {
                basePriceUsd = currentValue / currencyRate;
            }
        }

        if (!regularBasePriceUsd) {
            regularBasePriceUsd = basePriceUsd;
        }

        if (!basePriceUsd && regularBasePriceUsd) {
            basePriceUsd = regularBasePriceUsd;
        }

        if (regularBasePriceUsd > 0 && basePriceUsd > 0) {
            specialToRegularRatio = basePriceUsd / regularBasePriceUsd;
            if (!isFinite(specialToRegularRatio) || specialToRegularRatio <= 0) {
                specialToRegularRatio = 1;
            }
        }
    }

    function formatCurrencyValue(value) {
        if (!value || !currencyCode) {
            return Math.round(value).toLocaleString();
        }

        try {
            return new Intl.NumberFormat(undefined, {
                style: 'currency',
                currency: currencyCode,
                maximumFractionDigits: 0
            }).format(value);
        } catch (e) {
            return Math.round(value).toLocaleString();
        }
    }

    function getOptionScope() {
        if (!productId) {
            return null;
        }

        // Если есть внутренние опции калькулятора, возвращаем сам калькулятор
        if (hasInternalOptions) {
            var $currentWrapper = $('[data-calc-id="' + calcId + '"]');
            if ($currentWrapper.length) {
                return $currentWrapper;
            }
            return null;
        }

        var $productIdFields = $('[name="product_id"]').filter(function() {
            var val = parseInt($(this).val(), 10);
            if (isNaN(val) || val !== productId) {
                return false;
            }
            return $(this).closest('.ml-calc-wrapper').length === 0;
        });

        if ($productIdFields.length) {
            var $form = $productIdFields.first().closest('form');
            if ($form.length) {
                return $form;
            }

            var $productBlock = $productIdFields.first().closest('#product');
            if ($productBlock.length) {
                return $productBlock;
            }

            return $productIdFields.first().parent();
        }

        var $currentWrapper = $('[data-calc-id="' + calcId + '"]');
        if ($currentWrapper.length) {
            return $currentWrapper;
        }

        return null;
    }

    function collectOptionAdjustments($scope) {
        var adjustments = [];

        if (!$scope || !$scope.length) {
            return adjustments;
        }

        $scope.find('input[type="radio"][name^="option["]:checked, input[type="checkbox"][name^="option["]:checked').each(function() {
            var $input = $(this);
            var insideCalc = $input.closest('.ml-calc-wrapper').length > 0;
            if (insideCalc) {
                if (!$input.data('mlCalcOption')) {
                    return;
                }
            } else if (hasInternalOptions) {
                return;
            }

            var priceValue = parseFloat($input.attr('data-price'));
            if (isNaN(priceValue)) {
                return;
            }

            var prefix = ($input.attr('data-prefix') || '+').trim();
            if (!prefix) {
                prefix = '+';
            }

            adjustments.push({
                prefix: prefix,
                price: priceValue
            });
        });

        $scope.find('select[name^="option["]').each(function() {
            var $select = $(this);
            var insideCalc = $select.closest('.ml-calc-wrapper').length > 0;
            if (insideCalc) {
                if (!$select.data('mlCalcOption')) {
                    return;
                }
            } else if (hasInternalOptions) {
                return;
            }

            $select.find('option:selected').each(function() {
                var $option = $(this);
                var priceValue = parseFloat($option.attr('data-price'));
                if (isNaN(priceValue)) {
                    return;
                }

                var prefix = ($option.attr('data-prefix') || '+').trim();
                if (!prefix) {
                    prefix = '+';
                }

                adjustments.push({
                    prefix: prefix,
                    price: priceValue
                });
            });
        });

        return adjustments;
    }

    function calculatePriceWithOptions(basePrice, adjustments) {
        var price = basePrice;
        var overridePrice = null;

        for (var i = 0; i < adjustments.length; i++) {
            var adjustment = adjustments[i];
            if (typeof adjustment.price !== 'number' || isNaN(adjustment.price)) {
                continue;
            }

            switch (adjustment.prefix) {
                case '=':
                    overridePrice = adjustment.price;
                    break;
                case '-':
                    price -= adjustment.price;
                    break;
                case '+':
                default:
                    price += adjustment.price;
                    break;
            }
        }

        if (overridePrice !== null) {
            price = overridePrice;
        }

        if (price < 0) {
            price = 0;
        }

        return price;
    }

    function updateProductPriceField(value, regularValue) {
        if (!$productPriceInput.length || isNaN(value)) {
            return;
        }

        $productPriceInput.val(value);

        // Обновляем атрибут data-price-regular если передана обычная цена
        if (!isNaN(regularValue) && regularValue > 0) {
            $productPriceInput.attr('data-price-regular', regularValue);
        }
    }

    function updateSummaryPriceDisplay(specialValue, regularValue) {
        var $currentPrice = find('.ml-calc-result__price-current');
        if (!isNaN(specialValue)) {
            var formattedCurrent = formatCurrencyValue(specialValue);
            if ($currentPrice.length) {
                $currentPrice.text(formattedCurrent);
            } else {
                var $regularPriceOnly = find('.ml-calc-result__price-regular');
                if ($regularPriceOnly.length) {
                    $regularPriceOnly.text(formattedCurrent);
                }
            }
        }

        if (!isNaN(regularValue)) {
            var $oldPrice = find('.ml-calc-result__price-old');
            if ($oldPrice.length) {
                if (Math.abs(regularValue - specialValue) > 1) {
                    $oldPrice.text(formatCurrencyValue(regularValue));
                    $oldPrice.show();
                } else {
                    $oldPrice.hide();
                }
            }
        }
    }

    function setupOptionPriceSync() {
        if (!productId || !$productPriceInput.length) {
            return false;
        }

        var $optionScope = getOptionScope();
        if (!$optionScope || !$optionScope.length) {
            return false;
        }

        function recomputePrice() {
            var adjustments = collectOptionAdjustments($optionScope);
            var regularBaseUsdToUse = regularBasePriceUsd || basePriceUsd;
            var baseSpecialUsd = basePriceUsd;
            if (!baseSpecialUsd && specialToRegularRatio > 0) {
                baseSpecialUsd = regularBaseUsdToUse * specialToRegularRatio;
            }
            if (!baseSpecialUsd) {
                baseSpecialUsd = regularBaseUsdToUse;
            }
            var calculatedSpecialUsd = calculatePriceWithOptions(baseSpecialUsd, adjustments);

            var calculatedRegularUsd;
            if (specialToRegularRatio > 0 && specialToRegularRatio !== 1) {
                calculatedRegularUsd = calculatedSpecialUsd / specialToRegularRatio;
            } else {
                calculatedRegularUsd = calculatePriceWithOptions(regularBaseUsdToUse, adjustments);
            }

            var convertedSpecialPrice = Math.round(calculatedSpecialUsd * currencyRate);
            var convertedRegularPrice = Math.round(calculatedRegularUsd * currencyRate);

            updateProductPriceField(convertedSpecialPrice, convertedRegularPrice);
            updateSummaryPriceDisplay(convertedSpecialPrice, convertedRegularPrice);
            calculatePayback();
        }

        $optionScope.on('change.mlCalc' + calcId, 'input[name^="option["], select[name^="option["], textarea[name^="option["]', function() {
            recomputePrice();
        });

        // Дополнительно отслеживаем клик для радиокнопок
        $optionScope.on('click.mlCalc' + calcId, 'input[type="radio"][name^="option["]', function() {
            setTimeout(recomputePrice, 50);
        });

        recomputePrice();
        return true;
    }

    function updateSliderProgress(element) {
        if (!element) {
            return;
        }

        var min = parseFloat(element.min);
        var max = parseFloat(element.max);
        var value = parseFloat(element.value);

        if (isNaN(min)) {
            min = 0;
        }
        if (isNaN(max)) {
            max = 100;
        }
        if (isNaN(value)) {
            value = min;
        }

        var percent = max <= min ? 0 : ((value - min) / (max - min)) * 100;
        percent = Math.min(Math.max(percent, 0), 100);

        element.style.setProperty('--progress', percent + '%');
    }

    // Обновление значений слайдеров при движении
    find('#clients-slider').on('input', function() {
        find('#clients-value').text($(this).val());
        updateSliderProgress(this);
    });

    find('#cost-slider').on('input', function() {
        find('#cost-value').text($(this).val());
        updateSliderProgress(this);
    });

    find('#days-slider').on('input', function() {
        find('#days-value').text($(this).val());
        updateSliderProgress(this);
    });

    find('#rent-slider').on('input', function() {
        find('#rent-value').text($(this).val());
        updateSliderProgress(this);
    });

    find('#utilities-slider').on('input', function() {
        find('#utilities-value').text($(this).val());
        updateSliderProgress(this);
    });

    find('#percent-slider').on('input', function() {
        find('#percent-value').text($(this).val());
        updateSliderProgress(this);
    });

    // Инициализация прогресса для всех слайдеров
    find('.ml-calc-slider').each(function() {
        updateSliderProgress(this);
    });

    // Отслеживаем предыдущее значение параметров для сохранения динамики изменений
    var lastValues = {
        clients_per_day: parseInt(find('#clients-slider').val(), 10) || 0,
        procedure_cost: parseFloat(find('#cost-slider').val()) || 0,
        working_days: parseInt(find('#days-slider').val(), 10) || 0,
        rent: parseFloat(find('#rent-slider').val()) || 0,
        utilities: find('#utilities-slider').length ? parseFloat(find('#utilities-slider').val()) || 0 : 0,
        master_percent: parseFloat(find('#percent-slider').val()) || 0
    };

    function getNewValueForParameter(paramName, values) {
        switch (paramName) {
            case 'clients_per_day':
                return values.clientsPerDay;
            case 'procedure_cost':
                return values.procedureCost;
            case 'working_days':
                return values.workingDays;
            case 'rent':
                return values.rent;
            case 'utilities':
                return values.utilities;
            case 'master_percent':
                return values.masterPercent;
            default:
                return null;
        }
    }

    // Функция для сохранения статистики изменения параметра
    function saveStatistics(changedParameter, explicitNewValue) {
        if (!productId) {
            return;
        }

        // Собираем данные расчета
        var productPrice = parseFloat(find('#ml-product-price').val());
        var productPriceRegular = parseFloat(find('#ml-product-price').attr('data-price-regular')) || 0;
        var clientsPerDay = parseInt(find('#clients-slider').val());
        var procedureCost = parseFloat(find('#cost-slider').val());
        var workingDays = parseInt(find('#days-slider').val());
        var rent = parseFloat(find('#rent-slider').val());
        var utilitiesSlider = find('#utilities-slider');
        var utilities = utilitiesSlider.length ? parseFloat(utilitiesSlider.val()) : 0;
        var masterPercent = parseFloat(find('#percent-slider').val());

        var newValue = explicitNewValue;
        if (typeof newValue === 'undefined') {
            newValue = getNewValueForParameter(changedParameter, {
                clientsPerDay: clientsPerDay,
                procedureCost: procedureCost,
                workingDays: workingDays,
                rent: rent,
                utilities: utilities,
                masterPercent: masterPercent
            });
            if (typeof newValue === 'number' && isNaN(newValue)) {
                newValue = null;
            }
        }
        var oldValue = lastValues.hasOwnProperty(changedParameter) ? lastValues[changedParameter] : null;

        $.ajax({
            url: 'index.php?route=extension/module/ml_calc/saveStatistics',
            type: 'post',
            data: {
                product_id: productId,
                changed_parameter: changedParameter,
                product_price: productPrice,
                product_price_regular: productPriceRegular,
                clients_per_day: clientsPerDay,
                procedure_cost: procedureCost,
                working_days: workingDays,
                rent: rent,
                utilities: utilities,
                master_percent: masterPercent,
                old_value: oldValue,
                new_value: newValue
            },
            dataType: 'json',
            success: function(json) {
                if (newValue !== null && newValue !== undefined) {
                    lastValues[changedParameter] = newValue;
                }
            },
            error: function(xhr, status, error) {
                console.error('[ML Calc] Error saving statistics:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
            }
        });
    }

    // Отслеживаем изменения каждого слайдера отдельно
    find('#clients-slider').on('change', function() {
        calculatePayback();
        saveStatistics('clients_per_day', parseInt($(this).val(), 10));
    });

    find('#cost-slider').on('change', function() {
        calculatePayback();
        saveStatistics('procedure_cost', parseFloat($(this).val()));
    });

    find('#days-slider').on('change', function() {
        calculatePayback();
        saveStatistics('working_days', parseInt($(this).val(), 10));
    });

    find('#rent-slider').on('change', function() {
        calculatePayback();
        saveStatistics('rent', parseFloat($(this).val()));
    });

    find('#utilities-slider').on('change', function() {
        calculatePayback();
        saveStatistics('utilities', parseFloat($(this).val()));
    });

    find('#percent-slider').on('change', function() {
        calculatePayback();
        saveStatistics('master_percent', parseFloat($(this).val()));
    });

    // Функция генерации формул для тултипов
    function updateResultTooltips(json, clients, cost, days, rent, utilities, percent, price) {
        if (!json.payback_days_raw) {
            return; // Нет данных для тултипов
        }

        // Определяем язык из HTML или URL
        var lang = document.documentElement.lang || 'en';
        if (lang.indexOf('uk') === 0 || lang.indexOf('ua') === 0 || window.location.pathname.indexOf('/ua/') === 0) {
            lang = 'uk';
        } else if (lang.indexOf('ru') === 0) {
            lang = 'ru';
        } else {
            lang = 'en';
        }

        // Переводы по умолчанию для разных языков
        var translations = {
            uk: {
                dailyIncome: 'Денний дохід',
                monthlyIncome: 'Місячний дохід',
                masterExpenses: 'Витрати майстра',
                totalExpenses: 'Загальні витрати',
                netProfit: 'Чистий прибуток',
                payback: 'Окупність',
                days: 'днів',
                monthsShort: 'міс',
                monthlyProfit: 'Місячний прибуток',
                annualProfit: 'Річний прибуток',
                regularPrice: 'Звичайна ціна',
                perMonth: '/міс'
            },
            ru: {
                dailyIncome: 'Дневной доход',
                monthlyIncome: 'Месячный доход',
                masterExpenses: 'Расходы мастера',
                totalExpenses: 'Общие расходы',
                netProfit: 'Чистая прибыль',
                payback: 'Окупаемость',
                days: 'дней',
                monthsShort: 'мес',
                monthlyProfit: 'Месячная прибыль',
                annualProfit: 'Годовая прибыль',
                regularPrice: 'Обычная цена',
                perMonth: '/мес'
            },
            en: {
                dailyIncome: 'Daily income',
                monthlyIncome: 'Monthly income',
                masterExpenses: 'Master expenses',
                totalExpenses: 'Total expenses',
                netProfit: 'Net profit',
                payback: 'Payback',
                days: 'days',
                monthsShort: 'mo',
                monthlyProfit: 'Monthly profit',
                annualProfit: 'Annual profit',
                regularPrice: 'Regular price',
                perMonth: '/mo'
            }
        };

        // Получаем переводы: сначала из PHP, затем из встроенных переводов
        var labels = window.mlCalcFormulaLabels || translations[lang] || translations.en;

        // Используем данные из JSON ответа сервера
        var dailyIncome = json.daily_income_raw || 0;
        var monthlyIncome = json.monthly_income_raw || 0;
        var masterExpense = json.monthly_expense_master_raw || 0;
        var totalExpenses = json.monthly_expenses_total_raw || 0;
        var monthlyProfit = json.monthly_profit_raw || 0;
        var paybackDays = json.payback_days_raw;
        var paybackMonths = Math.round((paybackDays / 30) * 10) / 10;

        // Формула окупаемости
        var paybackFormula =
            labels.dailyIncome + ' = ' + clients + ' × ' + cost + ' = ' + dailyIncome.toFixed(0) + '\n' +
            labels.monthlyIncome + ' = ' + dailyIncome.toFixed(0) + ' × ' + days + ' = ' + monthlyIncome.toFixed(0) + '\n' +
            labels.masterExpenses + ' = ' + monthlyIncome.toFixed(0) + ' × ' + percent + '% = ' + masterExpense.toFixed(0) + '\n' +
            labels.totalExpenses + ' = ' + rent + ' + ' + utilities + ' + ' + masterExpense.toFixed(0) + ' = ' + totalExpenses.toFixed(0) + '\n' +
            labels.netProfit + ' = ' + monthlyIncome.toFixed(0) + ' - ' + totalExpenses.toFixed(0) + ' = ' + monthlyProfit.toFixed(0) + '\n' +
            labels.payback + ' = ' + price.toFixed(0) + ' ÷ ' + monthlyProfit.toFixed(0) + ' = ' + paybackDays.toFixed(1) + ' ' + labels.days + ' (' + paybackMonths.toFixed(1) + ' ' + labels.monthsShort + ')';

        find('#payback-result').attr('data-formula', paybackFormula);
        find('#payback-result').addClass('ml-calc-result-tooltip');

        // Формула годовой прибыли
        var annualProfit = json.annual_profit_raw || 0;
        var profitFormula =
            labels.monthlyProfit + ' = ' + monthlyProfit.toFixed(0) + '\n' +
            labels.annualProfit + ' = ' + monthlyProfit.toFixed(0) + ' × 12 = ' + annualProfit.toFixed(0);

        find('#profit-result').attr('data-formula', profitFormula);
        find('#profit-result').addClass('ml-calc-result-tooltip');

        // Формула для окупаемости без акции (если есть)
        if (json.payback_days_regular_raw && json.price_regular_raw) {
            var priceRegular = json.price_regular_raw;
            var paybackDaysRegular = json.payback_days_regular_raw;
            var paybackMonthsRegular = Math.round((paybackDaysRegular / 30) * 10) / 10;

            var paybackRegularFormula =
                labels.regularPrice + ' = ' + priceRegular.toFixed(0) + '\n' +
                labels.netProfit + ' = ' + monthlyProfit.toFixed(0) + ' ' + labels.perMonth + '\n' +
                labels.payback + ' = ' + priceRegular.toFixed(0) + ' ÷ ' + monthlyProfit.toFixed(0) + ' = ' + paybackDaysRegular.toFixed(1) + ' ' + labels.days + ' (' + paybackMonthsRegular.toFixed(1) + ' ' + labels.monthsShort + ')';

            find('#payback-result-regular').attr('data-formula', paybackRegularFormula);
            find('#payback-result-regular').addClass('ml-calc-result-tooltip');
        }
    }

    // Функция расчета
    function calculatePayback() {
        // Отменяем предыдущий запрос, если он еще выполняется
        if (currentAjaxRequest) {
            currentAjaxRequest.abort();
            currentAjaxRequest = null;
        }

        var $resultBlock = find('.ml-calc-result');
        if ($resultBlock.length) {
            $resultBlock.addClass('ml-calc-result--loading');
        }

        var productPrice = parseFloat(find('#ml-product-price').val());
        var productPriceRegular = parseFloat(find('#ml-product-price').attr('data-price-regular')) || 0;
        var clientsPerDay = parseInt(find('#clients-slider').val());
        var procedureCost = parseFloat(find('#cost-slider').val());
        var workingDays = parseInt(find('#days-slider').val());
        var rent = parseFloat(find('#rent-slider').val());
        var utilitiesSlider = find('#utilities-slider');
        var utilities = utilitiesSlider.length ? parseFloat(utilitiesSlider.val()) : 0;

        if (isNaN(utilities)) {
            utilities = 0;
        }
        var masterPercent = parseFloat(find('#percent-slider').val());

        currentAjaxRequest = $.ajax({
            url: 'index.php?route=extension/module/ml_calc/calculate',
            type: 'post',
            data: {
                product_price: productPrice,
                product_price_regular: productPriceRegular,
                clients_per_day: clientsPerDay,
                procedure_cost: procedureCost,
                working_days: workingDays,
                rent: rent,
                utilities: utilities,
                master_percent: masterPercent
            },
            dataType: 'json',
            success: function(json) {
                try {
                    if (!json || typeof json !== 'object') {
                        throw new Error('Invalid response format');
                    }

                    if (json.success) {
                        find('#payback-result').text(json.payback_text);
                        find('#profit-result').text(json.annual_profit + ' ГРН');
                        find('#monthly-profit-result').text(json.monthly_profit + ' ГРН');
                        find('#monthly-expenses-result').text(json.monthly_expenses_total + ' ГРН');
                        find('#monthly-expense-rent').text(json.monthly_expense_rent + ' ГРН');
                        find('#monthly-expense-utilities').text(json.monthly_expense_utilities + ' ГРН');
                        find('#monthly-expense-master').text(json.monthly_expense_master + ' ГРН');

                    // Проверяем настройку показа окупаемости без акции
                    var showRegularPayback = wrapper.getAttribute('data-show-regular-payback') === '1';
                    if (showRegularPayback && json.has_regular_price && json.payback_text_regular) {
                        find('#payback-result-regular').text(json.payback_text_regular);
                        find('#payback-regular-row').css('display', 'flex');
                    } else {
                        find('#payback-regular-row').css('display', 'none');
                    }

                    if (json.monthly_expense_rent_raw > 0) {
                        find('#monthly-expense-rent-row').css('display', 'flex');
                    } else {
                        find('#monthly-expense-rent-row').css('display', 'none');
                    }

                    if (json.monthly_expense_utilities_raw > 0) {
                        find('#monthly-expense-utilities-row').css('display', 'flex');
                    } else {
                        find('#monthly-expense-utilities-row').css('display', 'none');
                    }

                    if (json.monthly_expense_master_raw > 0) {
                        find('#monthly-expense-master-row').css('display', 'flex');
                    } else {
                        find('#monthly-expense-master-row').css('display', 'none');
                    }

                    find('#ml-calc-breakdown').slideDown();
                    find('#ml-calc-breakdown-separator').css('display', 'block');

                    if (json.warning) {
                        find('#ml-calc-warning').text(json.warning).slideDown();
                    } else {
                        find('#ml-calc-warning').slideUp(function() {
                            $(this).text('');
                        });
                    }

                    find('#ml-calc-result').slideDown();

                    // Добавляем тултипы с формулами, если включено
                    var showResultTooltips = wrapper.getAttribute('data-show-result-tooltips') === '1';
                    if (showResultTooltips && json.payback_days_raw) {
                        updateResultTooltips(json, clientsPerDay, procedureCost, workingDays, rent, utilities, masterPercent, productPrice);
                    }
                    } else {
                        var errorMsg = json.error || 'Произошла ошибка при расчете';
                        console.error('[ML Calc] Calculation error:', errorMsg);
                        alert(errorMsg);
                    }
                } catch (error) {
                    console.error('[ML Calc] Error processing response:', error);
                    alert('Ошибка обработки результатов расчета');
                } finally {
                    if ($resultBlock.length) {
                        setTimeout(function() {
                            $resultBlock.removeClass('ml-calc-result--loading');
                        }, 400);
                    }
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                if ($resultBlock.length) {
                    setTimeout(function() {
                        $resultBlock.removeClass('ml-calc-result--loading');
                    }, 400);
                }

                // Игнорируем отмененные запросы
                if (xhr && (xhr.statusText === 'abort' || xhr.readyState === 0) || thrownError === 'abort') {
                    return;
                }

                // Логируем ошибку
                console.error('[ML Calc] AJAX error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: thrownError,
                    response: xhr.responseText
                });

                // Показываем понятное сообщение пользователю
                var userMsg = 'Не удалось выполнить расчет. Пожалуйста, попробуйте еще раз.';
                if (xhr.status === 0) {
                    userMsg = 'Нет подключения к серверу. Проверьте интернет-соединение.';
                } else if (xhr.status === 404) {
                    userMsg = 'Сервис расчета не найден. Обратитесь к администратору.';
                } else if (xhr.status === 500) {
                    userMsg = 'Ошибка сервера. Попробуйте позже.';
                }
                alert(userMsg);
            }
        });
    }

    var optionSyncConfigured = setupOptionPriceSync();

    // Автоматический расчет при загрузке
    if (!optionSyncConfigured) {
        calculatePayback();
    }
};

// Автоматическая инициализация для калькуляторов на странице товара
jQuery(document).ready(function() {
    jQuery('.ml-calc-wrapper').each(function() {
        window.MLCalc.init(this);
    });

    // Обработчик для кнопки открытия вкладки калькулятора
    jQuery(document).on('click', '#ml-calc-open-tab', function(e) {
        e.preventDefault();

        // Находим вкладку калькулятора и активируем её
        var $calcTab = jQuery('a[href="#tab-ml-calc"]');
        if ($calcTab.length) {
            $calcTab.tab('show');

            // Прокручиваем к вкладкам
            jQuery('html, body').animate({
                scrollTop: $calcTab.closest('.nav-tabs').offset().top - 100
            }, 500);
        }
    });
});
