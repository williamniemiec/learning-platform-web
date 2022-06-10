![](https://raw.githubusercontent.com/williamniemiec/learning-platform-web/master/docs/images/logo/logo.jpg)

<h1 align='center'>Learning Platform - WEB</h1>
<p align='center'>Learning platform built with PHP.</p>
<p align="center">
	<a href="https://github.com/williamniemiec/learning-platform-web/actions/workflows/windows.yml"><img src="https://github.com/williamniemiec/learning-platform-web/actions/workflows/windows.yml/badge.svg" alt=""></a>
	<a href="https://github.com/williamniemiec/learning-platform-web/actions/workflows/macos.yml"><img src="https://github.com/williamniemiec/learning-platform-web/actions/workflows/macos.yml/badge.svg" alt=""></a>
	<a href="https://github.com/williamniemiec/learning-platform-web/actions/workflows/ubuntu.yml"><img src="https://github.com/williamniemiec/learning-platform-web/actions/workflows/ubuntu.yml/badge.svg" alt=""></a>
	<a href="http://www.php.net"><img src="https://img.shields.io/badge/PHP-7+-D0008F.svg" alt="PHP compatibility"></a>
	<a href="https://www.mysql.com"><img src="https://img.shields.io/badge/MySQL-5.7.26+-D0008F.svg" alt="MySQL compatibility"></a>
	<a href="https://github.com/williamniemiec/learning-platform-web/releases"><img src="https://img.shields.io/github/v/release/williamniemiec/learning-platform-web" alt="Release"></a>
	<a href="https://github.com/williamniemiec/learning-platform-web/blob/master/LICENSE"><img src="https://img.shields.io/github/license/williamniemiec/learning-platform-web" alt="License"></a>
</p>
<p align="center">
	<a href='https://wniemiec-web-learningplatform.herokuapp.com/)'><img alt='Deploy' src='https://www.herokucdn.com/deploy/button.svg' width=200/></a>
</p>

<hr />

## ❇ Introduction
Website project about a learning platform built with PHP along with [Selenium framework](https://www.selenium.dev/) for testing. It also uses <a href="https://github.com/williamniemiec/MVC-in-PHP">MVC design pattern</a>. This application was made for learning purposes only, not for profit. You can interact with the project through the Heroku platform ([click here to access](https://wniemiec-web-learningplatform.herokuapp.com/)) and access the administration area [here](https://wniemiec-web-learningplatform.herokuapp.com/panel/).


### Login information
| Area |Email| Password|
|------- |------- | --- |
| General | student@lp.com |	teste12345@A |
| Administration | admin@lp.com |	teste12345@A |

## ⚠ Warnings
The hosting service Heroku may have a certain delay (~ 1 min) for uploading the application so the loading of the website may have a certain delay. 

## ✔ Requirements
- [PHP 7+](https://www.php.net);
- [MySQL 5.7.26+](https://www.mysql.com);
- [Composer](https://getcomposer.org);

## 🖼 Gallery

#### Home
![home](https://github.com/williamniemiec/learning-platform-web/blob/master/docs/images/app/home.png?raw=true)

#### Bundle information
![bundle](https://github.com/williamniemiec/learning-platform-web/blob/master/docs/gif/bundle.gif?raw=true)

#### My courses
![bundle-edit](https://github.com/williamniemiec/learning-platform-web/blob/master/docs/gif/my-courses.gif?raw=true)

#### Video class
![video-class](https://github.com/williamniemiec/learning-platform-web/blob/master/docs/gif/video-class.gif?raw=true)

#### Settings
![settings](https://github.com/williamniemiec/learning-platform-web/blob/master/docs/images/app/settings.png?raw=true)

#### Support
![support](https://github.com/williamniemiec/learning-platform-web/blob/master/docs/images/app/support.png?raw=true)

#### Support - content
![support-content](https://github.com/williamniemiec/learning-platform-web/blob/master/docs/images/app/support-content.png?raw=true)

#### Admin area - courses manager
![courses-manager](https://github.com/williamniemiec/learning-platform-web/blob/master/docs/images/app/courses-manager.png?raw=true)

#### Admin area - bundles manager
![bundles-manager](https://github.com/williamniemiec/learning-platform-web/blob/master/docs/images/app/bundles-manager.png?raw=true)

#### Admin area - bundles manager - edit
![bundles-manager-edit](https://github.com/williamniemiec/learning-platform-web/blob/master/docs/gif/bundle-edit.gif?raw=true)

## 🚩 Changelog
Details about each version are documented in the [releases section](https://github.com/williamniemiec/learning-platform-web/releases).

## 🗺 Project structure
![architecture](https://raw.githubusercontent.com/williamniemiec/learning-platform-web/master/docs/images/design/architecture.png)

#### Database

###### Conceptual diagram

![database-conceptual-diagram](https://raw.githubusercontent.com/williamniemiec/learning-platform-web/master/docs/images/database/schema-conceptual.png?raw=true)

###### Logical diagram
![database-logical-diagram](https://raw.githubusercontent.com/williamniemiec/learning-platform-web/master/docs/images/database/schema-logical.png?raw=true)

###### Programs used to create database schematics
|Schema|Name|
|-------|----|
|Conceptual|[BRModelo 3.0](http://www.sis4.com/brModelo/)|
|Logical|[MySQL Workbench 8.0](https://www.mysql.com/products/workbench/)|

## 📁 Files

### /
|        Name        |Type|Description|
|----------------|-------------------------------|-----------------------------|
|docs |`Directory`|Documentation files|
| panel	|	 `Directory`	| Admin system |
|src  |`Directory`|Application and test files|
|vendor  |`Directory`|Composer files|
| 	.htaccess 				| `File`	| Redirection for correct MVC performance on Apache servers
| 	Procfile 				| `File`	| Heroku configuration file
| 	index.php 				| `File`	| Application entry point
| 	robots.txt 				| `File`	|  Tells search engine crawlers which URLs the crawler can access on your site
| 	Web.config 				| `File`	| Redirection for correct MVC performance on IIS servers

### /src
|        Name        |Type|Description|
|----------------|-------------------------------|-----------------------------|
|main|`Directory`|Application files|
|test|`Directory`|Test files|

### /src/main
|        Name        |Type|Description|
|----------------|-------------------------------|-----------------------------|
|php|`Directory`|Code files|
| sql | `Directory`| SQL queries |
| webapp | `Directory`| Image, CSS, and JavaScript files |

### /src/main/php
|        Name        |Type|Description|
|----------------|-------------------------------|-----------------------------|
| 	config 				| `Directory`	| Classes responsible for the MVC operations
| 	controllers 		| `Directory`	| Application controller classes
| 	dao 				| `Directory`	| Data access object classes
| 	domain 				| `Directory`	| Application domain classes
| repositories	|	 `Directory`	| Classes that handle with database and storage |
| 	util 				| `Directory`	| Utility classes
| 	views 				| `Directory`	| Visual application classes
| 	config.php 				| `File`	| System variables
| 	environment.php 				| `File`	| System environment
