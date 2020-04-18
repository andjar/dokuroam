<?php
/**
 * DokuWiki Plugin sqlite (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class admin_plugin_sqlite extends DokuWiki_Admin_Plugin {

    function getMenuSort() {
        return 500;
    }

    function forAdminOnly() {
        return true;
    }

    function handle() {
        global $conf;
        global $INPUT;

        if(isset($_POST['sqlite_rename'])) {

            $path = $conf['metadir'].'/'.$_REQUEST['db'];
            if(io_rename($path.'.sqlite', $path.'.sqlite3')) {
                msg('Renamed database file succesfull!', 1);
                //set to new situation
                $_REQUEST['version'] = 'sqlite3';

            } else {
                msg('Renaming database file fails!', -1);
            }

        } elseif(isset($_POST['sqlite_convert'])) {

            /** @var $DBI helper_plugin_sqlite */
            $DBI        = plugin_load('helper', 'sqlite');
            $time_start = microtime(true);

            if($dumpfile = $DBI->dumpDatabase($_REQUEST['db'], DOKU_EXT_SQLITE)) {
                msg('Database temporary dumped to file: '.hsc($dumpfile).'. Now loading in new database...', 1);

                if(!$DBI->fillDatabaseFromDump($_REQUEST['db'], $dumpfile)) {
                    msg('Conversion failed!', -1);
                    return false;
                }

                //TODO delete dumpfile
                //return @unlink($dumpfile);
                //TODO delete old sqlite2-db
                // return @unlink($conf['metadir'].'/'.$_REQUEST['db'].'.sqlite');

                msg('Conversion succeed!', 1);
                //set to new situation
                $_REQUEST['version'] = 'sqlite3';
            }
            $time_end = microtime(true);
            $time     = $time_end - $time_start;
            msg('Database "'.hsc($_REQUEST['db']).'" converted from sqlite 2 to 3 in '.$time.' seconds.', 0);

        } elseif($INPUT->bool('sqlite_export') && checkSecurityToken()) {

            /** @var $DBI helper_plugin_sqlite */
            $DBI        = plugin_load('helper', 'sqlite');
            $dbname = $INPUT->str('db');

            $dumpfile = $DBI->dumpDatabase($dbname, DOKU_EXT_PDO, true);
            if ($dumpfile) {
                header('Content-Type: text/sql');
                header('Content-Disposition: attachment; filename="'.$dbname.'.sql";');

                readfile($dumpfile);
                exit(0);
            }
        } elseif($INPUT->bool('sqlite_import') && checkSecurityToken()) {
            global $conf;

            /** @var $DBI helper_plugin_sqlite */
            $DBI        = plugin_load('helper', 'sqlite');
            $dbname = $INPUT->str('db');
            $dumpfile = $_FILES['dumpfile']['tmp_name'];

            if (empty($dumpfile)) {
                msg($this->getLang('import_no_file'), -1);
                return;
            }

            if ($DBI->fillDatabaseFromDump($dbname, $dumpfile, true)) {
                msg($this->getLang('import_success'), 1);
            }
        }
    }

    function html() {
        global $ID;
        global $conf;

        echo $this->locale_xhtml('intro');

        if(isset($_REQUEST['db']) && checkSecurityToken()) {

            echo '<h2>'.$this->getLang('db').' "'.hsc($_REQUEST['db']).'"</h2>';
            echo '<div class="level2">';

            $sqlcommandform = true;
            /** @var $DBI helper_plugin_sqlite */
            $DBI = plugin_load('helper', 'sqlite');
            if($_REQUEST['version'] == 'sqlite2') {
                if(helper_plugin_sqlite_adapter::isSqlite3db($conf['metadir'].'/'.$_REQUEST['db'].'.sqlite')) {

                    msg('This is a database in sqlite3 format.', 2);
                    msg(
                        'This plugin needs your database file has the extension ".sqlite3"
                        instead of ".sqlite" before it will be recognized as sqlite3 database.', 2
                    );
                    $form = new Doku_Form(array('method'=> 'post'));
                    $form->addHidden('page', 'sqlite');
                    $form->addHidden('sqlite_rename', 'go');
                    $form->addHidden('db', $_REQUEST['db']);
                    $form->addElement(form_makeButton('submit', 'admin', sprintf($this->getLang('rename2to3'), hsc($_REQUEST['db']))));
                    $form->printForm();

                    if($DBI->existsPDOSqlite()) $sqlcommandform = false;

                } else {
                    if($DBI->existsPDOSqlite()) {
                        $sqlcommandform = false;
                        msg('This is a database in sqlite2 format.', 2);

                        if($DBI->existsSqlite2()) {
                            $form = new Doku_Form(array('method'=> 'post'));
                            $form->addHidden('page', 'sqlite');
                            $form->addHidden('sqlite_convert', 'go');
                            $form->addHidden('db', $_REQUEST['db']);
                            $form->addElement(form_makeButton('submit', 'admin', sprintf($this->getLang('convert2to3'), hsc($_REQUEST['db']))));
                            $form->printForm();
                        } else {
                            msg(
                                'Before PDO sqlite can handle this format, it needs a conversion to the sqlite3 format.
                                Because PHP sqlite extension is not available,
                                you should manually convert "'.hsc($_REQUEST['db']).'.sqlite" in the meta directory to "'.hsc($_REQUEST['db']).'.sqlite3".<br />
                                See for info about the conversion '.$this->external_link('http://www.sqlite.org/version3.html').'.', -1
                            );
                        }
                    }
                }
            } else {
                if(!$DBI->existsPDOSqlite()) {
                    $sqlcommandform = false;
                    msg('A database in sqlite3 format needs the PHP PDO sqlite plugin.', -1);
                }
            }

            if($sqlcommandform) {
                echo '<ul>';
                echo '<li><div class="li"><a href="'.
                    wl(
                        $ID, array(
                                  'do'     => 'admin',
                                  'page'   => 'sqlite',
                                  'db'     => $_REQUEST['db'],
                                  'version'=> $_REQUEST['version'],
                                  'sql'    => 'SELECT name,sql FROM sqlite_master WHERE type=\'table\' ORDER BY name',
                                  'sectok' => getSecurityToken()
                             )
                    ).
                    '">'.$this->getLang('table').'</a></div></li>';
                echo '<li><div class="li"><a href="'.
                    wl(
                        $ID, array(
                                  'do'     => 'admin',
                                  'page'   => 'sqlite',
                                  'db'     => $_REQUEST['db'],
                                  'version'=> $_REQUEST['version'],
                                  'sql'    => 'SELECT name,sql FROM sqlite_master WHERE type=\'index\' ORDER BY name',
                                  'sectok' => getSecurityToken()
                             )
                    ).
                    '">'.$this->getLang('index').'</a></div></li>';
                echo '<li><div class="li"><a href="'.
                    wl(
                        $ID, array(
                               'do'     => 'admin',
                               'page'   => 'sqlite',
                               'db'     => $_REQUEST['db'],
                               'version'=> $_REQUEST['version'],
                               'sqlite_export' => '1',
                               'sectok' => getSecurityToken()
                           )
                    ).
                    '">'.$this->getLang('export').'</a></div></li>';


                $form = new \dokuwiki\Form\Form(array('enctype' => 'multipart/form-data'));
                $form->setHiddenField('id', $ID);
                $form->setHiddenField('do', 'admin');
                $form->setHiddenField('page', 'sqlite');
                $form->setHiddenField('db', $_REQUEST['db']);
                $form->setHiddenField('version', $_REQUEST['version']);
                $form->addElement(new dokuwiki\Form\InputElement('file', 'dumpfile'));
                $form->addButton('sqlite_import', $this->getLang('import'));
                echo '<li>' . $form->toHTML() . '</li>';
                echo '</ul>';

                $form = new Doku_Form(array('class'=> 'sqliteplugin'));
                $form->startFieldset('SQL Command');
                $form->addHidden('id', $ID);
                $form->addHidden('do', 'admin');
                $form->addHidden('page', 'sqlite');
                $form->addHidden('db', $_REQUEST['db']);
                $form->addHidden('version', $_REQUEST['version']);
                $form->addElement('<textarea name="sql" class="edit">'.hsc($_REQUEST['sql']).'</textarea>');
                $form->addElement('<input type="submit" class="button" />');
                $form->endFieldset();
                $form->printForm();

                if($_REQUEST['sql']) {

                    if(!$DBI->init($_REQUEST['db'], '')) return;

                    $sql = $DBI->SQLstring2array($_REQUEST['sql']);
                    foreach($sql as $s) {
                        $s = preg_replace('!^\s*--.*$!m', '', $s);
                        $s = trim($s);
                        if(!$s) continue;

                        $time_start = microtime(true);

                        $res = $DBI->query("$s;");
                        if($res === false) continue;

                        $result = $DBI->res2arr($res);

                        $time_end = microtime(true);
                        $time     = $time_end - $time_start;

                        $cnt = $DBI->res2count($res);
                        msg($cnt.' affected rows in '.($time < 0.0001 ? substr($time, 0, 5).substr($time, -3) : substr($time, 0, 7)).' seconds', 1);
                        if(!$cnt) continue;

                        echo '<div>';
                        $ths = array_keys($result[0]);
                        echo '<table class="inline">';
                        echo '<tr>';
                        foreach($ths as $th) {
                            echo '<th>'.hsc($th).'</th>';
                        }
                        echo '</tr>';
                        foreach($result as $row) {
                            echo '<tr>';
                            $tds = array_values($row);
                            foreach($tds as $td) {
                                if($td === null) $td='␀';
                                echo '<td>'.hsc($td).'</td>';
                            }
                            echo '</tr>';
                        }
                        echo '</table>';
                        echo '</div>';
                    }
                }

            }
            echo '</div>';
        }
    }

    function getTOC() {
        global $conf;
        global $ID;

        $toc            = array();
        $fileextensions = array('sqlite2'=> '.sqlite', 'sqlite3'=> '.sqlite3');

        foreach($fileextensions as $dbformat => $fileextension) {
            $toc[] = array(
                'link'  => wl($ID, array('do'=> 'admin', 'page'=> 'sqlite')),
                'title' => $dbformat.':',
                'level' => 1,
                'type'  => 'ul',
            );

            $dbfiles = glob($conf['metadir'].'/*'.$fileextension);

            if(is_array($dbfiles)) foreach($dbfiles as $file) {
                $db    = basename($file, $fileextension);
                $toc[] = array(
                    'link'  => wl($ID, array('do'=> 'admin', 'page'=> 'sqlite', 'db'=> $db, 'version'=> $dbformat, 'sectok'=> getSecurityToken())),
                    'title' => $this->getLang('db').' '.$db,
                    'level' => 2,
                    'type'  => 'ul',
                );
            }
        }

        return $toc;
    }
}

// vim:ts=4:sw=4:et:
