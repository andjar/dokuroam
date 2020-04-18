Sqlite Plugin for DokuWiki

A helper plugin to easily access a SQLite database.

The plugin detects whether PHPs PDO extension is available otherwise it
will look for the PHP sqlite extension.

PDO supports sqlite3 only, and this plugin will look for db files with extension *.sqlite3
The sqlite extension supports sqlite2 only, here this plugin will look for  *.sqlite files.

If both drivers are available, the admin allows to convert old sqlite2 format databases to
the new format. This should be done when upgrading from an older version. Manual upgrades
can be done through commandline tools. Database files can be found in the data/meta directory.

    sqlite databasename.sqlite .dump | sqlite3 databasename.sqlite3

More about this upgrade on http://www.sqlite.org/version3.html

All documentation for this plugin can be found at
http://www.dokuwiki.org/plugin:sqlite

If you install this plugin manually, make sure it is installed in
lib/plugins/sqlite/ - if the folder is called different it
will not work!

Please refer to http://www.dokuwiki.org/plugins for additional info
on how to install plugins in DokuWiki.

----

Copyright (C) Andreas Gohr <dokuwiki@cosmocode.de>,
              Gerrit Uitslag <klapinklapin@gmail.com>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; version 2 of the License

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

See the COPYING file in your DokuWiki folder for details
