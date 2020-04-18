<?php
/**
 * DokuWiki Plugin imgpaste (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <gohr@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_LF')) define('DOKU_LF', "\n");
if(!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once DOKU_PLUGIN . 'action.php';

class action_plugin_imgpaste extends DokuWiki_Action_Plugin {

    private $tempdir  = '';
    private $tempfile = '';

    public function register(Doku_Event_Handler $controller) {

        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax_call_unknown');

    }

    public function handle_ajax_call_unknown(Doku_Event &$event, $param) {
        if($event->data != 'plugin_imgpaste') return;
        global $lang;

        // get data
        global $INPUT;
        $data = $INPUT->post->str('data');
        list($type, $data) = explode(';', $data);
        if(!$data) $this->fail(400, $this->getLang('e_nodata'));

        // process data encoding
        $type = strtolower(substr($type, 5)); // strip 'data:' prefix
        $data = substr($data, 7); // strip 'base64,' prefix
        $data = base64_decode($data);

        // check for supported mime type
        $mimetypes = array_flip(getMimeTypes());
        if(!isset($mimetypes[$type])) $this->fail(415, $lang['uploadwrong']);

        // prepare file names
        $tempname = $this->storetemp($data);
        $filename = $this->getConf('filename');
        $filename = str_replace(
                        array(
                             '@NS@',
                             '@ID@',
                             '@USER@',
                             '@PAGE@'
                        ),
                        array(
                             getNS($INPUT->post->str('id')),
                             $INPUT->post->str('id'),
                             $_SERVER['REMOTE_USER'],
                             noNS($INPUT->post->str('id'))
                        ),
                        $filename
                    );
        $filename  = strftime($filename);
        $filename .= '.'.$mimetypes[$type];
        $filename = cleanID($filename);

        // check ACLs
        $auth = auth_quickaclcheck($filename);
        if($auth < AUTH_UPLOAD) $this->fail(403, $lang['uploadfail']);

        // do the actual saving
        $result = media_save(
                    array(
                         'name' => $tempname,
                         'mime' => $type,
                         'ext'  => $mimetypes[$type]
                    ),
                    $filename,
                    false,
                    $auth,
                    'copy'
        );
        if(is_array($result)) $this->fail(500, $result[0]);

        //Still here? We had a successful upload
        $this->clean();
        header('Content-Type: application/json');
        $json = new JSON();
        echo $json->encode(
            array(
                'message' => $lang['uploadsucc'],
                'id' => $result
            )
        );

        $event->preventDefault();
        $event->stopPropagation();
    }

    /**
     * Create a temporary file from the given data
     *
     * exits if an error occurs
     *
     * @param $data
     * @return string
     */
    private function storetemp($data){
        // store in temporary file
        $this->tempdir  = io_mktmpdir();
        if(!$this->tempdir) $this->fail(500);
        $this->tempfile = $this->tempdir.'/'.md5($data);
        if(!io_saveFile($this->tempfile, $data)) $this->fail(500);
        return $this->tempfile;
    }

    /**
     * remove temporary file and directory
     */
    private function clean(){
        if($this->tempfile && file_exists($this->tempfile)) @unlink($this->tempfile);
        if($this->tempdir && is_dir($this->tempdir)) @rmdir($this->tempdir);
        $this->tempfile = '';
        $this->tempdir = '';
    }

    /**
     * End the execution with a HTTP error code
     *
     * Calls clean
     *
     * @param int $status HTTP status code
     * @param string $text
     */
    private function fail($status, $text=''){
        $this->clean();
        http_status($status, $text);
        exit;
    }
}

// vim:ts=4:sw=4:et:
