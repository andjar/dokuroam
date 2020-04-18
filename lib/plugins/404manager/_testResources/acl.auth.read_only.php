# acl.auth.php
# <?php exit()?>
# Don't modify the lines above
#
# Access Control Lists
#
# Editing this file by hand shouldn't be necessary. Use the ACL
# Manager interface instead.
#
# If your auth backend allows special char like spaces in groups
# or user names you need to urlencode them (only chars <128, leave
# UTF-8 multibyte chars as is)
#
# none   0    Global Variable $AUTH_NONE
# read   1    Global Variable $AUTH_READ
# edit   2    Global Variable $AUTH_EDIT
# create 4    Global Variable $AUTH_CREATE
# upload 8    Global Variable $AUTH_UPLOAD
# delete 16   Global Variable $AUTH_DELETE
# admin  255  Global Variable $AUTH_ADMIN

*               @ALL        1
