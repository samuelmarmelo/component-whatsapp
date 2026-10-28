<?php
/**
 * Template do WhatsApp com várias unidades.
 *
 * Recebe $config com accounts (normalizadas), fallback_url, icon_url, title,
 * description, label, close_label e class.
 *
 * @package ComponentWhatsApp
 */

defined( 'ABSPATH' ) || exit;

$accounts    = isset( $config['accounts'] ) && is_array( $config['accounts'] ) ? $config['accounts'] : array();
$fallback    = isset( $config['fallback_url'] ) ? (string) $config['fallback_url'] : '';
$icon_url    = isset( $config['icon_url'] ) ? (string) $config['icon_url'] : '';
$label       = isset( $config['label'] ) ? (string) $config['label'] : '';
$extra_class = isset( $config['class'] ) ? trim( (string) $config['class'] ) : '';
$multi       = count( $accounts ) > 1;
$panel_id    = 'cwu-janela';

if ( '' === $fallback ) {
    return;
}
?>
<div class="cwu<?php echo $extra_class ? ' ' . esc_attr( $extra_class ) : ''; ?>" data-cwu>
    <a
        class="cwu__botao"
        href="<?php echo esc_url( $fallback ); ?>"
        target="_blank"
        rel="noopener noreferrer"
        <?php if ( $multi ) : ?>
            data-cwu-abrir
            aria-haspopup="dialog"
            aria-controls="<?php echo esc_attr( $panel_id ); ?>"
            aria-expanded="false"
        <?php endif; ?>
    >
        <?php if ( $icon_url ) : ?>
            <img class="cwu__icone" src="<?php echo esc_url( $icon_url ); ?>" alt="" width="28" height="28" aria-hidden="true">
        <?php endif; ?>
        <span class="cwu__rotulo"><?php echo esc_html( $label ); ?></span>
    </a>

    <?php if ( $multi ) : ?>
        <div class="cwu__janela" id="<?php echo esc_attr( $panel_id ); ?>" role="dialog" aria-modal="true" aria-labelledby="cwu-titulo" hidden>
            <div class="cwu__topo">
                <div class="cwu__textos">
                    <p class="cwu__titulo" id="cwu-titulo"><?php echo esc_html( (string) $config['title'] ); ?></p>
                    <?php if ( ! empty( $config['description'] ) ) : ?>
                        <p class="cwu__descricao"><?php echo esc_html( (string) $config['description'] ); ?></p>
                    <?php endif; ?>
                </div>
                <button type="button" class="cwu__fechar" data-cwu-fechar aria-label="<?php echo esc_attr( (string) $config['close_label'] ); ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <ul class="cwu__lista">
                <?php foreach ( $accounts as $account ) : ?>
                    <li>
                        <a class="cwu__unidade" href="<?php echo esc_url( $account['url'] ); ?>" target="_blank" rel="noopener noreferrer" data-cwu-unidade>
                            <span class="cwu__avatar" aria-hidden="true">
                                <?php if ( $icon_url ) : ?>
                                    <img src="<?php echo esc_url( $icon_url ); ?>" alt="" width="22" height="22">
                                <?php endif; ?>
                            </span>
                            <span class="cwu__info">
                                <span class="cwu__nome"><?php echo esc_html( $account['name'] ); ?></span>
                                <?php if ( '' !== $account['detail'] ) : ?>
                                    <span class="cwu__detalhe"><?php echo esc_html( $account['detail'] ); ?></span>
                                <?php endif; ?>
                            </span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
