<?php
/**
 * Template do botão flutuante do WhatsApp.
 *
 * @package ComponentWhatsApp
 */

defined( 'ABSPATH' ) || exit;

$url         = isset( $config['url'] ) ? (string) $config['url'] : '';
$icon_url    = isset( $config['icon_url'] ) ? (string) $config['icon_url'] : '';
$aria_label  = isset( $config['aria_label'] ) ? (string) $config['aria_label'] : __( 'Falar pelo WhatsApp', 'component-whatsapp' );
$label       = isset( $config['label'] ) ? (string) $config['label'] : __( 'WhatsApp', 'component-whatsapp' );
$extra_class = isset( $config['class'] ) ? trim( (string) $config['class'] ) : '';
$class       = 'component-whatsapp-fab' . ( $extra_class ? ' ' . $extra_class : '' );

if ( ! $url ) {
    return;
}
?>
<a
    class="<?php echo esc_attr( $class ); ?>"
    href="<?php echo esc_url( $url ); ?>"
    target="_blank"
    rel="noopener noreferrer"
    aria-label="<?php echo esc_attr( $aria_label ); ?>"
>
    <?php if ( $icon_url ) : ?>
        <img
            src="<?php echo esc_url( $icon_url ); ?>"
            alt=""
            width="28"
            height="28"
            aria-hidden="true"
        >
    <?php endif; ?>
    <span class="component-whatsapp-fab__label"><?php echo esc_html( $label ); ?></span>
</a>
