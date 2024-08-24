# miniKB
Here is a compact knowledgebase version, supports only one user :)  

* 2 database tables
* 4 PHP files
* 4 JS files  

Database file and structure if doesnt exist will be created on first run, see the `login.php` code. On tests is able to load 3000 records in 3 seconds.
&nbsp;

https://github.com/wenzhixin/bootstrap-table/assets/3852762/edc4062b-c61d-453b-b3da-09e33198a27d

&nbsp;

* The `sample database` with the 3000 records, borrowed by [Source Code Organizer NET](https://www.pipiscrew.com/threads/source-code-organizer-net-v2-0.8/) as is using the same dbase schema (which introduced on `VB6` flavor at 2005).  
* `Images embedded` as base64 and also drag&drop supported out-of-the-box by [summernote](https://github.com/summernote/summernote/).  

&nbsp;

use it with any standar PHP distro as :
```js
//runme.vbs
Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "cmd /c php.exe -S localhost:80 -t ../htdocs/", 0, False
```  

&nbsp;

## This project uses the following 3rd-party dependencies :
* [bootstrap](https://getbootstrap.com/)
* [summernote](https://github.com/summernote/summernote/) with [jQuery](https://github.com/jquery/jquery)
* [bootstrap-treeview](https://github.com/jonmiles/bootstrap-treeview)  

2024-04-08 - `bootstrap-treeview.js` update `getNodeIdByDBid`, take care of int/string `type` due [PHP v8.1](https://www.php.net/manual/en/migration81.incompatible.php#migration81.incompatible.pdo.mysql) [[2](https://github.com/doctrine/dbal/issues/5228)] change. Development made with PHP v8.0.7.  

---

if you still unhappy with this microKB, there are alternatives as :  
* [BookStack](https://github.com/BookStackApp/BookStack) - php 41mb without the dependencies ;)
* [phpMyFAQ](http://www.phpmyfaq.de/)
* [dokuwiki](https://www.dokuwiki.org/)
* [mediawiki](https://www.mediawiki.org/)
* [opus](https://github.com/ziishaned/opus)
* [Confluence](https://www.atlassian.com/software/confluence/download-archives)
* [WukongKnowledgeBase](https://github.com/WuKongOpenSource/Wukong_KnowledgeBase) - vue with java and elasticsearch
* [AdguardTeamKnowledgeBase](https://github.com/AdguardTeam/KnowledgeBase)
* [CatWiki](https://github.com/cabalamat/catwiki) - python
* [raneto](https://raneto.com/) - nodejs with markdown
* [docsify](https://docsify.js.org/) [[2](https://blog.stackademic.com/the-fast-way-to-create-documents-docsify-b92397947512)] - nodejs with markdown
* [wiki.js](https://js.wiki/)
* [mdBook](https://github.com/rust-lang/mdBook) [[2](https://rust-lang.github.io/mdBook/guide/creating.html)] - rust


alternative [docmost](https://docmost.com) | [gitbook](https://github.com/GitbookIO/gitbook) | [retype](https://retype.com/)  

---

## This project is no longer maintained
Copyright (c) 2024 [PipisCrew](http://pipiscrew.com)  

Licensed under the [MIT license](http://www.opensource.org/licenses/mit-license.php).