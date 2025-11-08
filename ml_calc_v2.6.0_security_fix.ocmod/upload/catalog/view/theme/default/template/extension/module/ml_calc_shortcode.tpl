<div class="ml-calc-shortcode" id="<?php echo $module_uid; ?>">
    <!-- DEBUG: Products count: <?php echo isset($debug_products_count) ? $debug_products_count : 'N/A'; ?> -->
    <!-- DEBUG: Selected product ID: <?php echo $selected_product_id; ?> -->
    <!-- DEBUG: Module HTML length: <?php echo strlen($module_html); ?> chars -->

    <?php if ($show_title) { ?>
    <h3 class="ml-calc-shortcode__title"><?php echo $title; ?></h3>
    <?php } ?>

    <?php if ($show_selector) { ?>
    <div class="ml-calc-shortcode__selector form-group">
        <label class="control-label" for="<?php echo $module_uid; ?>-product"><?php echo $text_select_product; ?></label>
        <select id="<?php echo $module_uid; ?>-product" class="form-control">
            <?php foreach ($products as $product) { ?>
            <option value="<?php echo $product['product_id']; ?>"<?php echo ($product['product_id'] == $selected_product_id) ? ' selected' : ''; ?>>
                <?php echo $product['name']; ?> (<?php echo $product['price']; ?>)
            </option>
            <?php } ?>
        </select>
    </div>
    <?php } ?>

    <?php if ($module_html) { ?>
    <div class="ml-calc-shortcode__body" id="<?php echo $module_uid; ?>-body">
        <?php echo $module_html; ?>
    </div>
    <?php } elseif (!$products) { ?>
    <div class="alert alert-info"><?php echo $text_no_products; ?></div>
    <?php } ?>
</div>
