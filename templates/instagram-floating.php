<?php
/**
 * Template do botão flutuante visual do Instagram.
 *
 * @package ComponentInstagram
 */

defined( 'ABSPATH' ) || exit;

$url         = isset( $config['url'] ) ? (string) $config['url'] : '';
$aria_label  = isset( $config['aria_label'] ) ? (string) $config['aria_label'] : __( 'Seguir a comunidade Nitgaymers no Instagram', 'component-instagram' );
$label       = isset( $config['label'] ) ? (string) $config['label'] : __( 'Instagram', 'component-instagram' );
$extra_class = isset( $config['class'] ) ? trim( (string) $config['class'] ) : '';
$class       = 'component-instagram-fab' . ( $extra_class ? ' ' . $extra_class : '' );

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
    <svg class="component-instagram-fab__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true" focusable="false">
        <rect width="20" height="20" x="2" y="2" rx="5" />
        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
        <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
    </svg>
    <span class="component-instagram-fab__label"><?php echo esc_html( $label ); ?></span>
</a>
