<?php
/**
 * Action sendemail for DokuWiki plugin bureaucracy
 */

class helper_plugin_bureaucracy_actionmail extends helper_plugin_bureaucracy_action {

    protected $_mail_html = '';
    protected $_mail_text = '';
    protected $subject = '';
    protected $replyto = array();
    protected $mailtemplate = '';

    /**
     * Build a nice email from the submitted data and send it
     *
     * @param helper_plugin_bureaucracy_field[] $fields
     * @param string                            $thanks
     * @param array                             $argv
     * @return string thanks message
     * @throws Exception mailing failed
     */
    public function run($fields, $thanks, $argv) {
        global $ID;
        global $conf;

        $mail = new Mailer();

        $this->prepareNamespacetemplateReplacements();
        $this->prepareDateTimereplacements();
        $this->prepareLanguagePlaceholder();
        $this->prepareNoincludeReplacement();
        $this->prepareFieldReplacements($fields);

        //set default subject
        $this->subject = sprintf($this->getLang('mailsubject'), $ID);

        //build html&text table, collect replyto and subject
        list($table_html, $table_text) = $this->processFieldsBuildTable($fields, $mail);

        //Body
        if($this->mailtemplate) {
            //show template
            $this->patterns['__tablehtml__'] = '/@TABLEHTML@/';
            $this->patterns['__tabletext__'] = '/@TABLETEXT@/';
            $this->values['__tablehtml__'] = $table_html;
            $this->values['__tabletext__'] = $table_text;

            list($this->_mail_html, $this->_mail_text) = $this->getContent();

        } else {
            //show simpel listing
            $this->_mail_html .= sprintf($this->getLang('mailintro')."<br><br>", dformat());
            $this->_mail_html .= $table_html;

            $this->_mail_text .= sprintf($this->getLang('mailintro')."\n\n", dformat());
            $this->_mail_text .= $table_text;
        }
        $mail->setBody($this->_mail_text,null,null,$this->_mail_html);

        // Reply-to
        if(!empty($this->replyto)) {
            $replyto = $mail->cleanAddress($this->replyto);
            $mail->setHeader('Reply-To', $replyto, false);
        }

        // To
        $to = $this->replace(implode(',',$argv)); // get recipient address(es)
        $to = $mail->cleanAddress($to);
        $mail->to($to);

        // From
        $mail->from($conf['mailfrom']);

        // Subject
        $this->subject = $this->replace($this->subject);
        $mail->subject($this->subject);

        if(!$mail->send()) {
            throw new Exception($this->getLang('e_mail'));
        }
        return '<p>' . $thanks . '</p>';
    }

    /**
     * Create html and plain table of the field
     * and collect values for subject and replyto
     *
     * @param helper_plugin_bureaucracy_field[] $fields
     * @param Mailer $mail
     * @return array of html and text table
     */
    protected function processFieldsBuildTable($fields, $mail) {
        global $ID;

        $table_html = '<table>';
        $table_text = '';

        foreach($fields as $field) {
            $html = $text = '';
            $value = $field->getParam('value');
            $label = $field->getParam('label');

            switch($field->getFieldType()) {
                case 'fieldset':
                    if(!empty($field->depends_on)) {
                        //print fieldset only if depend condition is true
                        foreach($fields as $field_tmp) {
                            if($field_tmp->getParam('label') === $field->depends_on[0] && $field_tmp->getParam('value') === $field->depends_on[1] ) {
                                list($html, $text) =  $this->mail_buildRow($label);
                            }
                        }
                    } else {
                        list($html, $text) =  $this->mail_buildRow($label);
                    }
                    break;
                case 'file':
                    if($value === null || $label === null) break; //print attachment only if field was visible
                    $file = $field->getParam('file');
                    if(!$file['size']) {
                        $message = $this->getLang('attachmentMailEmpty');
                    } else if($file['size'] > $this->getConf('maxEmailAttachmentSize')) {
                        $message = $file['name'] . ' ' . $this->getLang('attachmentMailToLarge');
                        msg(sprintf($this->getLang('attachmentMailToLarge_userinfo'), hsc($file['name']), filesize_h($this->getConf('maxEmailAttachmentSize'))), 2);
                    } else {
                        $message = $file['name'];
                        $mail->attachFile($file['tmp_name'], $file['type'], $file['name']);
                    }
                    list($html, $text) = $this->mail_buildRow($label, $message);
                    break;
                case 'subject':
                    $this->subject = $label;
                    break;
                case 'usemailtemplate':
                    if (!is_null($field->getParam('template')) ) {
                        $this->mailtemplate = $this->replace($field->getParam('template'));
                        resolve_pageid(getNS($ID), $this->mailtemplate, $ignored);
                    }
                    break;

                default:
                    if($value === null || $label === null) break;
                    if(is_array($value)) $value = implode(', ', $value);
                    list($html, $text) = $this->mail_buildRow($label, $value);

                    if(!is_null($field->getParam('replyto'))) {
                        $this->replyto[] = $value;
                    }
            }
            $table_html .= $html;
            $table_text .= $text;
        }
        $table_html .= '</table>';

        return array($table_html, $table_text);
    }

    /**
     * Build a row
     *
     * @param $column1
     * @param null $column2
     * @return array of html and text row
     */
    protected function mail_buildRow($column1,$column2=null) {
        if($column2 === null) {
            $html = '<tr><td colspan="2"><u>'.hsc($column1).'<u></td></tr>';
            $text = "\n=====".$column1.'=====';
        } else {
            $html = '<tr><td><b>'.hsc($column1).'<b></td><td>'.hsc($column2).'</td></tr>';
            $text = "\n $column1 \t\t $column2";
        }
        return array($html, $text);
    }

    /**
     * Parse mail template in html and text, and perform replacements
     *
     * @return array html and text content
     */
    protected function getContent() {
        $content = rawWiki($this->mailtemplate);
        $html = '';
        $text = '';

        if(preg_match_all('#<code\b(.*?)>(.*?)</code>#is', $content, $matches)) {
            foreach($matches[1] as $index => $codeoptions) {
                list($syntax,) = explode(' ', trim($codeoptions), 2);
                if($syntax == 'html') {
                    $html = $matches[2][$index];
                }
                if($syntax == 'text' || $syntax == '') {
                    $text = $matches[2][$index];
                }
            }
        }
        return array(
            $this->replace($html),
            $this->replace($text)
        );
    }
}
// vim:ts=4:sw=4:et:enc=utf-8:
