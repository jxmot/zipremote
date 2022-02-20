**THIS IS A WORK IN PROGRESS! Please be patient, we're almost done!**

# Zip Remote

This repository contains a utility that will zip files within a folder, **or** folder contents recursively on a remote web server and then download them to the client.

## Use Cases

* **Download server log files**: This is my primary use for this application. I maintain about a dozen servers and I review the logs periodically. I needed *something* to make that task easier and quicker.
* **Backup websites**: This application can *recursively* zip files from a starting location.

### Advantages

Typically I would use an SSH client with SFTP capabilities(with a "file explorer" window). But logging in, navigating to the correct folders, downloading the files, and doing that  for a dozen sites is tedious and time consuming.

The advantage here is that with a simple PHP script (*see *`test_zipremote.php`) the files can be downloaded (*somewhat securely too*) from all the servers in just a couple of minutes or less.

## Features

### Configurable

### Security

# Architecture

## Client

## Server

# Running The Application

## Requirements

### PHP Version

* **Server**: PHP 7.X or newer.
* **Client**: PHP 5.6 or newer.

### Server (*Site*)

### Client

## Preparation

### Edit Files

Prior to running there are some files that will require editing. The files and contents are described in the following sections.

#### Site

Path in repository: `/zipremote/site`

* `tzone.json` - Put your timezone in this file. A decent source for this is at <https://en.wikipedia.org/wiki/List_of_tz_database_time_zones>. Find your location and use the string found under the "TZ database name" column.
* `example_ziptargets.json` - Edit this file and save it as `ziptargets.json`.
  * `"ziploc"` - This is where the zip files are created prior to download.
  * `"locations"` - This is a two dimensional array. Each element in `locations[]` contains:
    * index 0 - This is the "path ID" (aka `_PATHID` in `index.php`). It is used by the client to select the zip targets (*path and zip file name*).
    * index 1 - This is the actual path to the file(s) to be zipped (aka `_TARGET` in `index.php`). It can be *relative*, *absolute*, or a sub-folder of `$HOME` on the platform where the site application is held.
    * index 2 - This is the *file pattern* used when zipping is **not** recursive (aka `_FILEPATT` in `index.php`).
    * index 3 - This is is the zip file *name* (aka `_ZIPNAME` in `index.php`). It will be placed in the location specified in `_TARGET`.
* `example_ipvalid.json.json` - Edit this file and save it as `ipvalid.json`.
  * `"list"` - This is a two dimensional array. Each element in `list[]` contains:
    * index 0 - A **Valid** IPV4 address that will be allowed access.
    * index 1 - A *name* associated with the IP address. It is for reference.
* `example_apikeys.json` - Edit this file and save it as `apikeys.json`.
  * `"keylist"` - Each element in `keylist[]` contains a unique string. It is compared to an incoming "key" value from the client. Here is an online utility for generating passwords (*work well as api keys*) - <https://passwordsgenerator.net/>
* `index.php` - There is no required editing before use. 

#### Client

Path in repository: `/zipremote/client`

* `gsfcfg.json` - There is no required editing before use. This file contains:
  * `"ziploc"` - The location where downloaded zip files will be saved.
  * `"dirsep"` - A directory separator character.
  * `"forcedl"` - If `true` the "site" will force a download of the selected zip file.
  * `"rmvafter"` - If `true` the "site" will remove the zip file after it has been force downloaded.
* `example_apikeys.json` - Edit this file and save it as `apikeys.json`. It must be identical to the "site" `apikeys.json` file.
* `example_sites.json` - Edit this file and save it as `sites.json`.
  * `"list"` - This is a two dimensional array. Each element in `list[]` contains:
    * index 0 - Contains an identifier used for selecting the site, it can be a number or a string.
    * index 1 - This is the URL of the "site", including the path to where the site application is stored.
* `test_zipremote.php` - Demonstration code, edit as needed to test your changes.

#### IMPORTANT

* The `apikeys.json` file in `client` and in `site` are the same file. If you edit one the other **must** be identical. Try to use randomized strings, don't make them easy to guess.
* The `site` files should be placed in a folder in your servers' `public_html` folder. The name of the containing folder can be anything(almost) and should be referenced in `/zipremote/ziptargets.json`. To obscure the containing folder I like to use a 12 to 16 character string of random letters and numbers.

### File Locations

# Possible Issues

* I have **not** tested where folders are symbolically linked. If this causes problems for anyone please create an issue in this repository.

# Known Issues

This section will be updated when ever new issues are discovered, but not yet resolved.

# The Future
