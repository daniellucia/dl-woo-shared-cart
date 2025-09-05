<?php

namespace DL\SharedCart;

defined('ABSPATH') || exit;

class Plugin
{

    /**
     * Inicializamos el plugin
     * @return void
     * @author Daniel Lucia
     */
    public function init(): void
    {
        add_action('woocommerce_cart_coupon', [$this, 'addSharedCartInput']);
        add_action('template_redirect', [$this, 'maybeLoadSharedCart']);
    }

    /**
     * Muestra input con el código de carrito compartido
     * @return void
     * @author Daniel Lucia
     */
    public function addSharedCartInput()
    {
        $url = $this->generateUrl();

        if (!$url) {
            return;
        }

        ?>
        <div style="display:inline-block; margin-left:12px;">
            <?php do_action('dl_woo_shared_cart_input_before', $url); ?>
            <input type="text" name="shared_cart_code" id="shared_cart_code" class="input-text" value="<?php echo esc_attr($url); ?>" style="display: none;" />
            <button type="button" class="button wp-element-button" id="apply_shared_cart"><?php esc_html_e('Share cart', 'dl-woo-shared-cart'); ?></button>
            <?php do_action('dl_woo_shared_cart_input_after', $url); ?>
        </div>

        <script>
            jQuery(document).ready(function($) {
                $('#apply_shared_cart').on('click', function() {
                    var code = $('#shared_cart_code').val();
                    if (code) {
                        navigator.clipboard.writeText(code).then(function() {
                            alert('<?php echo esc_js(__('Cart link copied to clipboard!', 'dl-woo-shared-cart')); ?>');
                        }, function() {
                            alert('<?php echo esc_js(__('Could not copy to clipboard.', 'dl-woo-shared-cart')); ?>');
                        });
                    }
                });
            });
        </script>

        <?php
    }

    /**
     * Construimos la url para compartir carrito, incluyendo atributos/variaciones
     * @return string
     * @author Daniel Lucia
     */
    private function generateUrl()
    {
        if (WC()->cart->is_empty()) {
            return '';
        }

        $items = [];
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];
            $variation_id = isset($cart_item['variation_id']) ? $cart_item['variation_id'] : 0;
            $variation = isset($cart_item['variation']) ? $cart_item['variation'] : [];
            $attributes = isset($cart_item['attributes']) ? $cart_item['attributes'] : [];

            $items[] = [
                'product_id'   => $product_id,
                'quantity'     => $quantity,
                'variation_id' => $variation_id,
                'variation'    => $variation,
                'attributes'   => $attributes,
            ];
        }

        $nonce = wp_create_nonce('dl_woo_shared_cart');

        $args = apply_filters('dl_woo_shared_cart_args', [
            'shared_cart' => base64_encode(serialize($items)),
            'shared_cart_nonce' => $nonce
        ]);

        $url = add_query_arg($args, home_url('/'));
        
        return $url;
    }

    /**
     * Comprobamos si existe un carrito compartido y lo cargamos (con atributos/variaciones)
     * @return void
     * @author Daniel Lucia
     */
    public function maybeLoadSharedCart()
    {
        if (isset($_GET['shared_cart'], $_GET['shared_cart_nonce']) && wp_verify_nonce($_GET['shared_cart_nonce'], 'dl_woo_shared_cart')) {
            
            $items = @unserialize(base64_decode($_GET['shared_cart']));
            if (!is_array($items)) {
                return;
            }

            do_action('dl_woo_shared_cart_before_load', $items);
            
            foreach ($items as $item) {
                $product_id   = intval($item['product_id'] ?? 0);
                $quantity     = intval($item['quantity'] ?? 0);
                $variation_id = intval($item['variation_id'] ?? 0);
                $variation    = isset($item['variation']) && is_array($item['variation']) ? $item['variation'] : [];
                $attributes   = isset($item['attributes']) && is_array($item['attributes']) ? $item['attributes'] : [];

                if ($product_id > 0 && $quantity > 0) {
                    WC()->cart->add_to_cart(
                        $product_id,
                        $quantity,
                        $variation_id,
                        $variation,
                        $attributes
                    );
                }
            }
            
            wp_safe_redirect(wc_get_cart_url());
            exit;
        }
    }
}
