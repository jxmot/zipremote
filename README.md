**THIS IS A WORK IN PROGRESS! Please be patient, we're almost done!**

# Zip Remote

This repository contains a utility that will zip files within a folder, **or** folder contents recursively on a remote web server and then download them to the client. The utility also has the ability to upload a zip file and optionally extract all or part of its contents.

- [Zip Remote](#zip-remote)
  * [Use Cases](#use-cases)
    + [Advantages](#advantages)
  * [Features](#features)
    + [Configurable](#configurable)
    + [Security](#security)
- [Running The Application](#running-the-application)
  * [Requirements](#requirements)
    + [PHP Versions](#php-versions)
    + [Apache](#apache)
    + [Site](#site)
    + [Client](#client)
      - [Run!](#run-)
  * [Preparation](#preparation)
    + [Edit Files](#edit-files)
      - [Site](#site-1)
      - [Client](#client-1)
  * [IMPORTANT](#important)
    + [JSON Key File](#json-key-file)
    + [File Locations](#file-locations)
- [Extras](#extras)
  * [HTML Demo Client](#html-demo-client)
- [Possible Issues](#possible-issues)
- [Known Issues](#known-issues)
- [The Future](#the-future)

<small><i><a href='http://ecotrust-canada.github.io/markdown-toc/'>Table of contents generated with markdown-toc</a></i></small>

## Use Cases

* **Download server log files**: This is my primary use for this application. I maintain about a dozen servers and I review the logs periodically. I needed *something* to make that task easier and quicker.
* **Backup websites**: This application can *recursively* zip files from a starting location.
* **Distribute Content or Files**: This application can help with the upload and unzip of website files. 

### Advantages

Typically I would use an SSH client with SFTP capabilities with a "file explorer" window. But logging in, navigating to the correct folders, downloading the files, and doing that for a dozen sites is tedious and time consuming.

The advantage here is that with a simple PHP script (*see* `client/test_zipremote.php`) the files can be downloaded (*somewhat securely too*) from all the servers in just a couple of minutes or less.

## Features

There are two parts in this application. The primary part is the **Site** side. It is intended to be installed on an internet accessible server running Apache 2 and PHP V7+.

The second part is the **Client** side and the code provided is more of a demonstration of how to use the API.

### Configurable

Both *sides* of this application make use JSON files to contain configurations and run-time settings. 

### Security

The security implementation in this application is not the *best*. However it should be sufficient for most use-cases.

**First Level** - This is accomplished on the "site" side by checking the visiting IP address against a list of "approved" IP addresses. **NOTE**: This has been disabled in order to make it easier to get everything running. And later you can add IPs to `ipvalid.json`.

**Second Level** - This is accomplished by the use of a "key" and a "path ID". With those two parameters the client identifies itself and selects a predetermined path and zip operation(*files only, or recursive*).

**Third Level** - When you create the folder to contain the `site` files do not use the name `zipremote` or `site` to contain the `site` files. Make it obscure by using a randomized name.

# Running The Application

Before continuing please review the [Preparation](#preparation) section.

This application only runs when a request is received from a "client". It responds to HTTP GET and PUT requests with HTTP error codes and a JSON formatted response.

## Overview

### Download a Zip File

<p align="center">
  <img src="./mdimg/overview-GET.jpg" style="width:60%"; alt="Overview Diagram" txt="Overview Diagram"/>
</p>

### Upload a Zip File

<p align="center">
  <img src="./mdimg/overview-PUT.jpg" style="width:60%"; alt="Overview Diagram" txt="Overview Diagram"/>
</p>

## Requirements

### Server

I'm using this application on Linux/cPanel servers.

### PHP Versions

* **Server**: PHP 7.X or newer.
* **Client**: PHP 5.6 or newer.

### Apache

Apache 2.4 or newer is recommended.

### Site

After editing the [Site](#site-1) JSON files copy all files in the `site` folder to a folder on your website's server.

### Client

Edit `/zipremote/client/test_zipremote.php` to match the changes you will make to the [Client](#client-1) JSON files.

### Tools

My primary development environment is in Windows and this what use:

* SSH/SFTP - I use a *free* SSH client from [Bitvise](<https://www.bitvise.com/>).
* Text editor - Your choice
* PC Web server - I use [XAMPP](<https://www.apachefriends.org/index.html>) for running the [HTML Demo Client](#html-demo-client)
* API tester - I use [Postman](<https://www.postman.com/>) for developing and testing  endpoints.

## Run!

At a local command line run this from within the `client` folder - `php ./test_gettZipFile.php`.

## Preparation

### Edit Files

Prior to running there are some files that will require editing. The files and contents are described in the following sections.

#### Site

Path in repository: `/site`

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
  * `$ipv` - This enables or disables IP validation. By default is disabled. Set it to `true` to enable it after you have IP addresses in `ipvalid.json`.
* `.htaccess`- There is no required editing before use. This will allow URLs to work without the `.php` extension.

#### Client

Path in repository: `/client`

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
* `test_getZipFile.php` **and** `test_putZipFile.php` - Demonstration code, edit as needed to test your changes.

See [Extras](#extras) for additional files and information.

## IMPORTANT

### JSON Key File

The `apikeys.json` file in the `client` and in `site` are the same file. If you edit one the other **must** be identical. Try to use randomized strings, don't make them easy to guess.

### File Locations

The `site` files should be placed in a folder in your servers' `public_html` folder. The name of the containing folder can be anything(almost) and should be referenced in `/client/sites.json`. To obscure the containing folder I like to use a 12 to 16 character string of random letters and numbers. For example:

This site:
`["bigsite", "https://bigsite_server/zipremote"]`

Change to:
`["bigsite", "https://bigsite_server/F7Mh3MRhXEUA"]`

And the `site` files get copied into:
`/home/$USER/pubic_html/F7Mh3MRhXEUA`

Or the equivalent location on your server.

# Extras

## HTML Demo Client

The files `/zipremote/client/demo_gsfapi.html` and `/zipremote/client/gsfapi.php` were created to demonstrate the use of JavaScript to access the ZipRemote API. 

The `gsfapi.php` file is called via a `GET` method in `demo_gsfapi.html`, it is where `/zipremote/client/getsitefiles.php`:`getSiteFiles()` is called.

**NOTE**: You will need an HTTP server with PHP>=5.6 *on your local network* for `demo_gsfapi.html`. This will help insure that the intended security remains intact. In addition, you will need to enter your internet-facing IP address into the `site/ipvalid.json` file if you have enabled that security feature.

# Possible Issues

* I have **not** tested where folders are symbolically linked. If this causes problems for anyone please create an issue in this repository.

# Known Issues

This section will be updated when ever new issues are discovered, but not yet resolved.

# The Future

I would like to create a "manager" front end that uses ZipRemote. It would aid in maintaining multiple servers and keeping them up to date. It may also be possible to tie in Github to obtain content for uploading.

---
<img src="http://webexperiment.info/extcounter/mdcount.php?id=zipremote">