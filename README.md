# Component WhatsApp

Componente reutilizável de botão flutuante do WhatsApp para projetos WordPress. A implementação foi extraída do widget global usado no tema [Studio Alma do Lar](https://github.com/marketingwebsam/studioalmadolar) e não depende de WooCommerce, JavaScript, API externa ou plugin de terceiros.

> O projeto consumidor fornece o número em formato internacional e chama `component_whatsapp_register()` uma vez. O componente registra o CSS e renderiza o botão no `wp_footer`.

## Conteúdo

| Arquivo | Responsabilidade |
|---|---|
| `component-whatsapp.php` | API pública, normalização do telefone, geração do link `wa.me`, enqueue do CSS e hook de renderização. |
| `templates/whatsapp-floating.php` | Markup acessível do botão, com `target="_blank"` e `rel="noopener noreferrer"`. |
| `assets/whatsapp.css` | Layout, posicionamento fixo, estados de foco/hover, tema escuro e redução de movimento. |
| `assets/whatsapp.svg` | Ícone branco autohospedado usado pelo botão. |

## Instalação como plugin

Copie o diretório para `wp-content/plugins/component-whatsapp/`, ative o plugin ou carregue o arquivo pelo bootstrap do projeto e registre o componente:

```php
require_once WP_CONTENT_DIR . '/plugins/component-whatsapp/component-whatsapp.php';

component_whatsapp_register(
    array(
        'number'  => '5521988491364',
        'message' => '',
    )
);
```

Quando o arquivo é carregado como plugin, a URL dos assets é resolvida automaticamente por `plugin_dir_url()`. Para instalações em outro local, informe `base_url` apontando para o diretório público do componente.

## Instalação dentro de um tema

Se o componente for copiado para `components/whatsapp/` dentro do tema, informe a URL pública desse diretório:

```php
require_once get_template_directory() . '/components/whatsapp/component-whatsapp.php';

component_whatsapp_register(
    array(
        'number'   => get_theme_mod( 'studioalmadolar_whatsapp_number', '5521988491364' ),
        'base_url' => get_template_directory_uri() . '/components/whatsapp',
    )
);
```

O número pode conter espaços, sinais e parênteses. O componente remove caracteres não numéricos, limita o resultado a 20 dígitos e só renderiza o botão quando houver um número válido.

## Configuração visual

A cor pode ser fornecida pelo filtro `component_whatsapp_button_color`. O valor é sanitizado com `sanitize_hex_color()` e recebe `#128C3C` quando estiver ausente ou inválido:

```php
add_filter(
    'component_whatsapp_button_color',
    function ( $color ) {
        return get_theme_mod( 'meu_projeto_whatsapp_color', '#128C3C' );
    }
);
```

As variáveis abaixo permitem ajustar a integração sem alterar o CSS do componente:

```css
:root {
    --component-whatsapp-right: 18px;
    --component-whatsapp-bottom: 88px;
    --component-whatsapp-z-index: 910;
    --component-whatsapp-focus: #8b5e3c;
}
```

Para uma barra de compra fixa em páginas de produto, o projeto consumidor pode elevar o botão somente quando necessário, reproduzindo o comportamento do tema de origem:

```css
@media (max-width: 782px) {
    body.single-product.has-mobile-product-sticky {
        --component-whatsapp-bottom: calc(136px + env(safe-area-inset-bottom));
    }
}
```

## API pública

| Função | Uso |
|---|---|
| `component_whatsapp_register( $args )` | Registra hooks e configura o componente. É idempotente. |
| `component_whatsapp_get_url( $message, $number )` | Retorna um link sanitizado para `https://wa.me/{número}` ou uma string vazia. |
| `component_whatsapp_sanitize_number( $value )` | Normaliza um telefone para o formato internacional aceito pelo WhatsApp. |
| `component_whatsapp_render_floating( $args )` | Renderiza manualmente o botão, caso o projeto não queira usar o hook `wp_footer`. |
| `component_whatsapp_asset_url( $relative_path )` | Resolve a URL pública de um asset conforme `base_url`. |

O componente também disponibiliza os filtros `component_whatsapp_number` e `component_whatsapp_button_color`. Não há JavaScript obrigatório: o comportamento é um link HTTPS para a conversa do WhatsApp.

## Comportamento preservado

O widget mantém as decisões visuais do projeto de origem: botão circular de `52px`, ícone interno de `30px`, posição padrão `18px` da borda direita e `88px` acima da base, `z-index` padrão `910`, foco visível, suporte ao tema escuro, `prefers-reduced-motion` e texto acessível visualmente oculto. A CTA contextual da página de produto não faz parte deste repositório, pois é um componente diferente do FAB flutuante.

## Testes manuais recomendados

Após instalar em um projeto WordPress, confirme que o número é normalizado, que o link abre `https://wa.me/{número}` em nova aba, que a mensagem inicial é codificada no parâmetro `text`, que o foco de teclado é visível e que a posição não sobrepõe barras fixas ou áreas de navegação móvel.

## Crédito do ícone

O SVG foi preservado do componente original e contém a atribuição do Font Awesome Free 6.7.2 no próprio arquivo. Consulte `assets/whatsapp.svg` para os termos e o link de licença correspondentes.
