<?php
/**
 * Componente WhatsApp com várias unidades.
 *
 * Botão flutuante que, ao ser clicado, abre uma janela para a pessoa escolher
 * com qual unidade (ou setor) quer conversar, como o recurso de várias contas
 * do plugin WP Chat App. Cada unidade abre o próprio link wa.me.
 *
 * Independente do componente simples (component-whatsapp.php): arquivos,
 * classes CSS, configuração e hooks próprios. Sem JavaScript o botão é um
 * link para a primeira unidade (ou para `fallback_number`). O JS só liga a
 * janela (abrir, fechar, Esc, foco preso). Sem estilo nem script inline.
 *
 * @package ComponentWhatsApp
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'COMPONENT_WHATSAPP_UNIDADES_VERSION' ) ) {
    define( 'COMPONENT_WHATSAPP_UNIDADES_VERSION', '1.0.0' );
}

if ( ! defined( 'COMPONENT_WHATSAPP_UNIDADES_DIR' ) ) {
    define( 'COMPONENT_WHATSAPP_UNIDADES_DIR', __DIR__ );
}

if ( ! defined( 'COMPONENT_WHATSAPP_UNIDADES_URL' ) ) {
    define( 'COMPONENT_WHATSAPP_UNIDADES_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}

/**
 * Configuração corrente do componente.
 *
 * @var array<string, mixed>
 */
$GLOBALS['component_whatsapp_unidades_config'] = isset( $GLOBALS['component_whatsapp_unidades_config'] ) && is_array( $GLOBALS['component_whatsapp_unidades_config'] )
    ? $GLOBALS['component_whatsapp_unidades_config']
    : array();

/**
 * Normaliza um telefone para o formato internacional do wa.me.
 *
 * @param mixed $value Telefone com ou sem máscara.
 * @return string Só dígitos (máximo 20) ou ''.
 */
function component_whatsapp_unidades_sanitize_number( $value ) {
    $number = preg_replace( '/\D+/', '', (string) $value );

    return substr( (string) $number, 0, 20 );
}

/**
 * Link wa.me de uma unidade.
 *
 * @param string $number  Telefone.
 * @param string $message Mensagem inicial opcional.
 * @return string URL ou ''.
 */
function component_whatsapp_unidades_get_url( $number, $message = '' ) {
    $number = component_whatsapp_unidades_sanitize_number( $number );

    if ( strlen( $number ) < 10 ) {
        return '';
    }

    $url = 'https://wa.me/' . rawurlencode( $number );

    if ( '' !== (string) $message ) {
        $url = add_query_arg( 'text', (string) $message, $url );
    }

    return esc_url( $url );
}

/**
 * Lista de unidades válidas (nome + link), na ordem recebida.
 *
 * Cada item aceito: name (obrigatório), number ou url (wa.me / api.whatsapp.com),
 * detail (texto curto, ex.: horário) e message (mensagem inicial própria).
 *
 * @param mixed  $accounts Lista recebida.
 * @param string $message  Mensagem padrão.
 * @return array<int, array{name:string,detail:string,url:string}>
 */
function component_whatsapp_unidades_normalize_accounts( $accounts, $message = '' ) {
    $out = array();

    foreach ( is_array( $accounts ) ? $accounts : array() as $account ) {
        if ( ! is_array( $account ) || empty( $account['name'] ) ) {
            continue;
        }

        $url = '';
        if ( ! empty( $account['url'] ) && preg_match( '#^https://(wa\.me|api\.whatsapp\.com)/#', (string) $account['url'] ) ) {
            $url = esc_url( (string) $account['url'] );
        } elseif ( ! empty( $account['number'] ) ) {
            $url = component_whatsapp_unidades_get_url( $account['number'], isset( $account['message'] ) ? $account['message'] : $message );
        }

        if ( '' === $url ) {
            continue;
        }

        $out[] = array(
            'name'   => (string) $account['name'],
            'detail' => isset( $account['detail'] ) ? (string) $account['detail'] : '',
            'url'    => $url,
        );
    }

    return $out;
}

/**
 * URL pública de um asset.
 *
 * @param string $relative_path Caminho relativo ao componente.
 * @return string
 */
