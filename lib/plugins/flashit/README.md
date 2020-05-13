# Total Recall

### Flash card memorization webapp.

**Stand-alone PHP script for Flash Cards with a Javascript interface and logic**

*By: Dr. Brady Bouchard*

Development continues at: <https://github.com/bouchard/totalrecall.drbouchard.ca>

This script/app is designed to be dead-simple: no editing, no user accounts, no extras - just a simple, beautiful interface for learning.

### Features:

* Beautiful, minimal, and functional interface to maximize your learning potential.
* The awesome SM2 algorithm for keeping track of learning progress and spaced intervals to optimize your learning.
* After initial page load, all data is loaded into the browser - no waiting for additional page loads or AJAX calls!
* All progress data is stored locally.
	* Uses localStorage in modern browsers, based on the simple jQuery plugin available [here](http://www.stoimen.com/blog/2010/02/25/jquery-localstorage-plugin/).

Browser support: All modern browsers. Tested briefly on the iPhone.

Inspiration from the beautiful stylings of cramberry.net.

Using the wonderful [Fancybox](http://fancybox.net/) for displaying overlaid images.

### How to Use:

#### Step-by-step Setup:

1. Click the '**Downloads**' button at the top of this page on Github, and download the latest packaged version.
2. Unzip the file you just downloaded (on Macs, this happens automatically).
3. Create a subdirectory named 'sets' within the folder you just unzipped.
3. On startup, any XML files you have in your 'sets' directory will be loaded. The XML files have a very simple format, which is detailed further down.
	* Anything you type will be displayed as-is - if you want bullet lists, etc., read the next bullet point:
	* Questions and answers are formatted with [Markdown](http://daringfireball.net/projects/markdown/): you can use HTML as well if you like for formatting, include image links, etc.
4. Put the directory somewhere that a webserver can get at it:
	* If you're running a Mac with OS X, you're in luck - you have a webserver built in! Copy everything in the package you downloaded to '/Library/WebServer/Documents/'. Then go to **System Preferences -> Sharing** and turn 'Web Sharing' on.
	* If you're running Linux, you probably know what to do to get this to work.
	* If you're running Windows, good luck - you're best choice is to upload this directory to your school's webserver if your school has one and has **PHP** turned on (email them and ask if this seems over your head!).
5. Load index.php in a modern browser (Navigate to 'http://localhost/' if running this on your own computer).
6. Done!

#### Quick Setup for Advanced Users:

1. Clone the repo ("git clone http://github.com/brady8/total-recall.git"). Or download the zipped package.
2. XML files (format described below) go in the subdirectory 'sets'. Formatted with [Markdown](http://daringfireball.net/projects/markdown/), and raw HTML is fine.
3. Put the directory somewhere your local webserver can get at it, or upload it to a proper server: on Macs, /Library/WebServer/Documents is good.
4. Load it up (again, on Macs: http://localhost/).
5. Done!

### XML File Format

	<?xml version="1.0" encoding="UTF-8"?>
	<flashcards>
		<categories>
			<category name="Anatomy" order="1" id="1">
				<set name="Anatomy 101" id="1"/>
				<set name="Anatomy 102" id="2"/>
			</category>
			<category name="Physiology" order="2" id="2">
				<set name="Physiology 101" order="1" id="5"/>
				<set name="Physiology 102" order="2" id="8"/>
				<set name="Physiology 103" order="3" id="7"/>
			</category>
		</categories>
		<cards>
			<card id="1">
				<question>The **internal thoracic arteries** continue inferiorly as the _____.</question>
				<answer>Superior epigastric arteries</answer>
				<moreinfo>The superior epigastric arteries are an important site for anastomoses when blockage occurs in the inferior vena cava.</moreinfo>
				<associated_sets>1,2</associated_sets>
			</card>
		</cards>
	</flashcards>

### TODO:

1. Explicit support for mobile browsers:
	* Works on the iPhone, but could be optimized.
