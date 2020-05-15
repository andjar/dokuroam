# DokuRoam
DokuWiki based implementation of Roam Research. Roam Research has rapidly gained popularity by opening up to new workflows. Here, I present some modifications of DokuWiki that implement many of the central features of Roam Research.

There are two main "spaces":
* Note taking and todo: https://www.youtube.com/watch?v=7JOgkxssXks
* Academic writing: https://youtu.be/VHGYJyvfZeo

In addition, you have a page for flashcards (fetched from your notes):
* https://www.youtube.com/watch?v=pSdiAeKo-fE

## Features
### General
All your files are saved as **plain text files** on your server. DokuRoam supports basic **markdown** syntax, and can include **media files**. Also, your pages has a complete **revision history** and drafts are **auto-saved**.

### Note taking
**Backlinking and transclusion**. In DokuRoam, you have *notes* and *note topics*. Notes will often contain links to one or more note topics. When this happens, the note will be included at the bottom of the note topic page. This way, you can easily see all notes related to a topic.

**Daily summaries**. The daily summary contains all notes written that day, and all tasks created, completed or due that day.

**Adding notes**. Notes can be added in the sidebar when browsing note topics and will automatically be linked to the note topic.

**Adding tasks**. You can add tasks just as easy as notes. Just hit the "Todo" checkbox below. You can also set a due date for the task.

**Adding notes/tasks by email**. Send yourself a note/todo with "\[note\]" in the subject, and run the roam/email.php at some point to fetch these emails and rebuild the dokuwiki index.

**Flashcards**. You can easily add flashcards while taking notes and rehearse as you want. You questions are visible within the note and edited from there.

### Academic writing
If you have **pandoc** installed on your server, you can export it by simply hitting a button. This will also take care of **citations**. The **YAML** section can be modified in your document to customize the export. References can be added in the sidebar formatted as **bibtex**, and are made available in the sidebar by **bibtexbrowser.php**.

You can have the editing window in fullscreen for **distraction free** editing and switch to **night mode** if you want.

## Installation

First: I am not a professional programmer and my main goal is just making this functional. As I run this setup locally, offline and with just a single user, security is not my main priority. I therefore strongly advise you not to expose this setup to the internet without reviewing the changes that I have made and evaluated the risks yourself, and I will not take any responsibility for trouble that may arise. However, I hope you'll find it useful :)

Clone this project and upload it to your server. Username and password are both "admin" and you should change this. These settings are set as default:

* phpoksecurity -> Allow embedded PHP? **YES**
  * **Note that this may be a threat to security**, use with care
* plugin»todo»AllowLinks -> Allow actions to also link to pages with the same name? **Yes**
* plugin»todo»ActionNamespace -> What namespace should your actions be created in (".:" = Current NS, Blank = Root NS)  -> **Notes**
* template -> Template aka. the design of the wiki -> "**bootstrap3**"
* tpl»bootstrap3»sidebarPosition DokuWiki Sidebar position (left or right) -> **right**
* youarehere -> Use hierarchical breadcrumbs (you probably want to disable the above option then) -> **Yes**

See the [wiki](https://github.com/andjar/dokuroam/wiki/) for details.
