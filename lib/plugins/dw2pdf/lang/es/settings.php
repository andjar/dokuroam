<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * English language file
 *
 * @author Domingo Redal <docxml@gmail.com>
 */
$lang['pagesize']              = 'El formato de la página soportado por mPDF. Usualmente <code>A4</code> o <code>letter</code>.';
$lang['orientation']           = 'Orientación de la página.';
$lang['orientation_o_portrait'] = 'Vertical';
$lang['orientation_o_landscape'] = 'Horizontal';
$lang['font-size']             = 'El tamaño de la fuente para el texto normal en puntos.';
$lang['doublesided']           = 'Documentos de dos caras inician en una página impar, y tienen pares de páginas impares y pares. Documentos de una cara sólo tienen páginas impares.';
$lang['toc']                   = 'Añadir una Tabla de Contenidos autogenerada en PDF (nota: Puede añadir páginas en blanco debido a que la TdC comienza en una página impar y la TdC siempre se incluye en una página par, las páginas de la TdC en si mismas no tienen números de páginas)';
$lang['toclevels']             = 'Define el nivel principal y la profunidad de los niveles que serán añadidos a la TdC. Los niveles de TdC del wiki por defecto usados son <a href="#config___toptoclevel">toptoclevel</a> y <a href="#config___maxtoclevel">maxtoclevel</a>. Formato: <code><i>&lt;top&gt;</i>-<i>&lt;max&gt;</i></code>';
$lang['maxbookmarks']          = '¿Cuántos niveles de secciones deben ser usados en los marcadores en el PDF? <small>(0=none, 5=all)</small>';
$lang['template']              = '¿Cuál plantilla debe ser usada para dar formato a los PDFs?';
$lang['output']                = '¿Cómo debe ser presentado el PDF al usuario?';
$lang['output_o_browser']      = 'Ver en el navegador';
$lang['output_o_file']         = 'Bajar el PDF';
$lang['usecache']              = '¿Deben los PDFs ser guardados en caché? Las imágenes añadidas no serán revisadas por los ACL, deshabilitar si es un problema de seguridad para usted.';
$lang['usestyles']             = 'Puede añadir una lista separada por comas de los plugins de los que <code>style.css</code> o <code>screen.css</code> deben ser usados para la generación del PDF. Por defecto sólo <code>print.css</code> y <code>pdf.css</code> son usados.';
$lang['qrcodesize']            = 'Tamaño del código QR añadido (en pixeles <code><i>&lt;width&gt;</i><b>x</b><i>&lt;height&gt;</i></code>). Dejar vacío para deshabilitar';
$lang['showexportbutton']      = 'Mostrar el botón de exportar PDF (solo cuando es soportado por su plantilla)';
