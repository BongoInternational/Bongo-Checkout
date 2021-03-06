/*		
	Bongo Checkout for Volusion		
	v2.0
	
	By Elijah Boston (elijah.boston@bongous.com / elijahboston.com)	
	
	
	
	REQUIREMENTS:
	---
	- jQuery 1.4.2 or newer
	- A Partner Key and Checkout URL provided by Bongo International
	
*/

var $b = jQuery.noConflict();


/*
		UTILITY FUNCTIONS
*/


function gup( name, url )
{
	name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	
	var regexS = "[\\?&]"+name+"=([^&#]*)";
	var regex = new RegExp( regexS );
	var results = regex.exec( url );
	
	if( results == null )
		return "";
	else
		return results[1];
}

// Trim string
function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}

// Convert hex notation to string
function hex2str(hex_encoded_str) {
	var decoded_str = '';
	var hex_code = '';
	var hex_pos = 0;
	var symbol = '';
	
	var hex_pos = hex_encoded_str.indexOf('%');
	if (hex_pos > 0) {
	
			hex_code = hex_encoded_str.substr(hex_pos+1, 2);

			switch (hex_code) {
				case '2D':
					
					symbol = '-';
					break;
				default:

					symbol = '';
					break;
			}
			
			decoded_str = hex_encoded_str.substring(0, hex_pos) + symbol + hex_encoded_str.substring(hex_pos + 3);
			
			// Recursively convert all hex in the string
			return hex2str(decoded_str);
			
	} else {
		// This string has no more hex!
		decoded_str = hex_encoded_str;
		return decoded_str;
	}
	
	
}

