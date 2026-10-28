# Component WhatsApp

Componente reutilizável de botão flutuante do WhatsApp para projetos WordPress. A implementação foi extraída do widget global usado no tema [Studio Alma do Lar](https://github.com/marketingwebsam/studioalmadolar) e não depende de WooCommerce, JavaScript, API externa ou plugin de terceiros.

> O projeto consumidor fornece o número em formato internacional e chama `component_whatsapp_register()` uma vez. O componente registra o CSS e renderiza o botão no `wp_footer`.
>
> O repositório também oferece o componente irmão visual de Instagram. Ele é carregado separadamente por `component-instagram.php` e não altera nem substitui o widget do WhatsApp.

## Conteúdo

| Arquivo | Responsabilidade |
|---|---|
| `component-whatsapp.php` | API pública, normalização do telefone, geração do link `wa.me`, enqueue do CSS e hook de renderização. |
| `templates/whatsapp-floating.php` | Markup acessível do botão, com `target="_blank"` e `rel="noopener noreferrer"`. |
| `assets/whatsapp.css` | Layout, posicionamento fixo, estados de foco/hover, tema escuro e redução de movimento. |
| `assets/whatsapp.svg` | Ícone branco autohospedado usado pelo botão. |
| `component-whatsapp-unidades.php` | Variante com várias unidades: botão que abre a janela de seleção (ver seção própria). |
| `templates/whatsapp-unidades.php` | Markup acessível do botão e da janela de unidades. |
| `assets/whatsapp-unidades.css` / `.js` | Estilo e comportamento da janela (sem dependências, sem inline). |
| `component-instagram.php` | API pública, validação do link HTTPS do Instagram, enqueue do CSS e hook de renderização do widget visual. |
| `templates/instagram-floating.php` | Markup acessível do FAB de Instagram, com ícone SVG inline, `target="_blank"` e `rel="noopener noreferrer"`. |
| `assets/instagram.css` | Gradiente, layout, posicionamento fixo, estados de foco/hover, tema escuro e redução de movimento do Instagram. |

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

## Componente Instagram visual

O widget de Instagram é uma adição independente e somente visual. Ele mantém a mesma ergonomia do FAB de origem — botão circular de `52px`, ícone de `30px`, posição fixa, foco visível, suporte a safe area móvel e `prefers-reduced-motion` — mas não copia telefone, mensagem, API, tracking ou lógica específica do WhatsApp.

Carregue o arquivo ao lado do componente original e registre o widget com o endereço oficial da comunidade:

```php
require_once WP_CONTENT_DIR . '/plugins/component-whatsapp/component-instagram.php';

component_instagram_register(
    array(
        'url'      => 'https://instagram.com/nitgaymers',
        'base_url' => WP_CONTENT_URL . '/plugins/component-whatsapp',
    )
);
```

Quando o arquivo estiver dentro de um tema, informe a URL pública do diretório compartilhado:

```php
require_once get_template_directory() . '/components/whatsapp/component-instagram.php';

component_instagram_register(
    array(
        'url'      => 'https://instagram.com/nitgaymers',
        'base_url' => get_template_directory_uri() . '/components/whatsapp',
    )
);
```

A API pública adicional é:

| Função | Uso |
|---|---|
| `component_instagram_register( $args )` | Registra hooks e configura o widget. É idempotente. |
| `component_instagram_get_url( $url )` | Retorna um link HTTPS sanitizado do Instagram ou uma string vazia. |
| `component_instagram_sanitize_url( $value )` | Aceita somente `https://instagram.com` ou `https://www.instagram.com`. |
| `component_instagram_render_floating( $args )` | Renderiza manualmente o FAB, caso o projeto não use `wp_footer`. |
| `component_instagram_asset_url( $relative_path )` | Resolve a URL pública de um asset conforme `base_url`. |

O componente rejeita esquemas que não sejam HTTPS e hosts que não pertençam ao Instagram. O markup usa `target="_blank"`, `rel="noopener noreferrer"`, rótulo acessível visualmente oculto e ícone SVG inline autohospedado. A única navegação externa é o link configurado para o perfil do Instagram.

As variáveis CSS permitem ajustar sua posição sem editar o arquivo do componente:

```css
:root {
    --component-instagram-right: 18px;
    --component-instagram-bottom: 88px;
    --component-instagram-z-index: 910;
    --component-instagram-focus: #8b5e3c;
}
```

Para renderizar os dois widgets, registre `component_whatsapp_register()` e `component_instagram_register()` separadamente. Os arquivos, classes CSS, configurações e hooks são independentes; o componente original do WhatsApp continua preservado.

## Componente WhatsApp com várias unidades

Variante para quem atende por várias unidades ou setores, no lugar do recurso de "várias contas" do plugin WP Chat App: o botão flutuante abre uma janela para a pessoa escolher com quem falar, e cada unidade abre o próprio link `wa.me`. É um componente irmão e independente (`component-whatsapp-unidades.php`, classes `cwu__*`); o botão simples continua igual.

```php
require_once WP_CONTENT_DIR . '/plugins/component-whatsapp/component-whatsapp-unidades.php';

component_whatsapp_unidades_register(
    array(
        'title'       => 'Selecione a unidade',
        'description' => 'Escolha com qual unidade quer conversar.',
        'label'       => 'Mande uma mensagem no WhatsApp',
        'message'     => '',               // mensagem inicial padrão (opcional)
        'base_url'    => WP_CONTENT_URL . '/plugins/component-whatsapp',
        'accounts'    => array(
            array( 'name' => 'Unidade Centro', 'number' => '+55 (11) 90000-0001', 'detail' => 'Segunda a sexta, de 8h às 18h' ),
            array( 'name' => 'Unidade Norte',  'url'    => 'https://wa.me/5511900000002' ),
        ),
    )
);
```

Comportamento:

- Duas ou mais unidades válidas: o botão abre a janela (diálogo acessível: `aria-haspopup`, `aria-expanded`, foco vai para a primeira unidade, Tab fica preso na janela, Esc, botão Fechar e clique fora fecham e devolvem o foco).
- Uma unidade só: o botão vira link direto, sem janela.
- Nenhuma unidade válida e sem `fallback_number`: nada é renderizado (SafeWidget).
- Sem JavaScript: o botão é um link para a primeira unidade (ou `fallback_number`).
- Unidade aceita `number` (normalizado para dígitos) ou `url` (`https://wa.me/...` ou `https://api.whatsapp.com/...`); outros links são descartados.
- Sem estilo nem script inline (compatível com CSP estrita). Assets: `assets/whatsapp-unidades.css` e `assets/whatsapp-unidades.js` (sem dependências).

Ajustes visuais por variáveis CSS, sem editar o componente:

```css
.cwu {
    --cwu-cor: #2db742;
    --cwu-cor-texto: #fff;
    --cwu-right: 20px;
    --cwu-bottom: 30px;
    --cwu-z-index: 910;
    --cwu-raio: 16px;
    --cwu-foco: #004aad;
}
```

| Função | Uso |
|---|---|
| `component_whatsapp_unidades_register( $args )` | Registra hooks e configura o componente. É idempotente. |
| `component_whatsapp_unidades_render( $args )` | Renderiza manualmente (sem o hook `wp_footer`). |
| `component_whatsapp_unidades_normalize_accounts( $accounts, $message )` | Devolve só as unidades válidas (`name`, `detail`, `url`). |
| `component_whatsapp_unidades_get_url( $number, $message )` | Link `wa.me` sanitizado ou string vazia. |

Exemplo sem WordPress: `exemplos/whatsapp-unidades.html` (números fictícios). Origem: tema Loghus Pré-Vestibular (v0.2.0), que reproduziu o seletor de unidades do WP Chat App.

## Comportamento preservado

O widget mantém as decisões visuais do projeto de origem: botão circular de `52px`, ícone interno de `30px`, posição padrão `18px` da borda direita e `88px` acima da base, `z-index` padrão `910`, foco visível, suporte ao tema escuro, `prefers-reduced-motion` e texto acessível visualmente oculto. A CTA contextual da página de produto não faz parte deste repositório, pois é um componente diferente do FAB flutuante.

## Testes manuais recomendados

Após instalar em um projeto WordPress, confirme que o número é normalizado, que o link abre `https://wa.me/{número}` em nova aba, que a mensagem inicial é codificada no parâmetro `text`, que o foco de teclado é visível e que a posição não sobrepõe barras fixas ou áreas de navegação móvel.

## Crédito do ícone

O SVG foi preservado do componente original e contém a atribuição do Font Awesome Free 6.7.2 no próprio arquivo. Consulte `assets/whatsapp.svg` para os termos e o link de licença correspondentes.
