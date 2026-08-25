<?php
/**
 * Plugin Name: Component WhatsApp
 * Description: Botão flutuante reutilizável do WhatsApp para WordPress.
 * Version: 1.0.0
 *
 * Componente reutilizável de botão flutuante do WhatsApp para WordPress.
 *
 * O componente não cria configurações próprias nem depende de tema, WooCommerce
 * ou plugin externo. O projeto consumidor fornece o número e chama
 * component_whatsapp_register() uma vez.
 *
 * @package ComponentWhatsApp
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'COMPONENT_WHATSAPP_VERSION' ) ) {
    define( 'COMPONENT_WHATSAPP_VERSION', '1.0.0' );
}

if ( ! defined( 'COMPONENT_WHATSAPP_DIR' ) ) {
    define( 'COMPONENT_WHATSAPP_DIR', __DIR__ );
}

if ( ! defined( 'COMPONENT_WHATSAPP_URL' ) ) {
    define( 'COMPONENT_WHATSAPP_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}

/**
 * Configuração corrente do componente.
 *
 * @var array<string, mixed>
 */
$GLOBALS['component_whatsapp_config'] = isset( $GLOBALS['component_whatsapp_config'] ) && is_array( $GLOBALS['component_whatsapp_config'] )
    ? $GLOBALS['component_whatsapp_config']
    : array();

/**
 * Sanitiza um número para o formato internacional aceito por wa.me.
 *
 * @param mixed $value Número recebido pelo projeto consumidor.
 * @return string
 */
function component_whatsapp_sanitize_number( $value ) {
    $number = preg_replace( '/\D+/', '', wp_unslash( (string) $value ) );

    return substr( (string) $number, 0, 20 );
}

/**
 * Monta um link seguro para iniciar conversa no WhatsApp.
 *
 * @param string $message Mensagem inicial opcional.
 * @param string $number Número internacional opcional.
 * @return string
 */
function component_whatsapp_get_url( $message = '', $number = '' ) {
    $number = component_whatsapp_sanitize_number( $number );

    if ( ! $number ) {
        $number = component_whatsapp_sanitize_number(
            apply_filters( 'component_whatsapp_number', '' )
        );
    }

    if ( ! $number ) {
        return '';
    }

    $url = 'https://wa.me/' . rawurlencode( $number );

    if ( $message ) {
        $url = add_query_arg( 'text', $message, $url );
    }

    return esc_url( $url );
}

/**
 * Retorna a versão do asset usando filemtime quando o arquivo existir.
 *
 * @param string $relative_path Caminho relativo à raiz do componente.
 * @return string
 */
function component_whatsapp_asset_version( $relative_path ) {
    $absolute_path = COMPONENT_WHATSAPP_DIR . '/' . ltrim( $relative_path, '/' );
    $mtime         = @filemtime( $absolute_path );

    return $mtime ? (string) $mtime : COMPONENT_WHATSAPP_VERSION;
}

/**
 * Retorna a URL pública de um asset do componente.
 *
 * @param string $relative_path Caminho relativo ao diretório público do componente.
 * @return string
 */
function component_whatsapp_asset_url( $relative_path ) {
    $base_url = ! empty( $GLOBALS['component_whatsapp_config']['base_url'] )
        ? $GLOBALS['component_whatsapp_config']['base_url']
        : COMPONENT_WHATSAPP_URL;

    return trailingslashit( $base_url ) . ltrim( $relative_path, '/' );
}

/**
 * Registra o componente e seus hooks padrão.
 *
 * A função é idempotente e pode ser chamada novamente para atualizar a
 * configuração antes da renderização. O projeto consumidor pode usar
 * component_whatsapp_render_floating() manualmente em vez do hook de footer.
 *
 * @param array<string, mixed> $args Configuração do componente.
 * @return void
 */
function component_whatsapp_register( $args = array() ) {
    $defaults = array(
        'number'     => '',
        'message'    => '',
        'aria_label' => __( 'Falar pelo WhatsApp', 'component-whatsapp' ),
        'label'      => __( 'WhatsApp', 'component-whatsapp' ),
        'base_url'   => COMPONENT_WHATSAPP_URL,
        'icon_url'   => '',
        'class'      => '',
    );

    $GLOBALS['component_whatsapp_config'] = wp_parse_args( $args, wp_parse_args( $GLOBALS['component_whatsapp_config'], $defaults ) );

    if ( ! array_key_exists( 'icon_url', $args ) || '' === trim( (string) $args['icon_url'] ) ) {
        $GLOBALS['component_whatsapp_config']['icon_url'] = component_whatsapp_asset_url( 'assets/whatsapp.svg' );
    }

    if ( ! has_action( 'wp_enqueue_scripts', 'component_whatsapp_enqueue_assets' ) ) {
        add_action( 'wp_enqueue_scripts', 'component_whatsapp_enqueue_assets', 8 );
    }

    if ( ! has_action( 'wp_footer', 'component_whatsapp_render_floating' ) ) {
        add_action( 'wp_footer', 'component_whatsapp_render_floating', 20 );
    }
}

/**
 * Carrega os estilos somente no front-end.
 *
 * @return void
 */
function component_whatsapp_enqueue_assets() {
    if ( is_admin() ) {
        return;
    }

    wp_enqueue_style(
        'component-whatsapp',
        component_whatsapp_asset_url( 'assets/whatsapp.css' ),
        array(),
        component_whatsapp_asset_version( '/assets/whatsapp.css' )
    );

    $button_color = sanitize_hex_color(
        apply_filters( 'component_whatsapp_button_color', '#128C3C' )
    );
    $button_color = $button_color ? $button_color : '#128C3C';

    wp_add_inline_style(
        'component-whatsapp',
        ':root { --component-whatsapp-color: ' . esc_attr( $button_color ) . '; }'
    );
}

/**
 * Renderiza o botão flutuante global.
 *
 * @param array<string, mixed> $args Sobrescreve a configuração registrada.
 * @return void
 */
function component_whatsapp_render_floating( $args = array() ) {
    if ( is_admin() ) {
        return;
    }

    $config = wp_parse_args( $args, $GLOBALS['component_whatsapp_config'] );
    $url    = ! empty( $config['url'] )
        ? esc_url( $config['url'] )
        : component_whatsapp_get_url( $config['message'], $config['number'] );

    if ( ! $url ) {
        return;
    }

    $template = COMPONENT_WHATSAPP_DIR . '/templates/whatsapp-floating.php';

    if ( ! is_readable( $template ) ) {
        return;
    }

    $config['url'] = $url;
    include $template;
}
