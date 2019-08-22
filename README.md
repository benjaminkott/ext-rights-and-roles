# DO NOT USE IN PRODUCTION YET

# Rights and Roles

This Extension handles several improvements for TYPO3 permission system.

- Prefix Groups with `[G]`
- Prefix Roles with `[R]`

## 1. Rights and Roles Matrix

It provides a Backend Module for a list of all Groups starting with [G].
It shows a Matrix of assigned or not assigned rights in table with the information about assignment of right to group (or to role).


## 2. Overload the access to pages etc

In the default you can only assign a single user or a single group to the access for pages.
This is sometimes not the correct way (see https://de.wikipedia.org/wiki/Access_Control_List).

So ACLs are not implemented in TYPO3. The solution is this extension.

You can implement the two following hooks in your code to archive this goal:

```

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['getPagePermsClause'][] = function ($params) use ($configuration)
{
    // Call the hook from RightsAndRoles Extension
    $hook = new \BK2K\RightsAndRoles\Hook\BackendUserGroupRightsHook($configuration);
    return $hook->getPagePermsClause($params);
};

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['calcPerms'][] = function ($params) use ($configuration)
{
    // Call the hook from RightsAndRoles Extension
    $hook = new \BK2K\RightsAndRoles\Hook\BackendUserGroupRightsHook($configuration);
    return $hook->calcPerms($params);
};
```

Now you are able to configure your permissions.

You have to add the following structure in your configuration:

```
  EXT
    page:
      debug: 1          # Debug mode flag
      access:           # the Access configuration
        13:
          0: 1
```

This means, that the group "13" (in this example a base group for simple editor) can read (decimal 1, binary 1)
all pages (0). The rights are calulated by standard binary additions (see \TYPO3\CMS\Core\Type\Bitmask\Permission):

```
  NOTHING: 0       (@see \TYPO3\CMS\Core\Type\Bitmask\Permission::NOTHING)
  PAGE_SHOW: 1     (@see \TYPO3\CMS\Core\Type\Bitmask\Permission::PAGE_SHOW)
  PAGE_EDIT: 2     (@see \TYPO3\CMS\Core\Type\Bitmask\Permission::PAGE_EDIT)
  PAGE_DELETE: 4   (@see \TYPO3\CMS\Core\Type\Bitmask\Permission::PAGE_DELETE)
  PAGE_NEW: 8      (@see \TYPO3\CMS\Core\Type\Bitmask\Permission::PAGE_NEW)
  CONTENT_EDIT: 16 (@see \TYPO3\CMS\Core\Type\Bitmask\Permission::CONTENT_EDIT)
  ALL: 31          (@see \TYPO3\CMS\Core\Type\Bitmask\Permission::ALL)
```

If a usergroup can view the page and should be able to edit the content, the correct binary value should be
"PAGE_SHOW | CONTENT_EDIT", which is calculated in decimal 17.
If a usergroup should have a specific access of a single page, you can add this page ID in the group block:

```
  EXT
    page:
      access:
        13:
          25: 19
```

With this configuration the usergroup "13" can "PAGE_SHOW && PAGE_EDIT && CONTENT_EDIT" for the Page with ID "25".
You can also combine the default page "0" (every page) with specific Page ID like:

```
  EXT
    page:
      access:
        13:
          0: 1
          25: 19
```
