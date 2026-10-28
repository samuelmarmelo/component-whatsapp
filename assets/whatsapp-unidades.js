/**
 * Componente WhatsApp com várias unidades: liga a janela de seleção.
 *
 * SafeWidget: cada instância roda isolada em try/catch; sem o JS o botão
 * continua sendo um link para a primeira unidade. Sem dependências.
 */
(function () {
    'use strict';

    function iniciar(caixa) {
        var botao = caixa.querySelector('[data-cwu-abrir]');
        var janela = caixa.querySelector('.cwu__janela');
        var fechar = caixa.querySelector('[data-cwu-fechar]');
        if (!botao || !janela) {
            return;
        }

        function focaveis() {
            return Array.prototype.slice.call(janela.querySelectorAll('a[href], button:not([disabled])'));
        }

        function teclado(evento) {
            if (evento.key === 'Escape') {
                evento.preventDefault();
                fecharJanela(true);
                return;
            }
            if (evento.key !== 'Tab') {
                return;
            }
            var lista = focaveis();
            if (!lista.length) {
                return;
            }
            var primeiro = lista[0];
            var ultimo = lista[lista.length - 1];
            if (evento.shiftKey && document.activeElement === primeiro) {
                evento.preventDefault();
                ultimo.focus();
            } else if (!evento.shiftKey && document.activeElement === ultimo) {
                evento.preventDefault();
                primeiro.focus();
            }
        }

        function fora(evento) {
            if (!caixa.contains(evento.target)) {
                fecharJanela(false);
            }
        }

        function abrir() {
            janela.hidden = false;
            caixa.classList.add('is-aberta');
            botao.setAttribute('aria-expanded', 'true');
            var primeiro = janela.querySelector('[data-cwu-unidade]') || focaveis()[0];
            if (primeiro) {
                primeiro.focus();
            }
            document.addEventListener('keydown', teclado);
            document.addEventListener('click', fora, true);
        }

        function fecharJanela(devolverFoco) {
            janela.hidden = true;
            caixa.classList.remove('is-aberta');
            botao.setAttribute('aria-expanded', 'false');
            document.removeEventListener('keydown', teclado);
            document.removeEventListener('click', fora, true);
            if (devolverFoco) {
                botao.focus();
            }
        }

        botao.addEventListener('click', function (evento) {
            evento.preventDefault();
            if (janela.hidden) {
                abrir();
            } else {
                fecharJanela(true);
            }
        });

        if (fechar) {
            fechar.addEventListener('click', function () {
                fecharJanela(true);
            });
        }

        Array.prototype.forEach.call(janela.querySelectorAll('[data-cwu-unidade]'), function (link) {
            link.addEventListener('click', function () {
                fecharJanela(false);
            });
        });
    }

    function iniciarTodos() {
        Array.prototype.forEach.call(document.querySelectorAll('[data-cwu]'), function (caixa) {
            try {
                iniciar(caixa);
            } catch (erro) {
                if (window.console && window.console.warn) {
                    window.console.warn('[component-whatsapp-unidades]', erro);
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciarTodos);
    } else {
        iniciarTodos();
    }
})();
