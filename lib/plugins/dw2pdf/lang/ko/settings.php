<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Myeongjin <aranet100@gmail.com>
 * @author wkdl <chobkwon@gmail.com>
 */
$lang['pagesize']              = 'mPDF가 지원하는 페이지 형식. 보통 <code>A4</code>나 <code>letter</code>입니다.';
$lang['orientation']           = '페이지 방향.';
$lang['orientation_o_portrait'] = '세로';
$lang['orientation_o_landscape'] = '가로';
$lang['doublesided']           = '양면 문서는 홀수 페이지를 추가하여 시작하고, 짝수 및 홀수 페이지의 쌍을 가지고 있습니다. 단면 문서는 홀수 페이지만을 가지고 있습니다.';
$lang['toc']                   = 'PDF에 자동 생성된 목차를 추가 (참고: 홀수 페이지에서 시작하기 때문에 빈 페이지를 추가 할 수 있고 목차는 항상 짝수 페이지 수를 포함합니다, 목차 페이지 자체는 페이지 번호가 없습니다)';
$lang['toclevels']             = '최고 수준의 목차에 추가되는 최대 수준의 깊이를 정의합니다. 기본 위키 목차 수준 <a href="#config___toptoclevel">toptoclevel</a> 및 <a href="#config___maxtoclevel">maxtoclevel</a>이 사용됩니다. 형식: <code><i>&lt;top&gt;</i>-<i>&lt;max&gt;</i></code>';
$lang['maxbookmarks']          = '얼마나 많은 문단 수준을 PDF 책갈피에 사용되어야 합니까? <small>(0=없음, 5=모두)</small>';
$lang['template']              = '어떤 템플릿을 PDF 파일의 형식에 사용되어야 합니까?';
$lang['output']                = '어떻게 PDF를 사용자에게 제시되어야 합니까?';
$lang['output_o_browser']      = '브라우저에서 보기';
$lang['output_o_file']         = 'PDF 다운로드';
$lang['usecache']              = 'PDF를 캐시해야 합니까? 보안 문제가 있으면 포함된 그림은 ACL을 확인되지 않고, 비활성화합니다.';
$lang['usestyles']             = 'PDF 생성에 사용되어야 하는 <code>style.css</code>나 <code>screen.css</code> 중 하나의 플러그인의 쉼표로 구분된 목록을 제공할 수 있습니다. 기본적으로 <code>print.css</code>와 <code>pdf.css</code>만 사용됩니다.';
$lang['qrcodesize']            = '포함된 QR 코드의 크기. (픽셀로 <code><i>너비</i><b>x</b><i>높이</i></code>) 비활성화하려면 비우세요';
$lang['showexportbutton']      = 'PDF 내보내기 버튼 보이기 (템플릿이 지원하고, 템플릿이 허용할 때만)';
