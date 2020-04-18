<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Paulo Ricardo Schopf <pschopf@gmail.com>
 * @author Rodrigo Pimenta <rodrigo.pimenta@gmail.com>
 */
$lang['pagesize']              = 'O formato da página conforme suportado pelo mPDF. Geralmente <code>A4</code> ou <code>Carta</code>';
$lang['orientation']           = 'Orientação da Página';
$lang['orientation_o_portrait'] = 'Retrato';
$lang['orientation_o_landscape'] = 'Paisagem';
$lang['font-size']             = 'O tamanho da fonte para textos normais.';
$lang['doublesided']           = 'O documento frente e verso começa adicionando uma página ímpar e possui pares de páginas pares e ímpares. O documento de lado único tem apenas páginas ímpares.';
$lang['toc']                   = 'Adiciona uma Tabela de Conteúdo gerada automaticamente (atenção: pode adicionar páginas em branco se iniciar em uma página ímpar e sempre incluirá no número par de páginas. As páginas ToC em si não tem números de página)';
$lang['toclevels']             = 'Define o nível superior e a profundidade máxima que são adicionados ao ToC. Os níveis padrão do wiki <a href="#config___toptoclevel">toptoclevel</a> e <a href="#config___maxtoclevel">maxtoclevel</a> são usados. Formato: <code><i>&lt;top&gt;</i>-<i>&lt;max&gt;</i></code>';
$lang['maxbookmarks']          = 'Quantos níveis de seção podem ser utilizados nos bookmarks do PDF? <small>(0=nenhum, 5=todos)</small>';
$lang['template']              = 'Qual template será utilizado para formatação dos PDFs?';
$lang['output']                = 'Como o PDF será apresentado ao usuário?';
$lang['output_o_browser']      = 'Mostrar no browser';
$lang['output_o_file']         = 'Download do PDF';
$lang['usecache']              = 'Os PDFs podem estarão em cache? Imagens internas não serão checadas pela ACL, então deixe desabilitado se deseja esta segurança para você.';
$lang['usestyles']             = 'Você pode fornecer uma lista de plugins, separados por vírgulas, com cada <code>style.css</code> ou <code>screen.css</code> que pode ser utilizado na geração do PDF. Por default somente <code>print.css</code> e <code>pdf.css</code> são utilizados.';
$lang['qrcodesize']            = 'Tamanho do Código QR gerado internamente (em pixels <code><i>width</i><b>x</b><i>height</i></code>). Vazio desabilita o recurso.';
$lang['showexportbutton']      = 'Mostrar o botão de Exportar PDF (somente quando suportado pelo template)';
