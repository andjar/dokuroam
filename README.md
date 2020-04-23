# DokuRoam
DokuWiki based implementation of Roam Research

See a tour on youtube: https://www.youtube.com/watch?v=7JOgkxssXks

Academic writing mode: https://www.youtube.com/watch?v=qu55V7LzdEI

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

### Article namespace
Here you can start writing articles and add references in the sidebar as bibtex. The Export function will give you nice articles using pandoc, with citations!

#### Nice reference list
I use bibtexbrowser.php to show the references.

### Revision history
All your pages has a complete revision history

#### Archive notes
You can add a simply "Archive" button to your sidebar by adding

```php
<php>
echo '<html>';
echo '<form action="/roam/archive.php" method="post" >';
echo '<input name="pageid" type="hidden" value="'. getID() .'" />';
echo '<input name="fpath" type="hidden" value="' . wikifn(getID()) . '"> ';
echo '<button type="submit">Archive</button>';
echo '</form>';
echo '</html>';
</php>
```

This will simply exchange the note tag with archived so that the note is not included in backlinks or in the note overview. All links to it will still work, and the file is not removed from the notes folder.

### Export options
All your notes are saved as plain text files on your server. There are plugins for export to pdf or odt.

If you have pandoc installed on your server, instructions are provided below on how to export a text (with citations!) to docx (or any other pandoc-supported format)

#### Pandoc export
There is a file called "pandoc.php"
```php
<?php

    $run_str = 'pandoc --filter pandoc-citeproc -f markdown ' . $_POST['fpath'] . ' -o ' . $_SERVER['DOCUMENT_ROOT'] . '/data/tmp/' . $_POST['pageid'] .'.docx';
    exec($run_str);
    header("Location: ". '/data/tmp/' . $_POST['pageid'] .'.docx');

?>
```
in the roam folder. In your sidebar you can add

```php
<php>
echo '<html>';
echo '<form action="/roam/pandoc.php" method="post" >';
echo '<input name="pageid" type="hidden" value="'. getID() .'" />';
echo '<input name="fpath" type="hidden" value="' . wikifn(getID()) . '"> ';
echo '<button type="submit">Export</button>';
echo '</form>';
echo '</html>';
</php>
``` 

Clicking on the "Export" button will run pandoc and redirect you to the file's location. For the citation to work, you should add your references in the beginning of your dokuwiki page. At the moment, there is no control of file access.

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
* [monthcal](https://www.dokuwiki.org/plugin:monthcal)\*
* newpagetemplate
* nosidebar
* pagelist
* pagemod
* sqlite
* tag
* todo
* wrap

The starred (\*) plugins have been modified.


## Modifications

### Include backlinks in the include plugin
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

In sum, these allows you to to use "blinks" in the same way as "tagtopic" or "namespace". Only pages in namespace "notes" tagged with "note" are included. The tag is to avoid recursion. You should therefore not use the blinks in pages tagged with "notes". If there are no backlinks yet, notes:dummy is included.

### Allow wikification of hidden fields in the bureaucracy plugin

To make the note field in the sidebar properly link back to the page from which the note was made, I added a hidden field. However, we need the bureaucracy plugin to render the content of the hidden field as wikitext. In fieldhidden.php (line 28):

```php
$tlp = $this->getParam('value');
       $ins = array_slice(p_get_instructions($tlp), 2, -2);
       $tlp = p_render('xhtml', $ins, $byref_ignore);
       $form->addHidden($params['name'], $tlp. '');
```

### Customize the links in the monthcal plugin
We want the links in the monthcal plugin to apply specific templates for dates and for months. This we can do by exploiting the newpagetemplate plugin.

Line 332 in syntax.php:

```php
$id = $data['namespace'] . ':' . $date->format('Y') . $date->format('m') . $date->format('d');
			$linkstring = '&newpagetemplate=journal:tmplt&newpagevars=@tododate@%2C'.$date->format('Y') . '-' . $date->format('m') . '-' . $date->format('d');
```

and line 345:

```php
$html_day = '<a href="' . wl($id) . $linkstring . '">' . $date->format('d') . '</a>';
```

To make a new calendar view, we edit line 280:

```php
$html .= html_wikilink($data['namespace'] . ':' . $date_prev_month->format('Y') . $date_prev_month->format('m') . ':', '<<');
$html .= html_wikilink($data['namespace'] . ':' . $date_next_month->format('Y') . $date_next_month->format('m') . ':', '>>');
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

.dokuwiki div.wrap_todohide{
    display: none;
}

.dokuwiki div.plugin_include_content div.wrap_exclude p{
    display: none;
}

.dokuwiki div.plugin_include_content div.wrap_exclude:after{
    content: "...";
}

.dokuwiki div.plugin_include_content div.wrap_exclude_totally{
    display: none;
}
```

#### WRAP elements
You can use 
```
<WRAP exclude>
Some text
</WRAP>
```

to avoid the text from showing up in transclusions. The text is replaced by a simple "...". To hide it completely, use exclude_totally instead.

### bibtexbrowser.php

We need to allow for .txt files, so edit line 327 to

```php
if (BIBTEXBROWSER_LOCAL_BIB_ONLY && (!file_exists($bib) || strcasecmp($ext, 'bib') != 0 || strcasecmp($ext, 'txt') != 0)) {
```

Also, we want to show the key (line 94);

```php
@define('ABBRV_TYPE','key');// may be year/x-abbrv/key/none/index/keys-index
```
