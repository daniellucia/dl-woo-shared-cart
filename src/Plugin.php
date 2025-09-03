<?php

defined('ABSPATH') || exit;

class DLWOOSharedCartPlugin
{

    /**
     * Inicializamos el plugin
     * @return void
     * @author Daniel Lucia
     */
    public function init(): void
    {
        add_action('woocommerce_cart_coupon', [$this, 'addSharedCartInput']);
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
            <input type="text" name="shared_cart_code" id="shared_cart_code" class="input-text" value="<?php echo esc_attr($url); ?>" style="display: none;" />
            <button type="button" class="button wp-element-button" id="apply_shared_cart"><?php esc_html_e('Share cart', 'dl-woo-shared-cart'); ?></button>
        </div>

        <script>
            jQuery(document).ready(function($) {
                $('#apply_shared_cart').on('click', function() {
                    var code = $('#shared_cart_code').val();
                    if (code) {
                        navigator.clipboard.writeText(code).then(function() {
                            alert('<?php echo esc_js(__('¡Enlace de carrito copiado al portapapeles!', 'dl-woo-shared-cart')); ?>');
                        }, function() {
                            alert('<?php echo esc_js(__('No se pudo copiar al portapapeles.', 'dl-woo-shared-cart')); ?>');
                        });
                    }
                });
            });
        </script>

        <?php
    }

    /**
     * Construimos la url para compartir carrito
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
            $items[$product_id] = $quantity;
        }

        $nonce = wp_create_nonce('dl_woo_shared_cart');
        $url = add_query_arg([
            'shared_cart' => base64_encode(serialize($items)),
            'shared_cart_nonce' => $nonce
        ], home_url('/'));

        return $url;
    }
    
}
