<?php
/**
 * @group plugin_bureaucracy
 * @group plugins
 */
class syntax_plugin_bureaucracy_fieldfile_test extends DokuWikiTest {

    protected $pluginsEnabled = array('bureaucracy');

    /**
     * Parse doku $syntax and check if any resulting xhtml element can be selected by $pqSelector
     *
     * @param $syntax
     * @param $pqSelector
     */
    protected function assertPqSelector($syntax, $pqSelector) {
        $xhtml = p_render('xhtml', p_get_instructions($syntax), $info);
        $doc = phpQuery::newDocument($xhtml);
        $result = pq($pqSelector, $doc);
        $this->assertEquals(1, $result->length, "selector: \"$pqSelector\" not found in\n$xhtml\n");
    }

    /**
     * Chceck if defined namespace doesn't violate $standardArgs
     */
    function test_syntax() {
        $standardArgs = array(
            '!' => 'input[type=file].edit',
            '^' => 'input[type=file][required].edit.required',
            '@' => 'input[type=file][required].edit.required',
            '! /regex/' => 'input[type=file].edit',
            '@ /regex/ "**Example error"' => 'input[type=file][required].edit.required'
        );

        //upload namespace not defined
        foreach ($standardArgs as $arg => $pqSelector) {
            $input = "<form>\nfile \"Some label\" $arg\n</form>";
            $this->assertPqSelector($input, $pqSelector);
        }

        //upload namespace defined, nothing shoud change in syntax
        foreach ($standardArgs as $arg => $pqSelector) {
            $input = "<form>\nfile \"Some label\" upload:here $arg\n</form>";
            $this->assertPqSelector($input, $pqSelector);
        }

        //upload namespace in ""
        foreach ($standardArgs as $arg => $pqSelector) {
            $input = "<form>\nfile \"Some label\" \"upload:here\" $arg\n</form>";
            $this->assertPqSelector($input, $pqSelector);
        }
    }

    /**
     * Parse the bureaucracy form syntax and simulate a file upload
     *
     * @param $form_syntax bureaucracy form syntax containg only one file field
     * @return string a name of the uploaded file
     */
    protected function simulate_file_upload($form_syntax) {
        $media = 'img.png';
        $media_src = mediaFN('wiki:dokuwiki-128.png');

        $syntax_plugin = new syntax_plugin_bureaucracy();
        $data = $syntax_plugin->handle($form_syntax, 0, 0, new Doku_Handler());

        $actionData = $data['actions'][0];
        $action = plugin_load('helper', $actionData['actionname']);

        $fileField = $data['fields'][0];

        //mock file upload
        $file = array(
            'name'     => $media,
            'type'     => 'image/png',
            'size'     => filesize($media_src),
            'tmp_name' => $media_src
        );
        //this is the only field
        $index = 0;
        //this is the only form
        $form_id = 0;
        $fileField->handle_post($file, $data['fields'], $index, $form_id);

        //upload file
        $action->run(
            $data['fields'],
            $data['thanks'],
            $actionData['argv']
        );

        return $media;
    }

    function test_action_template_upload_default() {
        $template_id = 'template_upload_default';
        $id = 'upload_default';

        saveWikiText($template_id, 'Value:@@Some label@@', 'summary');

        $form_syntax = "<form>action template $template_id $id\nfile \"Some label\"\n</form>";
        $media = $this->simulate_file_upload($form_syntax);

        //check if file exists where we suspect it to be
        $this->assertTrue(file_exists(mediaFN("$id:$media")));
    }

    function test_action_template_upload_absolute() {
        $template_id = 'template_upload_absolute';
        $id = 'upload_absolute';
        $upload_ns = 'upload:ns';

        saveWikiText($template_id, 'Value:@@Some label@@', 'summary');

        $form_syntax = "<form>action template $template_id $id\nfile \"Some label\" $upload_ns\n</form>";
        $media = $this->simulate_file_upload($form_syntax);

        //check if file exists where we suspect it to be
        $this->assertTrue(file_exists(mediaFN("$upload_ns:$media")));
    }

    function test_action_template_upload_relative() {
        $template_id = 'template_upload_relative';
        $id = 'upload_relative';
        $upload_ns_rel = 'upload:ns';
        $upload_ns = ".:$upload_ns_rel";

        saveWikiText($template_id, 'Value:@@Some label@@', 'summary');

        $form_syntax = "<form>action template $template_id $id\nfile \"Some label\" \"$upload_ns\"\n</form>";
        $media = $this->simulate_file_upload($form_syntax);

        //check if file exists where we suspect it to be
        $this->assertTrue(file_exists(mediaFN("$id:$upload_ns:$media")));
    }

}
