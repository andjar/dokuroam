# dokuroam
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

### Media support
You can add images and ???.

## Installation
Clone this project and upload it to your server. Open install.php, and install DokuWiki as normal.

When you log in as administrator, you should change the following settings:

* phpoksecurity -> Allow embedded PHP? **YES**
  * **Note that this may be a threat to safety**, use with care
* plugin»todo»AllowLinks -> Allow actions to also link to pages with the same name? **Yes**
* plugin»todo»ActionNamespace -> What namespace should your actions be created in (".:" = Current NS, Blank = Root NS)  -> **Notes**
* template -> Template aka. the design of the wiki -> "**bootstrap3**"
* tpl»bootstrap3»sidebarPosition DokuWiki Sidebar position (left or right) -> **right**
* youarehere -> Use hierarchical breadcrumbs (you probably want to disable the above option then) -> **Yes**

## Plugins
This project builds upon several plugins:

* 404manager
* bootswrapper
* bureacracy\*
* dropfiles
  * Github warns about security risks in this plugin
* dw2pdf
* imgpaste
* include\*
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
The include plugin does not support including backlinkgs out of the box. 


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