// Resolve country name into 2 letter ISO
function countryCode(country) {
	var iso_country_code = '';
	
	// ISO 3166-1 country names and codes from http://opencountrycodes.appspot.com/javascript
	countries = [["AF", "Afghanistan"],["AX", "Aland Islands"],["AL", "Albania"],["DZ", "Algeria"],["AS", "American Samoa"],["AD", "Andorra"],["AO", "Angola"],["AI", "Anguilla"],["AQ", "Antarctica"],["AG", "Antigua and Barbuda"],["AR", "Argentina"],["AM", "Armenia"],["AW", "Aruba"],["AU", "Australia"],["AT", "Austria"],["AZ", "Azerbaijan"],["BS", "Bahamas"],["BH", "Bahrain"],["BD", "Bangladesh"],["BB", "Barbados"],["BY", "Belarus"],["BE", "Belgium"],["BZ", "Belize"],["BJ", "Benin"],["BM", "Bermuda"],["BT", "Bhutan"],["BO", "Bolivia, Plurinational State of"],["BQ", "Bonaire, Saint Eustatius and Saba"],["BA", "Bosnia and Herzegovina"],["BW", "Botswana"],["BV", "Bouvet Island"],["BR", "Brazil"],["IO", "British Indian Ocean Territory"],["BN", "Brunei Darussalam"],["BG", "Bulgaria"],["BF", "Burkina Faso"],["BI", "Burundi"],["KH", "Cambodia"],["CM", "Cameroon"],["CA", "Canada"],["CV", "Cape Verde"],["KY", "Cayman Islands"],["CF", "Central African Republic"],["TD", "Chad"],["CL", "Chile"],["CN", "China"],["CX", "Christmas Island"],["CC", "Cocos (Keeling) Islands"],["CO", "Colombia"],["KM", "Comoros"],["CG", "Congo"],["CD", "Congo, The Democratic Republic of the"],["CK", "Cook Islands"],["CR", "Costa Rica"],["CI", "Cote D'ivoire"],["HR", "Croatia"],["CU", "Cuba"],["CW", "Curacao"],["CY", "Cyprus"],["CZ", "Czech Republic"],["DK", "Denmark"],["DJ", "Djibouti"],["DM", "Dominica"],["DO", "Dominican Republic"],["EC", "Ecuador"],["EG", "Egypt"],["SV", "El Salvador"],["GQ", "Equatorial Guinea"],["ER", "Eritrea"],["EE", "Estonia"],["ET", "Ethiopia"],["FK", "Falkland Islands (Malvinas)"],["FO", "Faroe Islands"],["FJ", "Fiji"],["FI", "Finland"],["FR", "France"],["GF", "French Guiana"],["PF", "French Polynesia"],["TF", "French Southern Territories"],["GA", "Gabon"],["GM", "Gambia"],["GE", "Georgia"],["DE", "Germany"],["GH", "Ghana"],["GI", "Gibraltar"],["GR", "Greece"],["GL", "Greenland"],["GD", "Grenada"],["GP", "Guadeloupe"],["GU", "Guam"],["GT", "Guatemala"],["GG", "Guernsey"],["GN", "Guinea"],["GW", "Guinea-Bissau"],["GY", "Guyana"],["HT", "Haiti"],["HM", "Heard Island and McDonald Islands"],["VA", "Holy See (Vatican City State)"],["HN", "Honduras"],["HK", "Hong Kong"],["HU", "Hungary"],["IS", "Iceland"],["IN", "India"],["ID", "Indonesia"],["IR", "Iran, Islamic Republic of"],["IQ", "Iraq"],["IE", "Ireland"],["IM", "Isle of Man"],["IL", "Israel"],["IT", "Italy"],["JM", "Jamaica"],["JP", "Japan"],["JE", "Jersey"],["JO", "Jordan"],["KZ", "Kazakhstan"],["KE", "Kenya"],["KI", "Kiribati"],["KP", "Korea, Democratic People's Republic of"],["KR", "Korea, Republic of"],["KW", "Kuwait"],["KG", "Kyrgyzstan"],["LA", "Lao People's Democratic Republic"],["LV", "Latvia"],["LB", "Lebanon"],["LS", "Lesotho"],["LR", "Liberia"],["LY", "Libyan Arab Jamahiriya"],["LI", "Liechtenstein"],["LT", "Lithuania"],["LU", "Luxembourg"],["MO", "Macao"],["MK", "Macedonia, The Former Yugoslav Republic of"],["MG", "Madagascar"],["MW", "Malawi"],["MY", "Malaysia"],["MV", "Maldives"],["ML", "Mali"],["MT", "Malta"],["MH", "Marshall Islands"],["MQ", "Martinique"],["MR", "Mauritania"],["MU", "Mauritius"],["YT", "Mayotte"],["MX", "Mexico"],["FM", "Micronesia, Federated States of"],["MD", "Moldova, Republic of"],["MC", "Monaco"],["MN", "Mongolia"],["ME", "Montenegro"],["MS", "Montserrat"],["MA", "Morocco"],["MZ", "Mozambique"],["MM", "Myanmar"],["NA", "Namibia"],["NR", "Nauru"],["NP", "Nepal"],["NL", "Netherlands"],["NC", "New Caledonia"],["NZ", "New Zealand"],["NI", "Nicaragua"],["NE", "Niger"],["NG", "Nigeria"],["NU", "Niue"],["NF", "Norfolk Island"],["MP", "Northern Mariana Islands"],["NO", "Norway"],["OM", "Oman"],["PK", "Pakistan"],["PW", "Palau"],["PS", "Palestinian Territory, Occupied"],["PA", "Panama"],["PG", "Papua New Guinea"],["PY", "Paraguay"],["PE", "Peru"],["PH", "Philippines"],["PN", "Pitcairn"],["PL", "Poland"],["PT", "Portugal"],["PR", "Puerto Rico"],["QA", "Qatar"],["RE", "Reunion"],["RO", "Romania"],["RU", "Russian Federation"],["RW", "Rwanda"],["BL", "Saint Barthelemy"],["SH", "Saint Helena, Ascension and Tristan Da Cunha"],["KN", "Saint Kitts and Nevis"],["LC", "Saint Lucia"],["MF", "Saint Martin (French Part)"],["PM", "Saint Pierre and Miquelon"],["VC", "Saint Vincent and the Grenadines"],["WS", "Samoa"],["SM", "San Marino"],["ST", "Sao Tome and Principe"],["SA", "Saudi Arabia"],["SN", "Senegal"],["RS", "Serbia"],["SC", "Seychelles"],["SL", "Sierra Leone"],["SG", "Singapore"],["SX", "Sint Maarten (Dutch Part)"],["SK", "Slovakia"],["SI", "Slovenia"],["SB", "Solomon Islands"],["SO", "Somalia"],["ZA", "South Africa"],["GS", "South Georgia and the South Sandwich Islands"],["ES", "Spain"],["LK", "Sri Lanka"],["SD", "Sudan"],["SR", "Suriname"],["SJ", "Svalbard and Jan Mayen"],["SZ", "Swaziland"],["SE", "Sweden"],["CH", "Switzerland"],["SY", "Syrian Arab Republic"],["TW", "Taiwan, Province of China"],["TJ", "Tajikistan"],["TZ", "Tanzania, United Republic of"],["TH", "Thailand"],["TL", "Timor-Leste"],["TG", "Togo"],["TK", "Tokelau"],["TO", "Tonga"],["TT", "Trinidad and Tobago"],["TN", "Tunisia"],["TR", "Turkey"],["TM", "Turkmenistan"],["TC", "Turks and Caicos Islands"],["TV", "Tuvalu"],["UG", "Uganda"],["UA", "Ukraine"],["AE", "United Arab Emirates"],["GB", "United Kingdom"],["US", "United States"],["UM", "United States Minor Outlying Islands"],["UY", "Uruguay"],["UZ", "Uzbekistan"],["VU", "Vanuatu"],["VE", "Venezuela, Bolivarian Republic of"],["VN", "Viet Nam"],["VG", "Virgin Islands, British"],["VI", "Virgin Islands, U.S."],["WF", "Wallis and Futuna"],["EH", "Western Sahara"],["YE", "Yemen"],["ZM", "Zambia"],["ZW", "Zimbabwe"]];

	for (i=0; i < countries.length; i++) {
		if (country == countries[i][1]) {
			iso_country_code = countries[i][0];
			break;
		}
	}

	return iso_country_code;
}

