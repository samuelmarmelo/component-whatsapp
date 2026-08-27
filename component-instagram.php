<?php
/**
 * Plugin Name: Component Instagram
 * Description: Botão flutuante reutilizável do Instagram para WordPress.
 * Version: 1.0.0
 *
 * Componente visual independente para um link HTTPS do Instagram. Não depende
 * de JavaScript, API externa, tracking ou do componente de WhatsApp.
 *
 * @package ComponentInstagram
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'COMPONENT_INSTAGRAM_VERSION' ) ) {
    define( 'COMPONENT_INSTAGRAM_VERSION', '1.0.0' );
}

if ( ! defined( 'COMPONENT_INSTAGRAM_DIR' ) ) {
    define( 'COMPONENT_INSTAGRAM_DIR', __DIR__ );
}

if ( ! defined( 'COMPONENT_INSTAGRAM_URL' ) ) {
    define( 'COMPONENT_INSTAGRAM_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}

/**
 * @var array<string, mixed>
 */
$GLOBALS['component_instagram_config'] = isset( $GLOBALS['component_instagram_config'] ) && is_array( $GLOBALS['component_instagram_config'] )
    ? $GLOBALS['component_instagram_config']
    : array();

/**
 * Aceita somente links HTTPS dos domínios oficiais do Instagram.
 *
 * @param mixed $value URL recebida pelo projeto consumidor.
 * @return string
 */
function component_instagram_sanitize_url( $value ) {
    $url = trim( esc_url( (string) wp_unslash( $value ) ) );

    if ( ! $url ) {
        return '';
    }

    $parts = parse_url( $url );
    $host  = is_array( $parts ) && isset( $parts['host'] ) ? strtolower( (string) $parts['host'] ) : '';
    $scheme = is_array( $parts ) && isset( $parts['scheme'] ) ? strtolower( (string) $parts['scheme'] ) : '';

    if ( 'https' !== $scheme || ! in_array( $host, array( 'instagram.com', 'www.instagram.com' ), true ) ) {
        return '';
    }

    return $url;
}

/**
 * Retorna o link oficial sanitizado do Instagram.
 *
 * @param string $url URL opcional.
 * @return string
 */
function component_instagram_get_url( $url = '' ) {
    $url = component_instagram_sanitize_url( $url );

    if ( ! $url ) {
        $url = component_instagram_sanitize_url(
            apply_filters( 'component_instagram_url', 'https://instagram.com/nitgaymers' )
        );
    }

    return $url ? esc_url( $url ) : '';
}

/**
 * Retorna a versão de um asset usando filemtime quando disponível.
 *
 * @param string $relative_path Caminho relativo à raiz do componente.
 * @return string
 */
function component_instagram_asset_version( $relative_path ) {
    $absolute_path = COMPONENT_INSTAGRAM_DIR . '/' . ltrim( $relative_path, '/' );
    $mtime         = @filemtime( $absolute_path );

    return $mtime ? (string) $mtime : COMPONENT_INSTAGRAM_VERSION;
}

/**
 * Retorna a URL pública de um asset do componente.
 *
 * @param string $relative_path Caminho relativo ao diretório público.
 * @return string
 */
function component_instagram_asset_url( $relative_path ) {
    $base_url = ! empty( $GLOBALS['component_instagram_config']['base_url'] )
        ? $GLOBALS['component_instagram_config']['base_url']
        : COMPONENT_INSTAGRAM_URL;

    return trailingslashit( $base_url ) . ltrim( $relative_path, '/' );
}

/**
 * Registra o widget e seus hooks padrão. A função é idempotente.
 *
 * @param array<string, mixed> $args Configuração do componente.
 * @return void
 */
function component_instagram_register( $args = array() ) {
    $defaults = array(
        'url'        => 'https://instagram.com/nitgaymers',
        'aria_label' => __( 'Seguir a comunidade Nitgaymers no Instagram', 'component-instagram' ),
        'label'      => __( 'Instagram', 'component-instagram' ),
        'base_url'   => COMPONENT_INSTAGRAM_URL,
        'class'      => '',
    );

    $GLOBALS['component_instagram_config'] = wp_parse_args( $args, wp_parse_args( $GLOBALS['component_instagram_config'], $defaults ) );

    if ( ! has_action( 'wp_enqueue_scripts', 'component_instagram_enqueue_assets' ) ) {
        add_action( 'wp_enqueue_scripts', 'component_instagram_enqueue_assets', 8 );
    }

    if ( ! has_action( 'wp_footer', 'component_instagram_render_floating' ) ) {
        add_action( 'wp_footer', 'component_instagram_render_floating', 20 );
    }
}

/**
 * Carrega o CSS somente no front-end.
 *
 * @return void
 */
function component_instagram_enqueue_assets() {
    if ( is_admin() ) {
        return;
    }

    wp_enqueue_style(
        'component-instagram',
        component_instagram_asset_url( 'assets/instagram.css' ),
        array(),
        component_instagram_asset_version( '/assets/instagram.css' )
    );
}

/**
 * Renderiza manualmente o FAB ou pelo hook wp_footer.
 *
 * @param array<string, mixed> $args Sobrescreve a configuração registrada.
 * @return void
 */
function component_instagram_render_floating( $args = array() ) {
    if ( is_admin() ) {
        return;
    }

    $config = wp_parse_args( $args, $GLOBALS['component_instagram_config'] );
    $url    = component_instagram_get_url( isset( $config['url'] ) ? $config['url'] : '' );

    if ( ! $url ) {
        return;
    }

    $template = COMPONENT_INSTAGRAM_DIR . '/templates/instagram-floating.php';

    if ( ! is_readable( $template ) ) {
        return;
    }

    $config['url'] = $url;
    include $template;
}
