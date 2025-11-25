(function (window, document, $) {
    'use strict';

    if (!$ || typeof $.ajax !== 'function') {
        console.error('[ML Calc] jQuery not available!');
        return;
    }

    var SHORTCODE_NAME = 'ml_calc';
    var shortcodeRegex = new RegExp('\\[' + SHORTCODE_NAME + '([^\\]]*)\\]', 'gi');

    function parseAttributes(attrString) {
        var attrs = {};
        var regex = /(\w+)\s*=\s*(?:"([^"]*)"|'([^']*)'|([^\s"'\]]+))/g;
        var match;

        while ((match = regex.exec(attrString)) !== null) {
            var key = match[1];
            var value = match[2] || match[3] || match[4] || '';
            attrs[key] = value;
        }

        return attrs;
    }

    function traverse(node, containers) {
        if (node.nodeType === 3) {
            var text = node.nodeValue;
            if (text.indexOf('[' + SHORTCODE_NAME) === -1) {
                return;
            }

            var parent = node.parentNode;
            var lastIndex = 0;
            var match;

            while ((match = shortcodeRegex.exec(text)) !== null) {
                var before = text.substring(lastIndex, match.index);
                if (before) {
                    parent.insertBefore(document.createTextNode(before), node);
                }

                var wrapper = document.createElement('div');
                wrapper.className = 'ml-calc-shortcode__placeholder';
                wrapper.setAttribute('data-shortcode', match[0]);
                parent.insertBefore(wrapper, node);
                containers.push(wrapper);

                lastIndex = match.index + match[0].length;
            }

            var after = text.substring(lastIndex);
            if (after) {
                parent.insertBefore(document.createTextNode(after), node);
            }

            parent.removeChild(node);
        } else if (node.nodeType === 1) {
            var tag = node.tagName;
            if (tag === 'SCRIPT' || tag === 'STYLE' || tag === 'TEXTAREA') {
                return;
            }

            var children = Array.prototype.slice.call(node.childNodes);
            for (var i = 0; i < children.length; i++) {
                traverse(children[i], containers);
            }
        }
    }

    function getCurrentProductId() {
        // Попробуем найти product_id в URL
        var urlParams = new URLSearchParams(window.location.search);
        var productId = urlParams.get('product_id');

        if (productId) {
            return productId;
        }

        // Попробуем найти в скрытом поле формы товара
        var productInput = document.querySelector('input[name="product_id"]');
        if (productInput) {
            return productInput.value;
        }

        return null;
    }

    function resolveShortcode(container) {
        if (container.getAttribute('data-ml-calc-loaded') === '1') {
            return;
        }

        var shortcode = container.getAttribute('data-shortcode') || '';
        var match = shortcodeRegex.exec(shortcode);
        shortcodeRegex.lastIndex = 0;

        if (!match) {
            return;
        }

        var attrs = parseAttributes(match[1] || '');

        var params = {
            route: 'extension/module/ml_calc_shortcode/shortcode'
        };

        // Если product_id не указан явно, но указан current="1", используем текущий товар
        if (!attrs.product_id && (attrs.current === '1' || attrs.current === 'true')) {
            var currentProductId = getCurrentProductId();
            if (currentProductId) {
                params.product_id = currentProductId;
                params.current_product = '1'; // Флаг для игнорирования JAN check
            }
        } else if (attrs.product_id) {
            params.product_id = attrs.product_id;
        }

        if (attrs.show_selector) {
            params.show_selector = attrs.show_selector;
        }
        if (attrs.show_title) {
            params.show_title = attrs.show_title;
        }
        if (attrs.title) {
            params.title = attrs.title;
        }

        container.setAttribute('data-ml-calc-loaded', '1');
        container.className += ' ml-calc-shortcode__loading';

        $.ajax({
            url: 'index.php',
            type: 'get',
            data: params,
            dataType: 'html'
        }).done(function (html) {
            if (html) {
                container.innerHTML = html;
                container.className = container.className.replace('ml-calc-shortcode__loading', 'ml-calc-shortcode__loaded');

                // Добавляем класс для страницы товара
                var calcWrapper = container.querySelector('.ml-calc-wrapper');
                if (calcWrapper) {
                    // Если использован current="1", значит это страница товара
                    if (attrs.current === '1' || attrs.current === 'true') {
                        calcWrapper.classList.add('ml-calc-wrapper--product-page');
                    } else {
                        // Проверяем наличие product_id в URL (старая логика)
                        var urlParams = new URLSearchParams(window.location.search);
                        if (urlParams.get('product_id')) {
                            calcWrapper.classList.add('ml-calc-wrapper--product-page');
                        }
                    }
                }

                // Инициализируем калькулятор после загрузки HTML
                if (calcWrapper && window.MLCalc && window.MLCalc.init) {
                    window.MLCalc.init(calcWrapper);
                }

                // Инициализируем обработчик смены товара
                var productSelect = container.querySelector('select[id$="-product"]');
                var bodyWrapper = container.querySelector('[id$="-body"]');

                if (productSelect && bodyWrapper) {
                    productSelect.addEventListener('change', function() {
                        var productId = this.value;

                        if (!productId) {
                            return;
                        }

                        bodyWrapper.style.opacity = '0.5';

                        $.ajax({
                            url: 'index.php?route=extension/module/ml_calc_shortcode/render&product_id=' + productId,
                            dataType: 'html'
                        }).done(function(html) {
                            if (html) {
                                bodyWrapper.innerHTML = html;

                                // Инициализируем калькулятор после загрузки нового HTML
                                var newCalcWrapper = bodyWrapper.querySelector('.ml-calc-wrapper');
                                if (newCalcWrapper && window.MLCalc && window.MLCalc.init) {
                                    window.MLCalc.init(newCalcWrapper);
                                }
                            } else {
                                alert('Error loading calculator');
                            }
                        }).fail(function() {
                            alert('Error loading calculator');
                        }).always(function() {
                            bodyWrapper.style.opacity = '1';
                        });
                    });
                }
            } else {
                container.className = container.className.replace('ml-calc-shortcode__loading', 'ml-calc-shortcode__error');
                container.innerHTML = '';
            }
        }).fail(function () {
            container.className = container.className.replace('ml-calc-shortcode__loading', 'ml-calc-shortcode__error');
        });
    }

    $(function () {
        var containers = [];
        traverse(document.body, containers);
        if (!containers.length) {
            // Дополнительная проверка: может шорткод находится внутри HTML?
            var bodyHTML = document.body.innerHTML;
            if (bodyHTML.indexOf('[ml_calc') > -1) {
                // Альтернативный поиск через innerHTML
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = bodyHTML;
                traverse(tempDiv, containers);
            }

            if (!containers.length) {
                return;
            }
        }

        for (var i = 0; i < containers.length; i++) {
            resolveShortcode(containers[i]);
        }
    });
})(window, document, window.jQuery || window.$);
