/**
 * ML Salon Calculator v2.7.0
 * Калькулятор окупаемости салона с поддержкой множественного выбора товаров
 */

(function() {
    'use strict';

    if (typeof jQuery === 'undefined') {
        console.error('[ML Salon Calc] jQuery not loaded');
        return;
    }

    var $ = jQuery;

    // Корзина товаров (LocalStorage)
    var cart = [];

    // Доступные товары
    var availableProducts = [];

    // Флаг для предотвращения повторных AJAX запросов
    var isCalculating = false;

    // Инициализация
    $(document).ready(function() {
        init();
    });

    function init() {
        loadAvailableProducts();
        loadCartFromLocalStorage();
        setupEventListeners();
        loadSavedCalculation();
        updateSliders();
    }

    // Загрузка списка доступных товаров
    function loadAvailableProducts() {
        console.log('[ML Salon Calc] Loading products...');
        $.ajax({
            url: 'index.php?route=extension/module/ml_calc_salon/getProducts',
            type: 'get',
            dataType: 'json',
            success: function(json) {
                console.log('[ML Salon Calc] Products loaded:', json);
                if (json.products) {
                    availableProducts = json.products;
                    console.log('[ML Salon Calc] Available products count:', availableProducts.length);
                    populateProductSelector();
                }
            },
            error: function(xhr, status, error) {
                console.error('[ML Salon Calc] Failed to load products:', error);
                console.error('[ML Salon Calc] Status:', status);
                console.error('[ML Salon Calc] Response:', xhr.responseText);
            }
        });
    }

    // Заполнение селектора товаров
    function populateProductSelector() {
        console.log('[ML Salon Calc] Populating product selector...');
        var $selector = $('#product-selector');

        if ($selector.length === 0) {
            console.error('[ML Salon Calc] Product selector #product-selector not found!');
            return;
        }

        $selector.empty();
        $selector.append('<option value="">-- Выберите товар --</option>');

        if (availableProducts.length === 0) {
            $selector.append('<option value="" disabled>Нет доступных товаров (установите JAN=1)</option>');
            console.warn('[ML Salon Calc] No products available. Make sure products have JAN="1"');
            return;
        }

        $.each(availableProducts, function(index, product) {
            // Проверяем, не добавлен ли уже этот товар
            var alreadyInCart = cart.some(function(item) {
                return item.product_id === product.product_id;
            });

            if (!alreadyInCart) {
                $selector.append('<option value="' + product.product_id + '" data-price="' + product.price + '" data-name="' + product.name + '" data-image="' + product.image + '">' + product.name + ' - ' + product.price_formatted + '</option>');
            }
        });

        console.log('[ML Salon Calc] Selector populated with', $selector.find('option').length - 1, 'products');
    }

    // Загрузка корзины из LocalStorage
    function loadCartFromLocalStorage() {
        var savedCart = localStorage.getItem('ml_salon_calc_cart');
        if (savedCart) {
            try {
                cart = JSON.parse(savedCart);
                renderCart();
            } catch (e) {
                console.error('[ML Salon Calc] Failed to parse cart from LocalStorage');
                cart = [];
            }
        }
    }

    // Сохранение корзины в LocalStorage
    function saveCartToLocalStorage() {
        localStorage.setItem('ml_salon_calc_cart', JSON.stringify(cart));
    }

    // Настройка обработчиков событий
    function setupEventListeners() {
        console.log('[ML Salon Calc] Setting up event listeners...');

        // Добавление товара из селектора
        $('#product-selector').on('change', function() {
            console.log('[ML Salon Calc] Product selector changed');
            var productId = parseInt($(this).val());
            console.log('[ML Salon Calc] Selected product ID:', productId);
            if (productId) {
                addProductToCart(productId);
                $(this).val('');
            }
        });

        // Обновление значений слайдеров
        $('#working-days-slider').on('input', function() {
            $('#working-days-value').text($(this).val());
            updateSliderProgress(this);
        });

        $('#rent-slider').on('input', function() {
            $('#rent-value').text($(this).val());
            updateSliderProgress(this);
        });

        $('#utilities-slider').on('input', function() {
            $('#utilities-value').text($(this).val());
            updateSliderProgress(this);
        });

        // Расчет
        $('#calculate-btn').on('click', function() {
            calculate();
        });

        // Сохранение расчета
        $('#save-btn').on('click', function() {
            saveCalculation();
        });
    }

    // Добавление товара в корзину
    function addProductToCart(productId) {
        console.log('[ML Salon Calc] Adding product to cart:', productId);

        var product = availableProducts.find(function(p) {
            return p.product_id === productId;
        });

        if (!product) {
            console.error('[ML Salon Calc] Product not found:', productId);
            return;
        }

        console.log('[ML Salon Calc] Product found:', product);

        var defaultClientsPerDay = parseInt($('#default-clients-per-day').val()) || 7;
        var defaultProcedureCost = parseInt($('#default-procedure-cost').val()) || 1000;
        var defaultMasterPercent = parseInt($('#default-master-percent').val()) || 15;

        var cartItem = {
            product_id: product.product_id,
            name: product.name,
            price: product.price,
            price_formatted: product.price_formatted,
            image: product.image,
            clients_per_day: defaultClientsPerDay,
            procedure_cost: defaultProcedureCost,
            master_percent: defaultMasterPercent
        };

        cart.push(cartItem);
        saveCartToLocalStorage();
        renderCart();
        populateProductSelector();
    }

    // Удаление товара из корзины
    function removeProductFromCart(index) {
        cart.splice(index, 1);
        saveCartToLocalStorage();
        renderCart();
        populateProductSelector();
        $('#results-block').hide();
    }

    // Отрисовка корзины
    function renderCart() {
        var $list = $('#salon-products-list');
        $list.empty();

        if (cart.length === 0) {
            $list.html('<p class="no-products">Нет товаров в корзине</p>');
            return;
        }

        $.each(cart, function(index, item) {
            var html = '<div class="salon-product-card" data-index="' + index + '">';
            html += '<div class="product-image"><img src="' + item.image + '" alt="' + item.name + '"></div>';
            html += '<div class="product-info">';
            html += '<div class="product-name">' + item.name + '</div>';
            html += '<div class="product-price">' + item.price_formatted + '</div>';
            html += '</div>';
            html += '<div class="product-params">';
            html += '<div class="param-field">';
            html += '<label>Клиенты/день</label>';
            html += '<div class="param-value" id="clients-value-' + index + '">' + item.clients_per_day + '</div>';
            html += '<input type="range" class="ml-calc-slider" data-param="clients_per_day" data-index="' + index + '" min="1" max="50" value="' + item.clients_per_day + '" step="1">';
            html += '</div>';
            html += '<div class="param-field">';
            html += '<label>Стоимость процедуры</label>';
            html += '<div class="param-value" id="cost-value-' + index + '">' + item.procedure_cost + '</div>';
            html += '<input type="range" class="ml-calc-slider" data-param="procedure_cost" data-index="' + index + '" min="100" max="10000" value="' + item.procedure_cost + '" step="50">';
            html += '</div>';
            html += '<div class="param-field">';
            html += '<label>Процент мастера (%)</label>';
            html += '<div class="param-value" id="percent-value-' + index + '">' + item.master_percent + '</div>';
            html += '<input type="range" class="ml-calc-slider" data-param="master_percent" data-index="' + index + '" min="0" max="100" value="' + item.master_percent + '" step="1">';
            html += '</div>';
            html += '</div>';
            html += '<button class="btn btn-danger btn-sm remove-product-btn" data-index="' + index + '">Удалить</button>';
            html += '</div>';

            $list.append(html);
        });

        // Обработчики для параметров товаров
        $('.salon-product-card input[type="range"]').on('input', function() {
            var index = $(this).data('index');
            var param = $(this).data('param');
            var value = parseInt($(this).val());

            cart[index][param] = value;
            $('#' + param.replace('_', '-') + '-value-' + index).text(value);
            saveCartToLocalStorage();
            updateSliderProgress(this);
        });

        // Обработчик удаления
        $('.remove-product-btn').on('click', function() {
            var index = $(this).data('index');
            removeProductFromCart(index);
        });

        // Инициализация прогресса слайдеров
        $('.ml-calc-slider').each(function() {
            updateSliderProgress(this);
        });
    }

    // Обновление прогресса слайдера
    function updateSliderProgress(element) {
        if (!element) return;

        var min = parseFloat(element.min) || 0;
        var max = parseFloat(element.max) || 100;
        var value = parseFloat(element.value) || min;

        var percent = max <= min ? 0 : ((value - min) / (max - min)) * 100;
        percent = Math.min(Math.max(percent, 0), 100);

        element.style.setProperty('--progress', percent + '%');
    }

    // Инициализация слайдеров
    function updateSliders() {
        $('.ml-calc-slider').each(function() {
            updateSliderProgress(this);
        });
    }

    // Расчет
    function calculate() {
        if (cart.length === 0) {
            alert('Добавьте хотя бы один товар для расчета');
            return;
        }

        if (isCalculating) {
            return;
        }

        isCalculating = true;
        $('#calculate-btn').prop('disabled', true).text('Расчет...');

        var workingDays = parseInt($('#working-days-slider').val());
        var rent = parseFloat($('#rent-slider').val());
        var utilities = parseFloat($('#utilities-slider').val());

        $.ajax({
            url: 'index.php?route=extension/module/ml_calc_salon/calculateMultiple',
            type: 'post',
            data: {
                products: cart,
                working_days: workingDays,
                rent: rent,
                utilities: utilities
            },
            dataType: 'json',
            success: function(json) {
                if (json.success) {
                    displayResults(json);
                    $('#save-btn').show();
                } else {
                    alert(json.error || 'Ошибка расчета');
                }
            },
            error: function() {
                alert('Ошибка при выполнении расчета');
            },
            complete: function() {
                isCalculating = false;
                $('#calculate-btn').prop('disabled', false).text('Рассчитать');
            }
        });
    }

    // Отображение результатов
    function displayResults(json) {
        $('#total-investment').text(json.total_investment + ' ГРН');
        $('#total-payback').text(json.total_payback_text);
        $('#total-profit').text(json.total_profit + ' ГРН');

        var $productResults = $('#product-results');
        $productResults.empty();

        if (json.product_results && json.product_results.length > 0) {
            $.each(json.product_results, function(index, result) {
                var html = '<div class="product-result-card">';
                html += '<h5>' + result.name + '</h5>';
                html += '<div class="result-row"><span>Выручка:</span><span>' + result.revenue + ' ГРН</span></div>';
                html += '<div class="result-row"><span>Расходы:</span><span>' + result.expenses + ' ГРН</span></div>';
                html += '<div class="result-row"><span>Прибыль:</span><span>' + result.profit + ' ГРН</span></div>';
                html += '<div class="result-row highlight"><span>Окупаемость:</span><span>' + result.payback_text + '</span></div>';
                html += '</div>';

                $productResults.append(html);
            });
        }

        $('#results-block').slideDown();
    }

    // Сохранение расчета
    function saveCalculation() {
        var calculationData = {
            cart: cart,
            working_days: parseInt($('#working-days-slider').val()),
            rent: parseFloat($('#rent-slider').val()),
            utilities: parseFloat($('#utilities-slider').val())
        };

        $.ajax({
            url: 'index.php?route=extension/module/ml_calc_salon/saveCalculation',
            type: 'post',
            data: {
                calculation_data: JSON.stringify(calculationData)
            },
            dataType: 'json',
            success: function(json) {
                if (json.success) {
                    var shareUrl = json.url;
                    prompt('Ссылка на расчет (скопируйте):', shareUrl);
                } else {
                    alert(json.error || 'Ошибка при сохранении');
                }
            },
            error: function() {
                alert('Ошибка при сохранении расчета');
            }
        });
    }

    // Загрузка сохраненного расчета
    function loadSavedCalculation() {
        var savedData = $('#saved-calculation').val();
        if (!savedData) {
            return;
        }

        try {
            var data = JSON.parse(savedData);

            if (data.cart && Array.isArray(data.cart)) {
                cart = data.cart;
                saveCartToLocalStorage();
                renderCart();
            }

            if (data.working_days) {
                $('#working-days-slider').val(data.working_days);
                $('#working-days-value').text(data.working_days);
            }

            if (data.rent) {
                $('#rent-slider').val(data.rent);
                $('#rent-value').text(data.rent);
            }

            if (data.utilities) {
                $('#utilities-slider').val(data.utilities);
                $('#utilities-value').text(data.utilities);
            }

            updateSliders();

            // Автоматически выполняем расчет
            setTimeout(function() {
                calculate();
            }, 500);

        } catch (e) {
            console.error('[ML Salon Calc] Failed to load saved calculation', e);
        }
    }

})();
