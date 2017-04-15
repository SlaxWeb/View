# ChangeLog

Changes between versions

## Current changes

* load templates without a view class
* load sub templates without a view class
* load multiple sub views/sub templates with the same name

## v0.5

### v0.5.1

* fix view consecutive load cache name malformation disabling load of multiple different
views in same requests

### v0.5.0

* Save view object to application properties and re-use it on consecutive calls
to the loader

## v0.4

### v0.4.2

* Use layout in view loader by default

### v0.4.1

* Fix subcomponent 'view-twig' version in component metadata file

### v0.4.0

* initial version
