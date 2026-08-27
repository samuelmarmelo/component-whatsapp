<?php
/**
 * Smoke test do componente sem uma instalação completa do WordPress.
 *
 * @package ComponentWhatsApp
 */

declare( strict_types=1 );

define( 'ABSPATH', __DIR__ );

function trailingslashit( string $value ): string {
    return rtrim( $value, '/' ) . '/';
}

function plugin_dir_url( string $file ): string {
    return 'https://example.test/wp-content/plugins/component-whatsapp/';
}

function wp_unslash( string $value ): string {
    return $value;
}

function add_query_arg( string $key, string $value, string $url ): string {
    return $url . '?' . http_build_query( array( $key => $value ), '', '&', PHP_QUERY_RFC3986 );
}

function esc_url( string $value ): string {
    return filter_var( $value, FILTER_SANITIZE_URL );
}

function esc_attr( string $value ): string {
    return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
}

function esc_html( string $value ): string {
    return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
}

function __( string $text, string $domain ): string {
    return $text;
}

function sanitize_hex_color( string $color ): string {
    return preg_match( '/^#[0-9a-f]{6}$/i', $color ) ? $color : '';
}

function apply_filters( string $tag, $value ) {
    return $value;
}

function wp_parse_args( $args, array $defaults = array() ): array {
    return array_merge( $defaults, is_array( $args ) ? $args : array() );
}

function has_action( string $hook, string $callback ): bool {
    return false;
}

function add_action( string $hook, string $callback, int $priority = 10 ): void {
}

function is_admin(): bool {
    return false;
}

function wp_enqueue_style( string $handle, string $src, array $deps, string $version ): void {
}

function wp_add_inline_style( string $handle, string $data ): void {
}

require dirname( __DIR__ ) . '/component-whatsapp.php';
require dirname( __DIR__ ) . '/component-instagram.php';

component_whatsapp_register(
    array(
        'number'   => '+55 (21) 98849-1364',
        'message'  => 'Olá, teste & confirmação',
        'base_url' => 'https://example.test/component-whatsapp',
    )
);

component_instagram_register(
    array(
        'url'      => 'https://instagram.com/nitgaymers',
        'base_url' => 'https://example.test/component-whatsapp',
    )
);

$url = component_whatsapp_get_url( 'Olá, teste & confirmação', '+55 (21) 98849-1364' );
if ( 'https://wa.me/5521988491364?text=Ol%C3%A1%2C%20teste%20%26%20confirma%C3%A7%C3%A3o' !== $url ) {
    throw new RuntimeException( 'A URL do WhatsApp não foi normalizada ou codificada corretamente.' );
}

if ( 'https://example.test/component-whatsapp/assets/whatsapp.svg' !== component_whatsapp_asset_url( 'assets/whatsapp.svg' ) ) {
    throw new RuntimeException( 'A URL pública do asset não respeita base_url.' );
}

ob_start();
component_whatsapp_render_floating();
$markup = (string) ob_get_clean();

foreach ( array( 'component-whatsapp-fab', 'https://wa.me/5521988491364', 'target="_blank"', 'rel="noopener noreferrer"' ) as $expected ) {
    if ( false === strpos( $markup, $expected ) ) {
        throw new RuntimeException( 'O markup renderizado não contém: ' . $expected );
    }
}

if ( 'https://instagram.com/nitgaymers' !== component_instagram_get_url() ) {
    throw new RuntimeException( 'A URL padrão do Instagram não foi preservada.' );
}

if ( '' !== component_instagram_sanitize_url( 'http://instagram.com/nitgaymers' ) ) {
    throw new RuntimeException( 'O componente aceitou uma URL do Instagram sem HTTPS.' );
}

if ( 'https://example.test/component-whatsapp/assets/instagram.css' !== component_instagram_asset_url( 'assets/instagram.css' ) ) {
    throw new RuntimeException( 'A URL pública do CSS do Instagram não respeita base_url.' );
}

ob_start();
component_instagram_render_floating();
$instagram_markup = (string) ob_get_clean();

foreach ( array( 'component-instagram-fab', 'component-instagram-fab__icon', 'https://instagram.com/nitgaymers', 'target="_blank"', 'rel="noopener noreferrer"', 'aria-label="Seguir a comunidade Nitgaymers no Instagram"' ) as $expected ) {
    if ( false === strpos( $instagram_markup, $expected ) ) {
        throw new RuntimeException( 'O markup do Instagram não contém: ' . $expected );
    }
}

echo "Smoke test passou.\n";
