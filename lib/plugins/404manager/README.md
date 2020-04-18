# dokuwiki-plugin-404manager [![Build Status](https://travis-ci.org/gerardnico/dokuwiki-plugin-404manager.svg?branch=master)](https://travis-ci.org/gerardnico/dokuwiki-plugin-404manager)

## About

The [404 manager Dokuwiki plugin](https://www.dokuwiki.org/plugin:404manager) changes the behavior of Dokuwiki when a page doesn't exist.

On a normal website, when a page doesn't exist, you get an [hard HTTP 404 response](https://en.wikipedia.org/wiki/HTTP_404), hence the name of the plugin.
Dokuwiki returns a soft 404 with this simple message : 'This topic does not exist yet'.

As you can see below on this snapshot:

![The illustration](https://github.com/gerardnico/dokuwiki-plugin-404manager/blob/master/images/dokuwiki_200.jpg "Dokuwiki This topic does not exist yet")

For the website readers, it's not really helpful and it can occur frequently when you use intensively the [plugin pagemove](https://www.dokuwiki.org/plugin:pagemove)
in order to move page around. The most important problem is that thus external links become obsolete (especially for search engine for instance).

## What can this plugin do ?

### For writers
  * Automatic redirection in edit mode
  * A message is shown when the page name already exist in other pages.

**Example:**

![The page name check message](https://github.com/gerardnico/dokuwiki-plugin-404manager/blob/master/images/404manager_check_page_name_message.png)


### For readers

  * Redirection to a target page for a source page with the admin page
  * Redirection by to the start page of the same namespace
  * Redirection by best namespace
  * Redirection to the internal search engine
  * and of course Nothing.



## Installation

Install the plugin using:

  * the [Plugin Manager](https://www.dokuwiki.org/plugin:plugin)
  * [manually](https://www.dokuwiki.org/plugin:Plugins) with the [download URL](http://github.com/gerardnico/dokuwiki-plugin-minimap/zipball/master), which points to latest version of the plugin.
  
on a server with minimal php 7.1

## Configuration settings

You can configure the 404 Manager Plugin in the Configuration settings admin page.

![404manager configruation settings](https://github.com/gerardnico/dokuwiki-plugin-404manager/blob/master/images/dokuwiki_404manager_conf.jpg)

You can :
  * choose which action you want to perform for a reader (if the first action don't find any target page, the 404Manager plugin go to the second action, ...)
  * set that a writer switch directly to the edit mode
  * set if you want to check if the name page is unique
  * set if you want to see the classic message. A classic message is a message that the user don't need to understand what happened. The 404 Manager have only one classic message and it's when a redirection to the edit mode occur for a writer)
  * set the weight factor for the [Redirection by best namepage](#redirection-by-best-namepage)


## Settings explained
### Redirection by configuration (Admin Page)

To go to the admin page, click on the admin button in the bottom of your page and click on the 404 Manager Plugin link.

![404manager in the admin page](https://github.com/gerardnico/dokuwiki-plugin-404manager/blob/master/images/dokuwiki_404manager_adminpage_list.jpg)

You will see the admin page for the 404 Manager Plugin. This page allow you to set up redirects to internal pages as external websites.

![404manager plugin admin page](https://github.com/gerardnico/dokuwiki-plugin-404manager/blob/master/images/dokuwiki_404manager_adminpage.jpg)

Action, you can perform :
  * Add/Modify a redirect with the form Add/Modify
  * Delete a redirect with the picture "delete"


### Redirection by best name page

The redirection by best name page is an simple algorithm which occurs to find the best page by name.

It calculate a score for two kinds of page :

  * the start pages of a namespace
  * the pages with the same name

A weight factor is applied and in this way, you can influence the redirection :

  * When a page have the same name (by default 4)
  * When a page is a start page of a namespace (by default 3)
  * When a namespace match (by default 5)

To change the default configuration, you must go to the [configuration settings](#configuration settings) page

#### Example

  * The page asked : namespace1:namespace2:pagename
  * We have one page with the same name : namespace1:pagename
  * We have one startpage : namespace1:namespace2:start

**Score :**
  * The score for the page with the same name is : 9 = 5 (because namespace1 is present in the asked page) + 4 (because the page name match)
  * The score for the startpage is : 13 = 5 (because namespace1 is present in the asked page) + 5 (because namespace2 is present in the asked page) + 3 (because it's a start page)

In this case, the startpage is the redirect page because it have a highest score (13 against 9)

### Redirection by best namespace
This redirection perform the same algorithm that the [redirection by best namepage](#Redirection by best namepage) but only for the start pages. If two start page for a namespace have the same score, the smallest start page is fired.

**Ex:**
  * asked page : namespace1:namespace3:namepage
  * first start page : namespace1:namespace2:start (Score 5 = 2 for namespace1 + 3 for the startpage)
  * second start page : namespace1:start (Score 5 = 2 for namespace1 + 3 for the startpage)
We have the same score and the redirection occur on the smallest start page : first start page.

### Redirection to the intern search engine
The 404 Manager redirects to the search engine.

The query performed is an explode of the page asked.

#### Example

  * The page asked : namespace1:namespace2:pre_pagename
  * The query asked : namespace1+namespace2+pre+pagename


## Data Store

You can find the meta data:
  * for the last version: in the Sqlite Database DOKUWIKI_HOME/data/meta/404manager.sqlite3
  * for the older version: in the file 404managerRedirect.conf or 404managerRedirect.conf.migrated in the directory DOKUWIKI_HOME\lib\plugins\404manager

With the SQLite plugin, the data can be queried directly through its admin page.
  
## Language
The plugin is only translated in English but you can translate it in your own language.

Just copy the directory [lang/en](lang/en), rename it in your own language (For instance, for france, to lang/fr) and translate the files.