function component_whatsapp_unidades_asset_url( $relative_path ) {
    $base_url = ! empty( $GLOBALS['component_whatsapp_unidades_config']['base_url'] )
        ? $GLOBALS['component_whatsapp_unidades_config']['base_url']
        : COMPONENT_WHATSAPP_UNIDADES_URL;

    return trailingslashit( $base_url ) . ltrim( $relative_path, '/' );
}

/**
 * Versão do asset por filemtime.
 *
 * @param string $relative_path Caminho relativo.
 * @return string
 */
function component_whatsapp_unidades_asset_version( $relative_path ) {
    $mtime = @filemtime( COMPONENT_WHATSAPP_UNIDADES_DIR . '/' . ltrim( $relative_path, '/' ) );

    return $mtime ? (string) $mtime : COMPONENT_WHATSAPP_UNIDADES_VERSION;
}

/**
 * Registra o componente (idempotente).
 *
 * @param array<string, mixed> $args accounts, title, description, label,
 *                                   message, fallback_number, base_url, class.
 * @return void
 */
function component_whatsapp_unidades_register( $args = array() ) {
    $defaults = array(
        'accounts'        => array(),
        'title'           => __( 'Selecione a unidade', 'component-whatsapp' ),
        'description'     => '',
        'label'           => __( 'Mande uma mensagem no WhatsApp', 'component-whatsapp' ),
        'close_label'     => __( 'Fechar', 'component-whatsapp' ),
        'message'         => '',
        'fallback_number' => '',
        'base_url'        => COMPONENT_WHATSAPP_UNIDADES_URL,
        'class'           => '',
    );

    $GLOBALS['component_whatsapp_unidades_config'] = wp_parse_args( $args, wp_parse_args( $GLOBALS['component_whatsapp_unidades_config'], $defaults ) );

    if ( ! has_action( 'wp_enqueue_scripts', 'component_whatsapp_unidades_enqueue_assets' ) ) {
        add_action( 'wp_enqueue_scripts', 'component_whatsapp_unidades_enqueue_assets', 8 );
    }

    if ( ! has_action( 'wp_footer', 'component_whatsapp_unidades_render' ) ) {
        add_action( 'wp_footer', 'component_whatsapp_unidades_render', 20 );
    }
}

/**
 * CSS e JS do componente (sem inline).
 *
 * @return void
 */
function component_whatsapp_unidades_enqueue_assets() {
    if ( is_admin() ) {
        return;
    }

    wp_enqueue_style(
        'component-whatsapp-unidades',
        component_whatsapp_unidades_asset_url( 'assets/whatsapp-unidades.css' ),
        array(),
        component_whatsapp_unidades_asset_version( 'assets/whatsapp-unidades.css' )
    );

    wp_enqueue_script(
        'component-whatsapp-unidades',
        component_whatsapp_unidades_asset_url( 'assets/whatsapp-unidades.js' ),
        array(),
        component_whatsapp_unidades_asset_version( 'assets/whatsapp-unidades.js' ),
        true
    );
}

/**
 * Renderiza o botão e a janela.
 *
 * Com uma unidade só, vira link direto (sem janela). Sem unidades válidas e
 * sem fallback_number, não renderiza nada (SafeWidget).
 *
 * @param array<string, mixed> $args Sobrescreve a configuração registrada.
 * @return void
 */
function component_whatsapp_unidades_render( $args = array() ) {
    if ( is_admin() ) {
        return;
    }

    $config   = wp_parse_args( $args, $GLOBALS['component_whatsapp_unidades_config'] );
    $accounts = component_whatsapp_unidades_normalize_accounts( $config['accounts'], (string) $config['message'] );
    $fallback = component_whatsapp_unidades_get_url( (string) $config['fallback_number'], (string) $config['message'] );

    if ( ! $accounts && '' === $fallback ) {
        return;
    }

    $config['accounts']     = $accounts;
    $config['fallback_url'] = '' !== $fallback ? $fallback : $accounts[0]['url'];
    $config['icon_url']     = component_whatsapp_unidades_asset_url( 'assets/whatsapp.svg' );

    $template = COMPONENT_WHATSAPP_UNIDADES_DIR . '/templates/whatsapp-unidades.php';

    if ( is_readable( $template ) ) {
        include $template;
    }
}