/*
		Bongo Checkout Object
*/

var BongoCheckout = {
	partner_key: 'YOUR PARTNER KEY',
	message: '<b>International Customers</b>, please click the button below to continue to Checkout.',
	debug_mode: true, // Display messages in the browser console
	
	cust_fname: '',
	cust_lname: '',
	cust_addr1: '',
	cust_addr2: '',
	cust_zip: '',
	cust_state: '',
	cust_country: 'United States',
	cust_country_iso: 'US',

	
	getItemIDs: function() {
		var ids = new Array();
		var ids_filtered = new Array();
		urls = $b('form[name="form"] td a[href^="ProductDetails.asp"]');

		
		for (i=0; i < urls.length; i++) {
			// Convert any hex ASCII into a character with hex2str, after using gup to extract URL parameters
			ids[i] = hex2str(gup('ProductCode', urls[i]));
		}

		return ids;
	},

	getItemNames: function() {
		names = new Array();
		elements = $b('form[name="form"] td a[href^="ProductDetails.asp"] b');
		
		for (i=0;i < elements.length; i++) {
			names[i] = elements[i].innerHTML;
		}
		return names;
	},

	getItemQuantities: function() {
		quantities = new Array();
		elements = $b('form[name="form"] td input[name^="Quantity"]');
		
		for (i=0; i < elements.length; i++) {
			quantities[i] = elements[i].value;
		}
		
		return quantities;
	},

	getItemPrices: function() {
		price = '';
		prices = new Array();
		elements = $b('form[name="form"] td div font:contains("$")').not(':odd');
		
		for (i=0; i < elements.length; i++) {
			price = trim(elements[i].innerHTML);
			prices[i] = price.substr(1);
		}
		
		return prices;
	},
	
	insertForm: function() {
		var itemIDs = new Array();
		var itemNames = new Array();
		var itemQuantities = new Array();
		var itemPrices = new Array();
		
		itemIDs = this.getItemIDs();
		itemNames = this.getItemNames();
		itemQuantities = this.getItemQuantities();
		itemPrices = this.getItemPrices();
		custInfo = new Array();
		
		// Change 'YOUR PARTNER KEY' in the line below to the partner key given to you.
		var partner_key = this.partner_key;
		$b('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PARTNER_KEY" value="'+partner_key+'">');
		
		
		// Append customer info (Country / State / Zip)
		$b('form[name="BongoCheckoutForm"]').append('<select style="display:none;" name="CUST_COUNTRY"><option value="'+this.cust_country_iso+'">'+this.cust_country+'</option></select> ');
		
		if (this.cust_state) {
			$b('form[name="BongoCheckoutForm"]').append('<input style="display:none;" type="text" name="CUST_STATE" value="'+this.cust_state+'" /> ');
		}
		
		if (this.cust_zip) {
			$b('form[name="BongoCheckoutForm"]').append('<input style="display:none;" type="text" name="CUST_ZIP" value="'+this.cust_zip+'" /> ');
		}
		
		// Append info for each item (ID / Name / Price / Quantity)
		for (i=0; i < itemIDs.length; i++) {
			$b('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_ID_'+(i+1)+'" value="'+itemIDs[i]+'" /> ');
			$b('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_NAME_'+(i+1)+'" value="'+itemNames[i]+'" /> ');
			$b('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_PRICE_'+(i+1)+'" value="'+itemPrices[i]+'" /> ');
			$b('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_Q_'+(i+1)+'" value="'+itemQuantities[i]+'" /> ');
			$b('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_SHIPPING_'+(i+1)+'" value="1.00"> ');
		}
	},
	
	getCustomerProfile: function() {

		if (this.compatability_mode) {
			// old format
			this.cust_zip = $b('form[name="form"] select[name="Precalc_PostalCode"]').val();
			this.cust_country = $b('form[name="form"] select[name="Precalc_Country"]').val();
			this.cust_state = $b('form[name="form"] input[name="Precalc_State"]').val();
		} else {
			this.cust_state = $b('form[name="form"] input[name="ShipState"]').val();
			this.cust_country = $b('form[name="form"] select[name="ShipCountry"]').val();
			this.cust_zip = $b('form[name="form"] input[name="ShipPostalCode"]').val();
		}
		
		if (this.cust_country != '') {
			this.cust_country_iso = countryCode(this.cust_country);
		}
	},
	
	// Append and control the international shipping notification
	insertButton: function() {
		var button_html = '<div style="width:400px; float:right; text-align:center; border:1px solid #ff6501; background:#fff; padding:10px; margin:10px 0;">'+this.message+'<br /><img style="cursor:pointer;" src="https://bongous.com/partner/images/Bongo_Checkout_Button.png" onclick="javascript:document.BongoCheckoutForm.submit()" border="0"/></div>';
		
		// v6.5 Specific
		if (document.getElementById('#primaryArticleText')) {
			$b('#primaryArticleText"]').append(button_html);
		} else {
		// Newer version
			$b('form[name="form"]').append(button_html);
		}

	},
	
	insertNotice: function() {
	
		var message = 'Click on the <font style="color:#ff6501;">orange</font> button below that says <font style="color:#ff6501;">\'International Customer? Click here to pay\'</font>. The shipping duties, taxes, and processing will be assessed on the next page.';
		var content = '<div id="BongoCheckoutNotice" style="padding:5px;"><p><b><font style="color:red;">International Customers!</font> Don\'t see your country?</b><br />'+message+'</p></div>';
		// Try to add a hidden box to display a message to the user
		try
		{	

			$b('form[name="form"] select[name="ShipCountry"]').parent().parent().parent().parent().parent().append(content);

		}
		catch(err) 
		{
		
		}
		
		if (!document.getElementById('BongoCheckoutNotice')) {
	
			// For older version of Volusion
			try
			{
				$b('form[name="form"] select[name="Precalc_Country"]').parent().parent().parent().append('<tr><td colspan="2">'+content+'</td></tr>');

			}
			catch(err)
			{
			
			}
			
		}
	},
	
	// Initialize the Bongo Checkout module
	init: function() {
		this.getCustomerProfile();
		this.insertForm();
		this.insertButton();
		this.insertNotice();
	},	
	
}

BongoCheckout.init();