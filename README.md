**THIS IS A WORK IN PROGRESS! Please be patient, we're almost done!**

# Zip Remote

This repository contains a utility that will zip files within a folder, **or** folder contents recursively on a remote web server and then download them to the client.

- [Zip Remote](#zip-remote)
  * [Use Cases](#use-cases)
    + [Advantages](#advantages)
  * [Features](#features)
    + [Configurable](#configurable)
    + [Security](#security)
- [Architecture](#architecture)
  * [Client](#client)
  * [Server](#server)
- [Running The Application](#running-the-application)
  * [Requirements](#requirements)
    + [PHP Version](#php-version)
    + [Site](#site)
    + [Client](#client-1)
  * [Preparation](#preparation)
    + [Edit Files](#edit-files)
      - [Site](#site-1)
      - [Client](#client-2)
      - [IMPORTANT](#important)
    + [File Locations](#file-locations)
- [Extras](#extras)
- [Possible Issues](#possible-issues)
- [Known Issues](#known-issues)
- [The Future](#the-future)

<small><i><a target='_blank' href='http://ecotrust-canada.github.io/markdown-toc/'>Table of contents generated with markdown-toc</a></i></small>

## Use Cases

* **Download server log files**: This is my primary use for this application. I maintain about a dozen servers and I review the logs periodically. I needed *something* to make that task easier and quicker.
* **Backup websites**: This application can *recursively* zip files from a starting location.

### Advantages

Typically I would use an SSH client with SFTP capabilities(with a "file explorer" window). But logging in, navigating to the correct folders, downloading the files, and doing that for a dozen sites is tedious and time consuming.

The advantage here is that with a simple PHP script (*see *`test_zipremote.php`) the files can be downloaded (*somewhat securely too*) from all the servers in just a couple of minutes or less.

## Features

There are two parts in this application. The primary part is the **Site** side. It is to be installed on an accessible server running Apache 2 and PHP V7+.

The second part is the **Client** side. The code provided is more of demonstration of how to use the API. 

### Configurable

Both *sides* of this application make use JSON files to contain configurations and run-time settings. 

### Security

# Architecture

## Client

## Server

# Running The Application

## Requirements

### PHP Version

* **Server**: PHP 7.X or newer.
* **Client**: PHP 5.6 or newer.

### Site

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
    * index `0` - This is the "path ID" (aka `_PATHID` in `index.php`). It is used by the client to select the zip targets (*path and zip file name*).
    * index `1` - This is the actual path to the file(s) to be zipped (aka `_TARGET` in `index.php`). It can be *relative*, *absolute*, or a sub-folder of `$HOME` on the platform where the site application is held.
    * index `2` - This is the *file pattern* used when zipping is **not** recursive (aka `_FILEPATT` in `index.php`).
    * index `3` - This is is the zip file *name* (aka `_ZIPNAME` in `index.php`). It will be placed in the location specified in `_TARGET`.
* `example_ipvalid.json.json` - Edit this file and save it as `ipvalid.json`.
  * `"list"` - This is a two dimensional array. Each element in `list[]` contains:
    * index `0` - A **Valid** IPV4 address that will be allowed access.
    * index `1` - A *name* associated with the IP address. It is for reference.
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
    * index `0` - Contains an identifier used for selecting the site, it can be a number or a string.
    * index `1` - This is the URL of the "site", including the path to where the site application is stored.
* `test_zipremote.php` - Demonstration code, edit as needed to test your changes.

See [Extras](#extras) for additional files and information.

## IMPORTANT

### JSON Key File

The `apikeys.json` file in `client` and in `site` are the same file. If you edit one the other **must** be identical. Try to use randomized strings, don't make them easy to guess.

### File Locations

The `site` files should be placed in a folder in your servers' `public_html` folder. The name of the containing folder can be anything(almost) and should be referenced in `/zipremote/client/sites.json`. To obscure the containing folder I like to use a 12 to 16 character string of random letters and numbers. For example:

This site:
`["bigsite", "https://bigsite_server/`**`zipremote`**`"]`

Change to:
`["bigsite", "https://bigsite_server/F7Mh3MRhXEUA"]`

And the `site` files are in:
`/home/$USER/pubic_html/F7Mh3MRhXEUA`

# Extras

**HTML/JavaScript Client** - `/zipremote/client/demo_gsfapi.html` and `/zipremote/client/gsfapi.php`. The `gsfapi.php` file is called via a `GET` method in `demo_gsfapi.html`. 

**NOTE**: You will need an HTTP server and PHP>=5.6 for `demo_gsfapi.html`.

# Possible Issues

* I have **not** tested where folders are symbolically linked. If this causes problems for anyone please create an issue in this repository.

# Known Issues

This section will be updated when ever new issues are discovered, but not yet resolved.

# The Future
