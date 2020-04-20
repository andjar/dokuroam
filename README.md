# DokuRoam
DokuWiki based implementation of Roam Research

See a tour on youtube: https://www.youtube.com/watch?v=7JOgkxssXks

Roam Research has rapidly gained popularity by opening up to new workflows. Here, I present some modifications of DokuWiki that implement many of the central features of Roam Research.

## Features

### Backlinking and transculsion
In DokuRoam, you have notes and note topics. Notes will often contain links to one or more note topics. When this happens, the note will be included at the bottom of the note topic page. This way, you can easily see all notes related to a topic.

### Easily add notes and asks
It is easy to add notes and todos in DokuRoam. The frontpage and the sidebar contain textfields where you can jot down your thoughts directly and save them. If you write a note from the sidebar, the page you have open will automtically be linked in the note.

DokuRoam also supports todos. You simply hit a checkbox and can add a due date to make a todo instead of a note.  

### Daily summaries
The daily summary contains all notes written today and all tasks created, completed or due today.

### Markdown
DokuWiki has its own syntax, but markdown is also supported

### Revision history
All your pages has a complete revision history

### Export options
All your notes are saved as plain text files on your server. There are plugins for export to pdf or odt.

If you have pandoc installed on your server, instructions is provided below on how to export a text (with citations!) to docx (or any other pandoc-supported format)

### Media support
You can add images and ???.

## Installation

First: I am not a professional programmer and my main goal is just making this functional. As I run this setup locally, offline and with just a single user, security is not my main priority. I therefore strongly advise you not to expose this setup to the internet without reviewing the changes that I have made and evaluated the risks yourself, and I will not take any responsibility for trouble that may arise. However, I hope you'll find it useful :)

Clone this project and upload it to your server. Open install.php, and install DokuWiki as normal.

When you log in as administrator, you should change the following settings:

* phpoksecurity -> Allow embedded PHP? **YES**
  * **Note that this may be a threat to security**, use with care
* plugin»todo»AllowLinks -> Allow actions to also link to pages with the same name? **Yes**
* plugin»todo»ActionNamespace -> What namespace should your actions be created in (".:" = Current NS, Blank = Root NS)  -> **Notes**
* template -> Template aka. the design of the wiki -> "**bootstrap3**"
* tpl»bootstrap3»sidebarPosition DokuWiki Sidebar position (left or right) -> **right**
* youarehere -> Use hierarchical breadcrumbs (you probably want to disable the above option then) -> **Yes**

## Plugins
This project builds upon several plugins:

* 404manager
* bootswrapper
* [bureaucracy](https://www.dokuwiki.org/plugin:bureaucracy)\*
* dw2pdf
* imgpaste
* [include](https://www.dokuwiki.org/plugin:include)\*
* linksuggest
* markdowku
* monthcal\*
* newpagetemplate
* nosidebar
* pagelist
* sqlite
* tag
* todo
* wrap

The starred (\*) plugins have been modified.


## Modifications

### Include backlinks
The include plugin does not support including backlinks out of the box. Two modifications were made:

In incude.php I have added this on line 15

```php
$this->Lexer->addSpecialPattern("{{blinks>.+?}}", $mode, 'plugin_include_include');
```

Secondly, in helper.php (line 715) I have added

```php
case 'blinks':
            $page = $this->_apply_macro($page, $parent_id);
            resolve_pageid(getNS($parent_id), $page, $exists);
            @require_once(DOKU_INC.'inc/fulltext.php');
            $pagearrays = ft_backlinks($page,true);
            $this->taghelper =& plugin_load('helper', 'tag');
            $tags = $this->taghelper->getTopic('notes', null, 'note');
            foreach ($tags as $tag){
                $tagss[] = $tag['id'];
            }
            if(!empty($pagearrays)){
                foreach ($pagearrays as $pagearray) {
                    if(in_array($pagearray,$tagss)){
                        $pages[] = $pagearray;
                    }
                }
            }else{
                $pages[] = 'notes:dummy';
            }
            break;
```

In sum, these allows you to to use "blinks" in the same way as "tagtopic" or "namespace". Only pages in namespace "notes" tagged with "note" are included. The tag is to avoid recursion. You should therefore not use the blink in pages tagged with "notes". If there are no backlinks yet, notes:dummy is included.

### Pandoc export
Make a file called "pandoc.php":
```php
<?php

    $run_str = 'pandoc --filter pandoc-citeproc -f markdown ' . $_POST['fpath'] . ' -o ' . $_SERVER['DOCUMENT_ROOT'] . '/data/tmp/' . $_POST['pageid'] .'.docx';
    exec($run_str);
    header("Location: ". '/data/tmp/' . $_POST['pageid'] .'.docx');

?>
```
and put it eg. in the root folder. In your sidebar you can add

```php
<php>
echo '<html>';
echo '<form action="/pandoc.php" method="post" >';
echo '<input name="pageid" type="hidden" value="'. getID() .'" />';
echo '<input name="fpath" type="hidden" value="' . wikifn(getID()) . '"> ';
echo '<button type="submit">Export</button>';
echo '</form>';
echo '</html>';
</php>
``` 

Clicking on the "Export" button will run pandoc and redirect you to the file's location. For the citation to work, you should add your references in the beginning of your dokuwiki page.

### Allow wikification of hidden fields in the bureaucracy plugin

To make the note field in the sidebar properly link back to the page from which the note was made, I added a hidden field. However, we need the bureaucracy plugin to render the content of the hidden field as wikitext. In fieldhidden.php (line 28):

```php
$tlp = $this->getParam('value');
       $ins = array_slice(p_get_instructions($tlp), 2, -2);
       $tlp = p_render('xhtml', $ins, $byref_ignore);
       $form->addHidden($params['name'], $tlp. '');
```


### userall.css
```css
#dokuwiki__aside{
    position: sticky;
    top: 75px;
    bottom: 0;
    overflow: auto;
}

.dw-sidebar-content legend {
    display: none;
}

.dokuwiki div.wrap_todohide{ /* added */
    display: none;
}
```

