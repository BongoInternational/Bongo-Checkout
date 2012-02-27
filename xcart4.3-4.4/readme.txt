Bongo Checkout for X-Cart v2.1.1
-----

Requirements
-----
X-Cart v4.3 or  v4.4
Bongo Partner Key & Checkout URL
Bongo Checkout Module for X-Cart v2.1


Installation
-----
1. In the Bongo_Checkout folder edit form.tpl and enter your Partner Key and Checkout URL.
2. Upload the modules/Bongo_Checkout folder to:
	
	xcart / modules / 

2. Upload the template_files/Bongo_Checkout folder to:

	X-Cart 4.3:
	xcart / [your skin name] / modules 

	X-Cart 4.4:
	xcart / skin / common_files / modules

3. Upload bongo_button.png to your root directory.

4. In your X-Cart administration area, go to Administration > Patch/Upgrade, under "Apply SQL Patch" paste the following into the field marked "SQL Query(ies)". This will activate the Bongo Checkout module. IT IS HIGHLY RECOMMENDED YOU BACKUP YOUR DATABASE BEFORE DOING THIS. You can backup your database from the Administration > DB backup/restore area.


INSERT INTO `xcart_modules` (`moduleid`, `module_name`, `module_descr`, `active`) VALUES ('1408', 'Bongo_Checkout', 'Integrated Bongo Checkout service into X-Cart.', 'Y');



Setup
-----

!!!!!
VERY IMPORTANT: 
The template includes can only be used on the shopping cart or checkout pages. Bongo Checkout will not work if placed on any other pages.
!!!!!

1. In your template, decide where you want to place the Bongo Checkout button (either the Cart or Checkout area), and insert the following code into your template:

{include file="modules/Bongo_Checkout/button.tpl"}

2. Insert the following wherever the button is used. Only insert this once per page (i.e. if you use the above to insert 3 Bongo Checkout buttons on a single page, you only need to insert this code once on that page). Make sure you insert this code AFTER the buttons and OUTSIDE of any other form elements on the page.

{include file="modules/Bongo_Checkout/form.tpl"}

